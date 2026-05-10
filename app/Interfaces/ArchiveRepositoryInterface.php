<?php

namespace App\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Archive Repository Interface
 */
interface ArchiveRepositoryInterface extends RepositoryInterface
{
    /**
     * Find an archive record by its unique archive number.
     */
    public function findByArchiveNumber(string $archiveNumber): ?object;

    /**
     * Get archive records by the user who archived them.
     */
    public function getByArchivedBy(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Search archive records.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;
}
