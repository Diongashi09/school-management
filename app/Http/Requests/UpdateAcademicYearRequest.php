<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('academic_years')->ignore($this->academic_year)],
            'start_date' => ['sometimes', 'date', 'before:end_date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'is_current' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'An academic year with this name already exists.',
            'start_date.before' => 'Start date must be before end date.',
            'end_date.after' => 'End date must be after start date.',
        ];
    }
}
