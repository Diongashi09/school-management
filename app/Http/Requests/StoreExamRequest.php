<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'exam_type' => ['required', 'in:quiz,test,midterm,final,assignment,project'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'total_marks' => ['required', 'numeric', 'min:1', 'max:1000'],
            'passing_marks' => ['required', 'numeric', 'min:0', 'max:1000'],
            'exam_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'instructions' => ['nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'class_id.exists' => 'The selected class does not exist.',
            'subject_id.exists' => 'The selected subject does not exist.',
            'academic_year_id.exists' => 'The selected academic year does not exist.',
            'total_marks.min' => 'Total marks must be at least 1.',
            'total_marks.max' => 'Total marks cannot exceed 1000.',
            'passing_marks.min' => 'Passing marks must be at least 0.',
            'passing_marks.max' => 'Passing marks cannot exceed 1000.',
            'exam_date.after_or_equal' => 'Exam date must be today or in the future.',
            'end_time.after' => 'End time must be after start time.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->passing_marks && $this->total_marks && $this->passing_marks > $this->total_marks) {
                $validator->errors()->add('passing_marks', 'Passing marks cannot be greater than total marks.');
            }
        });
    }
}
