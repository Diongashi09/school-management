<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parentId = $this->route('parent');
        
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($parentId, 'id')
            ],
            'phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'workplace' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'relationship' => 'nullable|string|in:parent,guardian,step-parent,other',
            'is_primary_contact' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a string.',
            'last_name.string' => 'Last name must be a string.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'relationship.in' => 'Invalid relationship type.',
        ];
    }
}