<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

/**
 * Document Repository Interface
 */
interface DocumentRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all documents for a specific research.
     */
    public function getByResearch(int $researchId): Collection;

    /**
     * Find a document by its SHA-256 hash (duplicate detection).
     */
    public function findBySha256(string $hash): ?object;
}
