<?php

namespace App\Repositories;

use App\Interfaces\DocumentRepositoryInterface;
use App\Models\ResearchDocument;
use Illuminate\Database\Eloquent\Collection;

/**
 * Document Repository
 *
 * Handles data access for ResearchDocument entities.
 */
class DocumentRepository extends BaseRepository implements DocumentRepositoryInterface
{
    public function __construct(ResearchDocument $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getByResearch(int $researchId): Collection
    {
        return $this->model
            ->where('research_id', $researchId)
            ->latest()
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySha256(string $hash): ?object
    {
        return $this->model
            ->with('research')
            ->where('sha256_hash', $hash)
            ->first();
    }
}
