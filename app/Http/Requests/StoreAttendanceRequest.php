<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
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
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s|after:check_in_time',
            'status' => 'required|in:present,absent,late,excused,partial',
            'remarks' => 'nullable|string|max:500',
            'is_half_day' => 'boolean',
            'period_type' => 'required|in:full_day,morning,afternoon,subject_wise',
            'period_number' => 'nullable|integer|min:1|max:10',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Student is required.',
            'student_id.exists' => 'Selected student does not exist.',
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'teacher_id.required' => 'Teacher is required.',
            'teacher_id.exists' => 'Selected teacher does not exist.',
            'academic_year_id.required' => 'Academic year is required.',
            'academic_year_id.exists' => 'Selected academic year does not exist.',
            'attendance_date.required' => 'Attendance date is required.',
            'attendance_date.date' => 'Attendance date must be a valid date.',
            'status.required' => 'Attendance status is required.',
            'status.in' => 'Attendance status must be one of: present, absent, late, excused, partial.',
            'check_out_time.after' => 'Check-out time must be after check-in time.',
            'period_type.in' => 'Period type must be one of: full_day, morning, afternoon, subject_wise.',
        ];
    }
}