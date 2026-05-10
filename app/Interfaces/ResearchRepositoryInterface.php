<?php

namespace App\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Research Repository Interface
 *
 * Extends the base repository with research-specific query methods.
 */
interface ResearchRepositoryInterface extends RepositoryInterface
{
    /**
     * Search research by title, abstract, or keywords.
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find research by SHA-256 file hash to detect duplicates.
     */
    public function findByHash(string $hash): ?object;

    /**
     * Get research by status with eager-loaded relations.
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get research by department with eager-loaded relations.
     */
    public function getByDepartment(int $departmentId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get research by researcher with eager-loaded relations.
     */
    public function getByResearcher(int $researcherId, int $perPage = 15): LengthAwarePaginator;
}
