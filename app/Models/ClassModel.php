<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'academic_year_id',
        'grade_level',
        'section',
        'capacity',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the academic year for this class.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the enrollments for this class.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    /**
     * Get the students enrolled in this class.
     */
    public function students()
    {
        return $this->hasManyThrough(Student::class, Enrollment::class, 'class_id', 'id', 'id', 'student_id');
    }

    /**
     * Get the teachers assigned to this class.
     */
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'class_teachers')
            ->withPivot('subject_id', 'academic_year_id', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Scope to get only active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get classes by grade level.
     */
    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade_level', $grade);
    }
    public function attendances()
{
    return $this->hasMany(Attendance::class, 'class_id');
}
}
