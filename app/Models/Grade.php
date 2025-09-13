<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'exam_id',
        'obtained_marks',
        'grade',
        'remarks',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'obtained_marks' => 'decimal:2',
    ];

    /**
     * Get the student for this grade.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the exam for this grade.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the user who created this grade.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get grades by student.
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get grades by exam.
     */
    public function scopeByExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    /**
     * Scope to get grades by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->whereHas('exam', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        });
    }

    /**
     * Scope to get grades by subject.
     */
    public function scopeBySubject($query, $subjectId)
    {
        return $query->whereHas('exam', function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        });
    }

    /**
     * Scope to get grades by academic year.
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->whereHas('exam', function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId);
        });
    }

    /**
     * Scope to get passing grades.
     */
    public function scopePassing($query)
    {
        return $query->whereHas('exam', function ($q) {
            $q->whereColumn('grades.obtained_marks', '>=', 'exams.passing_marks');
        });
    }

    /**
     * Scope to get failing grades.
     */
    public function scopeFailing($query)
    {
        return $query->whereHas('exam', function ($q) {
            $q->whereColumn('grades.obtained_marks', '<', 'exams.passing_marks');
        });
    }

    /**
     * Get the percentage score.
     */
    public function getPercentageAttribute()
    {
        if ($this->exam && $this->exam->total_marks > 0) {
            return round(($this->obtained_marks / $this->exam->total_marks) * 100, 2);
        }
        return 0;
    }

    /**
     * Check if the grade is passing.
     */
    public function getIsPassingAttribute()
    {
        return $this->exam && $this->obtained_marks >= $this->exam->passing_marks;
    }

    /**
     * Get the grade letter based on percentage.
     */
    public function getGradeLetterAttribute()
    {
        $percentage = $this->percentage;
        
        if ($percentage >= 97) return 'A+';
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        if ($percentage >= 60) return 'D-';
        return 'F';
    }

    /**
     * Boot method to automatically set grade letter.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($grade) {
            if ($grade->obtained_marks && $grade->exam) {
                $percentage = ($grade->obtained_marks / $grade->exam->total_marks) * 100;
                
                if ($percentage >= 97) $grade->grade = 'A+';
                elseif ($percentage >= 93) $grade->grade = 'A';
                elseif ($percentage >= 90) $grade->grade = 'A-';
                elseif ($percentage >= 87) $grade->grade = 'B+';
                elseif ($percentage >= 83) $grade->grade = 'B';
                elseif ($percentage >= 80) $grade->grade = 'B-';
                elseif ($percentage >= 77) $grade->grade = 'C+';
                elseif ($percentage >= 73) $grade->grade = 'C';
                elseif ($percentage >= 70) $grade->grade = 'C-';
                elseif ($percentage >= 67) $grade->grade = 'D+';
                elseif ($percentage >= 63) $grade->grade = 'D';
                elseif ($percentage >= 60) $grade->grade = 'D-';
                else $grade->grade = 'F';
            }
        });
    }
}
