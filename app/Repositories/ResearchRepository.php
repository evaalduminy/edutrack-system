<?php

namespace App\Repositories;

use App\Interfaces\ResearchRepositoryInterface;
use App\Models\Research;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Research Repository
 *
 * Handles all data access for Research entities.
 * Uses Eager Loading to solve N+1 query problems.
 */
class ResearchRepository extends BaseRepository implements ResearchRepositoryInterface
{
    /**
     * Default relations to eager-load for research queries.
     */
    protected array $defaultRelations = ['researcher', 'supervisor', 'department', 'documents'];

    public function __construct(Research $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $builder = $this->model
            ->with($this->defaultRelations)
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('abstract', 'LIKE', "%{$query}%");
            });

        // Apply optional filters
        if (! empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (! empty($filters['department_id'])) {
            $builder->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['researcher_id'])) {
            $builder->where('researcher_id', $filters['researcher_id']);
        }

        if (! empty($filters['date_from'])) {
            $builder->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $builder->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $builder->latest()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function findByHash(string $hash): ?object
    {
        return $this->model
            ->with($this->defaultRelations)
            ->where('file_hash_sha256', $hash)
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with($this->defaultRelations)
            ->where('status', $status)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getByDepartment(int $departmentId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with($this->defaultRelations)
            ->where('department_id', $departmentId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getByResearcher(int $researcherId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with($this->defaultRelations)
            ->where('researcher_id', $researcherId)
            ->latest()
            ->paginate($perPage);
    }
}
