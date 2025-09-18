<?php
// File: app/Models/Fee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fee_code',
        'name',
        'description',
        'amount',
        'fee_type',
        'payment_frequency',
        'academic_year_id',
        'class_id',
        'is_mandatory',
        'is_active',
        'due_date',
        'late_fee_amount',
        'late_fee_days',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'due_date' => 'date',
        'late_fee_amount' => 'integer',
        'late_fee_days' => 'integer',
    ];

    /**
     * Get the academic year for this fee.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the class for this fee.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the student fees for this fee.
     */
    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }

    /**
     * Scope to get only active fees.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get fees by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('fee_type', $type);
    }

    /**
     * Scope to get fees by class.
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to get fees by academic year.
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to get mandatory fees.
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Scope to get optional fees.
     */
    public function scopeOptional($query)
    {
        return $query->where('is_mandatory', false);
    }

    /**
     * Check if fee is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Get total collected amount for this fee.
     */
    public function getTotalCollectedAttribute()
    {
        return $this->studentFees()->where('status', 'paid')->sum('total_amount');
    }

    /**
     * Get total pending amount for this fee.
     */
    public function getTotalPendingAttribute()
    {
        return $this->studentFees()->whereIn('status', ['pending', 'partial', 'overdue'])->sum('total_amount');
    }
}