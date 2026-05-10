<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Archive Research Request
 */
class ArchiveResearchRequest extends FormRequest
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
            'research_id' => ['required', 'integer', 'exists:research,id'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'research_id.required' => 'يجب تحديد البحث المراد أرشفته.',
            'research_id.exists'   => 'البحث المحدد غير موجود.',
        ];
    }
}
