<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Research Request
 */
class UpdateResearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'         => ['sometimes', 'string', 'max:500'],
            'abstract'      => ['nullable', 'string', 'max:10000'],
            'file'          => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:51200'],
            'status'        => ['sometimes', 'in:pending,approved,rejected'],
            'supervisor_id' => ['nullable', 'integer', 'exists:users,id'],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
        ];
    }
}
