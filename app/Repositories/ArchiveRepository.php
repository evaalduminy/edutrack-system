<?php

namespace App\Repositories;

use App\Interfaces\ArchiveRepositoryInterface;
use App\Models\ArchiveRecord;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Archive Repository
 *
 * Handles data access for ArchiveRecord entities.
 */
class ArchiveRepository extends BaseRepository implements ArchiveRepositoryInterface
{
    public function __construct(ArchiveRecord $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function findByArchiveNumber(string $archiveNumber): ?object
    {
        return $this->model
            ->with(['research', 'archivedBy'])
            ->where('archive_number', $archiveNumber)
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getByArchivedBy(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['research', 'archivedBy'])
            ->where('archived_by', $userId)
            ->latest('archived_at')
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['research', 'archivedBy'])
            ->where(function ($q) use ($query) {
                $q->where('archive_number', 'LIKE', "%{$query}%")
                  ->orWhere('notes', 'LIKE', "%{$query}%")
                  ->orWhereHas('research', function ($rq) use ($query) {
                      $rq->where('title', 'LIKE', "%{$query}%");
                  });
            })
            ->latest('archived_at')
            ->paginate($perPage);
    }
}
