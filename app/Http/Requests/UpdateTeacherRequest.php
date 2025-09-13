<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // User account fields
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->teacher->user_id)],
            
            // Teacher profile fields
            'employee_id' => ['sometimes', 'string', 'max:50', Rule::unique('teachers')->ignore($this->teacher)],
            'date_of_birth' => ['sometimes', 'date', 'before:today'],
            'gender' => ['sometimes', 'in:male,female,other'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'string'],
            'qualification' => ['sometimes', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['sometimes', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive,terminated'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.unique' => 'A teacher with this employee ID already exists.',
            'email.unique' => 'A user with this email already exists.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'salary.min' => 'Salary must be a positive number.',
        ];
    }
}
