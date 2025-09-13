<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'exam_type',
        'class_id',
        'subject_id',
        'academic_year_id',
        'total_marks',
        'passing_marks',
        'exam_date',
        'start_time',
        'end_time',
        'instructions',
        'is_published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'total_marks' => 'decimal:2',
        'passing_marks' => 'decimal:2',
        'is_published' => 'boolean',
    ];

    /**
     * Get the class for this exam.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the subject for this exam.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the academic year for this exam.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the grades for this exam.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get the students who took this exam.
     */
    public function students()
    {
        return $this->hasManyThrough(Student::class, Grade::class, 'exam_id', 'id', 'id', 'student_id');
    }

    /**
     * Scope to get only published exams.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to get exams by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('exam_type', $type);
    }

    /**
     * Scope to get exams by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to get exams by subject.
     */
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope to get exams by academic year.
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to get upcoming exams.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('exam_date', '>=', now()->toDateString());
    }

    /**
     * Scope to get past exams.
     */
    public function scopePast($query)
    {
        return $query->where('exam_date', '<', now()->toDateString());
    }

    /**
     * Get the percentage of students who passed.
     */
    public function getPassPercentageAttribute()
    {
        $totalStudents = $this->grades()->count();
        if ($totalStudents === 0) return 0;
        
        $passedStudents = $this->grades()->where('obtained_marks', '>=', $this->passing_marks)->count();
        return round(($passedStudents / $totalStudents) * 100, 2);
    }

    /**
     * Get the average marks for this exam.
     */
    public function getAverageMarksAttribute()
    {
        return $this->grades()->avg('obtained_marks') ?? 0;
    }

    /**
     * Get the highest marks for this exam.
     */
    public function getHighestMarksAttribute()
    {
        return $this->grades()->max('obtained_marks') ?? 0;
    }

    /**
     * Get the lowest marks for this exam.
     */
    public function getLowestMarksAttribute()
    {
        return $this->grades()->min('obtained_marks') ?? 0;
    }
}
