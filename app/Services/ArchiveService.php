<?php

namespace App\Services;

use App\Interfaces\ArchiveRepositoryInterface;
use App\Interfaces\ResearchRepositoryInterface;
use App\Models\ArchiveRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Archive Service
 *
 * Handles the business logic for archiving approved research.
 * Generates unique archive numbers and manages the archiving workflow.
 */
class ArchiveService
{
    public function __construct(
        protected ArchiveRepositoryInterface $archiveRepo,
        protected ResearchRepositoryInterface $researchRepo,
    ) {}

    /**
     * Archive an approved research.
     *
     * @param  int  $researchId  The research ID to archive.
     * @param  int  $archivedBy  The user ID performing the archiving.
     * @param  string|null  $notes  Optional notes about the archiving.
     * @return ArchiveRecord  The created archive record.
     *
     * @throws \InvalidArgumentException  If research is not approved or already archived.
     */
    public function archiveResearch(int $researchId, int $archivedBy, ?string $notes = null): ArchiveRecord
    {
        return DB::transaction(function () use ($researchId, $archivedBy, $notes) {
            $research = $this->researchRepo->findById($researchId, ['*'], ['archiveRecord']);

            if (! $research) {
                throw new \InvalidArgumentException('Research not found.');
            }

            if ($research->status !== 'approved') {
                throw new \InvalidArgumentException(
                    'Only approved research can be archived. Current status: ' . $research->status
                );
            }

            if ($research->archiveRecord) {
                throw new \InvalidArgumentException(
                    'Research is already archived with number: ' . $research->archiveRecord->archive_number
                );
            }

            // Generate unique archive number
            $archiveNumber = $this->generateArchiveNumber($research);

            // Create archive record
            $archiveRecord = $this->archiveRepo->create([
                'research_id'      => $researchId,
                'archive_number'   => $archiveNumber,
                'archived_by'      => $archivedBy,
                'notes'            => $notes,
                'archive_metadata' => [
                    'department'     => $research->department->name ?? null,
                    'researcher'     => $research->researcher->name ?? null,
                    'file_hash'      => $research->file_hash_sha256,
                    'archived_at_ip' => request()->ip(),
                ],
                'archived_at'      => Carbon::now(),
            ]);

            // Update research with archived timestamp
            $this->researchRepo->update($researchId, [
                'archived_at' => Carbon::now(),
            ]);

            // Clear caches
            Cache::forget('research_statistics');
            Cache::forget('archive_statistics');

            Log::info('Research archived', [
                'research_id'    => $researchId,
                'archive_number' => $archiveNumber,
                'archived_by'    => $archivedBy,
            ]);

            return $archiveRecord;
        });
    }

    /**
     * Search archive records.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->archiveRepo->search($query, $perPage);
    }

    /**
     * Find an archive record by its archive number.
     */
    public function findByArchiveNumber(string $archiveNumber): ?object
    {
        return $this->archiveRepo->findByArchiveNumber($archiveNumber);
    }

    /**
     * Get archive records list.
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->archiveRepo->paginate($perPage, ['*'], ['research', 'archivedBy']);
    }

    /**
     * Get archive statistics (cached).
     */
    public function getStatistics(): array
    {
        return Cache::remember('archive_statistics', now()->addMinutes(15), function () {
            $total = $this->archiveRepo->all()->count();

            return [
                'total_archived'  => $total,
                'archived_today'  => ArchiveRecord::whereDate('archived_at', Carbon::today())->count(),
                'archived_month'  => ArchiveRecord::whereMonth('archived_at', Carbon::now()->month)
                    ->whereYear('archived_at', Carbon::now()->year)
                    ->count(),
            ];
        });
    }

    /**
     * Generate a unique, human-readable archive number.
     *
     * Format: EDU-{YEAR}-{DEPT_CODE}-{SEQUENCE}
     * Example: EDU-2026-CS-00042
     *
     * @param  object  $research  The research model.
     * @return string  The generated archive number.
     */
    protected function generateArchiveNumber(object $research): string
    {
        $year = Carbon::now()->format('Y');

        // Create department code from first 2-3 characters
        $deptName = $research->department->name ?? 'GEN';
        $deptCode = strtoupper(Str::substr(Str::ascii($deptName), 0, 3));

        // Get the next sequence number for this year + department
        $lastRecord = ArchiveRecord::where('archive_number', 'LIKE', "EDU-{$year}-{$deptCode}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->archive_number);
            $sequence = ((int) end($parts)) + 1;
        }

        return sprintf('EDU-%s-%s-%05d', $year, $deptCode, $sequence);
    }
}
