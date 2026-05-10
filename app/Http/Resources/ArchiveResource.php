<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Archive Resource
 */
class ArchiveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'archive_number'   => $this->archive_number,
            'notes'            => $this->notes,
            'archive_metadata' => $this->when($this->archive_metadata, $this->archive_metadata),
            'research'         => new ResearchResource($this->whenLoaded('research')),
            'archived_by'      => new UserResource($this->whenLoaded('archivedBy')),
            'archived_at'      => $this->archived_at->toIso8601String(),
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}
