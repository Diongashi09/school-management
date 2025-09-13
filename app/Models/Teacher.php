<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'date_of_birth',
        'gender',
        'phone',
        'address',
        'qualification',
        'specialization',
        'hire_date',
        'salary',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the user associated with this teacher.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the classes this teacher is assigned to.
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_teachers')
            ->withPivot('subject_id', 'academic_year_id', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get the subjects this teacher teaches.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_teachers')
            ->withPivot('class_id', 'academic_year_id', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get the current class assignments for this teacher.
     */
    public function currentAssignments()
    {
        return $this->hasMany(ClassTeacher::class)->where('academic_year_id', 2); // Current academic year
    }

    /**
     * Get the primary class for this teacher.
     */
    public function primaryClass()
    {
        return $this->hasOne(ClassTeacher::class)
            ->where('is_primary', true)
            ->where('academic_year_id', 2); // Current academic year
    }

    /**
     * Get the students taught by this teacher.
     */
    public function students()
    {
        return $this->hasManyThrough(Student::class, ClassTeacher::class, 'teacher_id', 'id', 'id', 'class_id')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.status', 'active');
    }

    /**
     * Scope to get only active teachers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get teachers by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to search teachers by name or employee ID.
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%');
        })->orWhere('employee_id', 'like', '%' . $search . '%');
    }

    /**
     * Get the teacher's age.
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }

    /**
     * Get the teacher's full name from user.
     */
    public function getFullNameAttribute()
    {
        return $this->user->name ?? 'N/A';
    }

    /**
     * Get the teacher's email from user.
     */
    public function getEmailAttribute()
    {
        return $this->user->email ?? 'N/A';
    }

    /**
     * Get the teacher's experience in years.
     */
    public function getExperienceAttribute()
    {
        return $this->hire_date->diffInYears(now());
    }
}
