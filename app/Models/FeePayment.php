<?php
// File: app/Models/FeePayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_receipt_number',
        'student_fee_id',
        'student_id',
        'amount_paid',
        'remaining_amount',
        'payment_method',
        'payment_reference',
        'payment_date',
        'received_by',
        'notes',
        'is_verified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount_paid' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'payment_date' => 'date',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the student fee for this payment.
     */
    public function studentFee()
    {
        return $this->belongsTo(StudentFee::class);
    }

    /**
     * Get the student for this payment.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the staff member who received this payment.
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope to get payments by method.
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope to get verified payments.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get unverified payments.
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope to get payments by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Check if payment is verified.
     */
    public function getIsVerifiedAttribute()
    {
        return $this->is_verified;
    }

    /**
     * Get formatted payment amount.
     */
    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount_paid, 2);
    }
}