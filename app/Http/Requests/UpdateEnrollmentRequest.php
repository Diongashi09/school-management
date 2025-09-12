<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnrollmentRequest extends FormRequest
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
            'student_id' => ['sometimes', 'exists:students,id'],
            'class_id' => ['sometimes', 'exists:classes,id'],
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            'enrollment_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'in:active,completed,withdrawn'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.exists' => 'The selected student does not exist.',
            'class_id.exists' => 'The selected class does not exist.',
            'academic_year_id.exists' => 'The selected academic year does not exist.',
        ];
    }
}
