<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'parent_id' => 'required|exists:parents,id',
            'relationship' => 'nullable|string|in:parent,guardian,step-parent,other',
            'is_primary_contact' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'can_pickup' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Student is required.',
            'student_id.exists' => 'Selected student does not exist.',
            'parent_id.required' => 'Parent is required.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'relationship.in' => 'Invalid relationship type.',
        ];
    }
}