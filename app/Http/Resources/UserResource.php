<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'role'          => $this->role,
            'department'    => new DepartmentResource($this->whenLoaded('department')),
            'email_verified' => $this->email_verified_at !== null,
            'created_at'    => $this->created_at->toIso8601String(),
        ];
    }
}
