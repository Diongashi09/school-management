<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->student->user_id)],
            
            // Student profile fields
            'student_id' => ['sometimes', 'string', 'max:50', Rule::unique('students')->ignore($this->student)],
            'date_of_birth' => ['sometimes', 'date', 'before:today'],
            'gender' => ['sometimes', 'in:male,female,other'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'address' => ['sometimes', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['sometimes', 'string', 'max:255'],
            'emergency_contact_phone' => ['sometimes', 'string', 'max:20'],
            'admission_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'in:active,inactive,graduated,transferred'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.unique' => 'A student with this ID already exists.',
            'email.unique' => 'A user with this email already exists.',
            'date_of_birth.before' => 'Date of birth must be before today.',
        ];
    }
}
