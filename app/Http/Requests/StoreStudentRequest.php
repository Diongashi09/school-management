<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            
            // Student profile fields
            'student_id' => ['required', 'string', 'max:50', 'unique:students,student_id'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:20'],
            'admission_date' => ['required', 'date'],
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
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
