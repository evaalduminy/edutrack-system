<?php

namespace App\Jobs;

use App\Models\Research;
use App\Models\ResearchDocument;
use App\Services\FileHashingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Generate File Hash Job
 *
 * Background job that computes the SHA-256 hash of a research document.
 * This is done asynchronously to avoid blocking the upload request.
 * The hash is used for duplicate detection and file integrity verification.
 */
class GenerateFileHashJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $researchId,
        protected int $documentId,
        protected string $filePath,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FileHashingService $hashingService): void
    {
        Log::info('GenerateFileHashJob started', [
            'research_id' => $this->researchId,
            'document_id' => $this->documentId,
        ]);

        $fullPath = Storage::disk('local')->path($this->filePath);

        if (! file_exists($fullPath)) {
            Log::error('File not found for hashing', [
                'path' => $fullPath,
                'research_id' => $this->researchId,
            ]);
            return;
        }

        try {
            $hash = $hashingService->hashFile($fullPath);

            // Update the document record
            ResearchDocument::where('id', $this->documentId)->update([
                'sha256_hash' => $hash,
            ]);

            // Update the research record
            Research::where('id', $this->researchId)->update([
                'file_hash_sha256' => $hash,
            ]);

            Log::info('File hash generated successfully', [
                'research_id' => $this->researchId,
                'hash'        => $hash,
            ]);
        } catch (\Exception $e) {
            Log::error('File hashing failed', [
                'research_id' => $this->researchId,
                'error'       => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('GenerateFileHashJob permanently failed', [
            'research_id' => $this->researchId,
            'document_id' => $this->documentId,
            'error'       => $exception->getMessage(),
        ]);
    }
}
