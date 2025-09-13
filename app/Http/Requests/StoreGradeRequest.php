<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
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
            'student_id' => ['required', 'exists:students,id'],
            'exam_id' => ['required', 'exists:exams,id'],
            'obtained_marks' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'student_id.exists' => 'The selected student does not exist.',
            'exam_id.exists' => 'The selected exam does not exist.',
            'obtained_marks.min' => 'Obtained marks must be at least 0.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->exam_id) {
                $exam = \App\Models\Exam::find($this->exam_id);
                if ($exam && $this->obtained_marks && $this->obtained_marks > $exam->total_marks) {
                    $validator->errors()->add('obtained_marks', 'Obtained marks cannot be greater than total marks (' . $exam->total_marks . ').');
                }
            }
        });
    }
}
