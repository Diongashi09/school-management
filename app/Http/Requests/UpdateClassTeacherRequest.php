<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassTeacherRequest extends FormRequest
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
            'teacher_id' => ['sometimes', 'exists:teachers,id'],
            'class_id' => ['sometimes', 'exists:classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'teacher_id.exists' => 'The selected teacher does not exist.',
            'class_id.exists' => 'The selected class does not exist.',
            'subject_id.exists' => 'The selected subject does not exist.',
            'academic_year_id.exists' => 'The selected academic year does not exist.',
        ];
    }
}
