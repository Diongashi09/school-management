<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'student_id',
        'date_of_birth',
        'gender',
        'blood_group',
        'address',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'admission_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    /**
     * Get the user associated with this student.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the enrollments for this student.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the current enrollment for this student.
     */
    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class)->where('status', 'active');
    }

    /**
     * Get the classes this student is enrolled in.
     */
    public function classes()
    {
        return $this->hasManyThrough(ClassModel::class, Enrollment::class, 'student_id', 'id', 'id', 'class_id');
    }

    /**
     * Get the current class for this student.
     */
    public function currentClass()
    {
        return $this->hasOneThrough(ClassModel::class, Enrollment::class, 'student_id', 'id', 'id', 'class_id')
            ->where('enrollments.status', 'active');
    }

    /**
     * Get the parents/guardians for this student.
     */
    public function parents()
    {
        return $this->belongsToMany(Parent::class, 'student_parents')
            ->withPivot('relationship', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Scope to get only active students.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get students by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to search students by name or student ID.
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%');
        })->orWhere('student_id', 'like', '%' . $search . '%');
    }

    /**
     * Get the student's age.
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }

    /**
     * Get the student's full name from user.
     */
    public function getFullNameAttribute()
    {
        return $this->user->name ?? 'N/A';
    }

    /**
     * Get the student's email from user.
     */
    public function getEmailAttribute()
    {
        return $this->user->email ?? 'N/A';
    }
}
