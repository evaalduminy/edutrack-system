<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Research Resource
 *
 * Transforms Research model data into a consistent JSON API format.
 * Ensures unified output structure across all API endpoints.
 */
class ResearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'abstract'         => $this->abstract,
            'status'           => $this->status,
            'file_hash'        => $this->file_hash_sha256,
            'is_archived'      => $this->is_archived,
            'archived_at'      => $this->archived_at?->toIso8601String(),
            'ai_metadata'      => $this->when($this->ai_metadata, $this->ai_metadata),

            // Relations (loaded conditionally to avoid N+1)
            'researcher'       => new UserResource($this->whenLoaded('researcher')),
            'supervisor'       => new UserResource($this->whenLoaded('supervisor')),
            'department'       => new DepartmentResource($this->whenLoaded('department')),
            'documents'        => DocumentResource::collection($this->whenLoaded('documents')),
            'archive_record'   => new ArchiveResource($this->whenLoaded('archiveRecord')),

            // Timestamps
            'created_at'       => $this->created_at->toIso8601String(),
            'updated_at'       => $this->updated_at->toIso8601String(),
        ];
    }
}
