<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can add permission checks here
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'sometimes|exists:students,id',
            'class_id' => 'sometimes|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'sometimes|exists:teachers,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'attendance_date' => 'sometimes|date',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s|after:check_in_time',
            'status' => 'sometimes|in:present,absent,late,excused,partial',
            'remarks' => 'nullable|string|max:500',
            'is_half_day' => 'boolean',
            'period_type' => 'sometimes|in:full_day,morning,afternoon,subject_wise',
            'period_number' => 'nullable|integer|min:1|max:10',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'student_id.exists' => 'Selected student does not exist.',
            'class_id.exists' => 'Selected class does not exist.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'academic_year_id.exists' => 'Selected academic year does not exist.',
            'attendance_date.date' => 'Attendance date must be a valid date.',
            'status.in' => 'Attendance status must be one of: present, absent, late, excused, partial.',
            'check_out_time.after' => 'Check-out time must be after check-in time.',
            'period_type.in' => 'Period type must be one of: full_day, morning, afternoon, subject_wise.',
        ];
    }
}