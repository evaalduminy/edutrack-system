<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Document Resource
 */
class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'original_name'      => $this->original_name,
            'mime_type'          => $this->mime_type,
            'file_size'          => $this->file_size,
            'file_size_formatted' => $this->formatted_size,
            'sha256_hash'        => $this->sha256_hash,
            'has_qr_code'        => $this->qr_code_path !== null,
            'extracted_metadata' => $this->when($this->extracted_metadata, $this->extracted_metadata),
            'created_at'         => $this->created_at->toIso8601String(),
        ];
    }
}
