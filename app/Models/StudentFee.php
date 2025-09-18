<?php
// File: app/Models/StudentFee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'fee_id',
        'academic_year_id',
        'amount',
        'discount_amount',
        'late_fee_amount',
        'total_amount',
        'status',
        'due_date',
        'paid_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    /**
     * Get the student for this fee.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the fee for this student fee.
     */
    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    /**
     * Get the academic year for this student fee.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the payments for this student fee.
     */
    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * Scope to get fees by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get overdue fees.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Scope to get paid fees.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to get pending fees.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get partial payments.
     */
    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    /**
     * Check if fee is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date->isPast() && in_array($this->status, ['pending', 'partial']);
    }

    /**
     * Get total paid amount.
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount_paid');
    }

    /**
     * Get remaining amount to be paid.
     */
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->total_paid;
    }

    /**
     * Check if fee is fully paid.
     */
    public function getIsFullyPaidAttribute()
    {
        return $this->remaining_amount <= 0;
    }

    /**
     * Get payment percentage.
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->total_amount <= 0) return 0;
        return round(($this->total_paid / $this->total_amount) * 100, 2);
    }
}