<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
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
            'attendance_records' => 'required|array|min:1',
            'attendance_records.*.student_id' => 'required|exists:students,id',
            'attendance_records.*.class_id' => 'required|exists:classes,id',
            'attendance_records.*.subject_id' => 'nullable|exists:subjects,id',
            'attendance_records.*.teacher_id' => 'required|exists:teachers,id',
            'attendance_records.*.academic_year_id' => 'required|exists:academic_years,id',
            'attendance_records.*.attendance_date' => 'required|date',
            'attendance_records.*.check_in_time' => 'nullable|date_format:H:i:s',
            'attendance_records.*.check_out_time' => 'nullable|date_format:H:i:s',
            'attendance_records.*.status' => 'required|in:present,absent,late,excused,partial',
            'attendance_records.*.remarks' => 'nullable|string|max:500',
            'attendance_records.*.is_half_day' => 'boolean',
            'attendance_records.*.period_type' => 'required|in:full_day,morning,afternoon,subject_wise',
            'attendance_records.*.period_number' => 'nullable|integer|min:1|max:10',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'attendance_records.required' => 'Attendance records are required.',
            'attendance_records.array' => 'Attendance records must be an array.',
            'attendance_records.min' => 'At least one attendance record is required.',
            'attendance_records.*.student_id.required' => 'Student is required for each record.',
            'attendance_records.*.student_id.exists' => 'Selected student does not exist.',
            'attendance_records.*.class_id.required' => 'Class is required for each record.',
            'attendance_records.*.class_id.exists' => 'Selected class does not exist.',
            'attendance_records.*.teacher_id.required' => 'Teacher is required for each record.',
            'attendance_records.*.teacher_id.exists' => 'Selected teacher does not exist.',
            'attendance_records.*.academic_year_id.required' => 'Academic year is required for each record.',
            'attendance_records.*.academic_year_id.exists' => 'Selected academic year does not exist.',
            'attendance_records.*.attendance_date.required' => 'Attendance date is required for each record.',
            'attendance_records.*.attendance_date.date' => 'Attendance date must be a valid date.',
            'attendance_records.*.status.required' => 'Attendance status is required for each record.',
            'attendance_records.*.status.in' => 'Attendance status must be one of: present, absent, late, excused, partial.',
            'attendance_records.*.period_type.in' => 'Period type must be one of: full_day, morning, afternoon, subject_wise.',
        ];
    }
}