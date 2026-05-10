<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Research Request
 *
 * Validates data when creating a new research entry.
 * Centralized validation keeps controllers skinny.
 */
class StoreResearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware/policies
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:500'],
            'abstract'      => ['nullable', 'string', 'max:10000'],
            'file'          => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:51200'], // 50MB max
            'status'        => ['sometimes', 'in:pending,approved,rejected'],
            'researcher_id' => ['required', 'integer', 'exists:users,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:users,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
        ];
    }

    /**
     * Custom error messages (Arabic-friendly).
     */
    public function messages(): array
    {
        return [
            'title.required'         => 'عنوان البحث مطلوب.',
            'title.max'              => 'عنوان البحث يجب ألا يتجاوز 500 حرف.',
            'file.mimes'             => 'يجب أن يكون الملف من نوع: PDF, DOC, DOCX.',
            'file.max'               => 'حجم الملف يجب ألا يتجاوز 50 ميجابايت.',
            'researcher_id.required' => 'يجب تحديد الباحث.',
            'researcher_id.exists'   => 'الباحث المحدد غير موجود.',
            'department_id.required' => 'يجب تحديد القسم.',
            'department_id.exists'   => 'القسم المحدد غير موجود.',
        ];
    }
}
