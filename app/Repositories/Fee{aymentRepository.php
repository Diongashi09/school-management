<?php
// File: app/Repositories/FeePaymentRepository.php

namespace App\Repositories;

use App\Models\FeePayment;
use App\Repositories\Interfaces\FeePaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FeePaymentRepository implements FeePaymentRepositoryInterface
{
    public function all(): Collection
    {
        return FeePayment::with(['student.user', 'studentFee.fee', 'receivedBy'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return FeePayment::with(['student.user', 'studentFee.fee', 'receivedBy'])
            ->orderBy('payment_date', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?FeePayment
    {
        return FeePayment::with(['student.user', 'studentFee.fee', 'receivedBy'])->find($id);
    }

    public function create(array $data): FeePayment
    {
        return FeePayment::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return FeePayment::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return FeePayment::destroy($id);
    }

    public function getByStudent(int $studentId): Collection
    {
        return FeePayment::with(['studentFee.fee', 'receivedBy'])
            ->where('student_id', $studentId)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function getByMethod(string $method): Collection
    {
        return FeePayment::with(['student.user', 'studentFee.fee', 'receivedBy'])
            ->byMethod($method)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function getVerified(): Collection
    {
        return FeePayment::with(['student.user', 'studentFee.fee', 'receivedBy'])
            ->verified()
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function getUnverified(): Collection
    {
        return FeePayment::with(['student.user', 'studentFee.fee', 'receivedBy'])
            ->unverified()
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return FeePayment::with(['student.user', 'studentFee.fee', 'receivedBy'])
            ->byDateRange($startDate, $endDate)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function getPaymentStatistics(string $startDate, string $endDate): array
    {
        $payments = FeePayment::whereBetween('payment_date', [$startDate, $endDate])->get();
       
        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount_paid'),
            'verified_payments' => $payments->where('is_verified', true)->count(),
            'unverified_payments' => $payments->where('is_verified', false)->count(),
            'payments_by_method' => $payments->groupBy('payment_method')->map->count(),
            'average_payment' => $payments->count() > 0 ? round($payments->avg('amount_paid'), 2) : 0,
        ];
    }

    public function getDailyCollectionReport(string $date): array
    {
        $payments = FeePayment::whereDate('payment_date', $date)
            ->with(['student.user', 'studentFee.fee'])
            ->get();
       
        return [
            'date' => $date,
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount_paid'),
            'payments_by_method' => $payments->groupBy('payment_method')->map->sum('amount_paid'),
            'payments_by_fee_type' => $payments->groupBy('studentFee.fee.fee_type')->map->sum('amount_paid'),
        ];
    }

    public function getMonthlyCollectionReport(int $month, int $year): array
    {
        $payments = FeePayment::whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->with(['student.user', 'studentFee.fee'])
            ->get();
       
        return [
            'month' => $month,
            'year' => $year,
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount_paid'),
            'daily_breakdown' => $payments->groupBy(function ($payment) {
                return $payment->payment_date->format('Y-m-d');
            })->map(function ($dayPayments) {
                return [
                    'count' => $dayPayments->count(),
                    'amount' => $dayPayments->sum('amount_paid'),
                ];
            }),
        ];
    }
}