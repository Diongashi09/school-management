<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
            'relationship.in' => 'Invalid relationship type.',
        ];
    }
}