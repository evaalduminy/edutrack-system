<?php

namespace App\Services;

use App\Interfaces\ResearchRepositoryInterface;
use App\Interfaces\DocumentRepositoryInterface;
use App\Jobs\GenerateFileHashJob;
use App\Jobs\GenerateQrCodeJob;
use App\Jobs\ExtractAiMetadataJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

/**
 * Research Service
 *
 * Encapsulates all business logic related to research management.
 * Keeps controllers skinny by handling upload pipelines, caching,
 * and job dispatching here.
 */
class ResearchService
{
    public function __construct(
        protected ResearchRepositoryInterface $researchRepo,
        protected DocumentRepositoryInterface $documentRepo,
        protected FileHashingService $hashingService,
    ) {}

    /**
     * Get paginated list of all research with optional search and filters.
     */
    public function list(array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;

        if (! empty($params['search'])) {
            $cacheKey = 'research_search_' . md5(json_encode($params));

            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($params, $perPage) {
                return $this->researchRepo->search(
                    $params['search'],
                    $params,
                    $perPage
                );
            });
        }

        return $this->researchRepo->paginate(
            $perPage,
            ['*'],
            ['researcher', 'supervisor', 'department', 'documents']
        );
    }

    /**
     * Get a single research by ID with all relations.
     */
    public function find(int $id): ?object
    {
        return $this->researchRepo->findById(
            $id,
            ['*'],
            ['researcher', 'supervisor', 'department', 'documents', 'archiveRecord']
        );
    }

    /**
     * Create a new research entry and dispatch background processing jobs.
     *
     * This method:
     * 1. Stores the uploaded file
     * 2. Creates the research record in a DB transaction
     * 3. Creates the document record
     * 4. Dispatches background jobs (hash → QR → AI metadata)
     *
     * @param  array  $data  Validated research data.
     * @param  UploadedFile|null  $file  The uploaded PDF/document.
     * @return object  The created research model.
     */
    public function create(array $data, ?UploadedFile $file = null): object
    {
        return DB::transaction(function () use ($data, $file) {
            // Store file if provided
            $filePath = null;
            $documentRecord = null;

            if ($file) {
                $filePath = $file->store('research/documents', 'local');
                $data['file_path'] = $filePath;
            }

            // Create the research record
            $research = $this->researchRepo->create($data);

            // Create document record if file was uploaded
            if ($file && $filePath) {
                $documentRecord = $this->documentRepo->create([
                    'research_id'    => $research->id,
                    'original_name'  => $file->getClientOriginalName(),
                    'stored_path'    => $filePath,
                    'mime_type'      => $file->getMimeType(),
                    'file_size'      => $file->getSize(),
                    'sha256_hash'    => null, // Will be filled by background job
                ]);

                // Dispatch background jobs in sequence (chain)
                // 1) Generate file hash → 2) Generate QR code → 3) Extract AI metadata
                GenerateFileHashJob::withChain([
                    new GenerateQrCodeJob($research->id, $documentRecord->id),
                    new ExtractAiMetadataJob($research->id),
                ])->dispatch($research->id, $documentRecord->id, $filePath);
            }

            // Clear search cache
            $this->clearSearchCache();

            Log::info('Research created', [
                'research_id' => $research->id,
                'has_file'    => $file !== null,
            ]);

            return $research;
        });
    }

    /**
     * Update an existing research entry.
     */
    public function update(int $id, array $data, ?UploadedFile $file = null): bool
    {
        return DB::transaction(function () use ($id, $data, $file) {
            if ($file) {
                // Delete old file
                $research = $this->researchRepo->findById($id);
                if ($research && $research->file_path) {
                    Storage::disk('local')->delete($research->file_path);
                }

                // Store new file
                $filePath = $file->store('research/documents', 'local');
                $data['file_path'] = $filePath;
                $data['file_hash_sha256'] = null; // Reset hash for re-computation

                // Create new document record
                $documentRecord = $this->documentRepo->create([
                    'research_id'   => $id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_path'   => $filePath,
                    'mime_type'     => $file->getMimeType(),
                    'file_size'     => $file->getSize(),
                    'sha256_hash'   => null,
                ]);

                // Re-run processing pipeline
                GenerateFileHashJob::withChain([
                    new GenerateQrCodeJob($id, $documentRecord->id),
                    new ExtractAiMetadataJob($id),
                ])->dispatch($id, $documentRecord->id, $filePath);
            }

            $result = $this->researchRepo->update($id, $data);

            $this->clearSearchCache();

            return $result;
        });
    }

    /**
     * Delete a research entry and its associated files.
     */
    public function delete(int $id): bool
    {
        $research = $this->researchRepo->findById($id, ['*'], ['documents']);

        if (! $research) {
            return false;
        }

        // Delete associated files
        if ($research->file_path) {
            Storage::disk('local')->delete($research->file_path);
        }

        // Delete document files
        if ($research->documents) {
            foreach ($research->documents as $doc) {
                Storage::disk('local')->delete($doc->stored_path);
                if ($doc->qr_code_path) {
                    Storage::disk('local')->delete($doc->qr_code_path);
                }
            }
        }

        $result = $this->researchRepo->delete($id);

        $this->clearSearchCache();

        return $result;
    }

    /**
     * Check if a file is a duplicate by computing its hash and comparing.
     *
     * @param  UploadedFile  $file  The uploaded file.
     * @return object|null  The existing research if duplicate, null otherwise.
     */
    public function checkDuplicate(UploadedFile $file): ?object
    {
        $tempPath = $file->getRealPath();
        $hash = $this->hashingService->hashFile($tempPath);

        return $this->researchRepo->findByHash($hash);
    }

    /**
     * Get research statistics for the dashboard (cached).
     */
    public function getStatistics(): array
    {
        return Cache::remember('research_statistics', now()->addMinutes(15), function () {
            return [
                'total'    => $this->researchRepo->all()->count(),
                'pending'  => $this->researchRepo->findByField('status', 'pending')->count(),
                'approved' => $this->researchRepo->findByField('status', 'approved')->count(),
                'rejected' => $this->researchRepo->findByField('status', 'rejected')->count(),
            ];
        });
    }

    /**
     * Clear cached search results.
     */
    protected function clearSearchCache(): void
    {
        // Clear statistics cache
        Cache::forget('research_statistics');
    }
}
