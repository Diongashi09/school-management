<?php
// File: app/Repositories/StudentFeeRepository.php

namespace App\Repositories;

use App\Models\StudentFee;
use App\Repositories\Interfaces\StudentFeeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentFeeRepository implements StudentFeeRepositoryInterface
{
    public function all(): Collection
    {
        return StudentFee::with(['student.user', 'fee', 'academicYear'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return StudentFee::with(['student.user', 'fee', 'academicYear'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?StudentFee
    {
        return StudentFee::with(['student.user', 'fee', 'academicYear', 'payments'])->find($id);
    }

    public function create(array $data): StudentFee
    {
        return StudentFee::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return StudentFee::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return StudentFee::destroy($id);
    }

    public function getByStudent(int $studentId): Collection
    {
        return StudentFee::with(['fee', 'academicYear', 'payments'])
            ->where('student_id', $studentId)
            ->orderBy('due_date', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return StudentFee::with(['student.user', 'fee', 'academicYear'])
            ->byStatus($status)
            ->orderBy('due_date', 'desc')
            ->get();
    }

    public function getOverdue(): Collection
    {
        return StudentFee::with(['student.user', 'fee', 'academicYear'])
            ->overdue()
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getPaid(): Collection
    {
        return StudentFee::with(['student.user', 'fee', 'academicYear'])
            ->paid()
            ->orderBy('paid_date', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return StudentFee::with(['student.user', 'fee', 'academicYear'])
            ->pending()
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getPartial(): Collection
    {
        return StudentFee::with(['student.user', 'fee', 'academicYear'])
            ->partial()
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getStudentFeeStatistics(int $studentId): array
    {
        $studentFees = StudentFee::where('student_id', $studentId)->get();
       
        return [
            'total_fees' => $studentFees->count(),
            'total_amount' => $studentFees->sum('total_amount'),
            'paid_amount' => $studentFees->sum('total_paid'),
            'pending_amount' => $studentFees->sum('remaining_amount'),
            'overdue_count' => $studentFees->where('is_overdue', true)->count(),
            'paid_count' => $studentFees->where('status', 'paid')->count(),
            'pending_count' => $studentFees->whereIn('status', ['pending', 'partial'])->count(),
        ];
    }

    public function getClassFeeStatistics(int $classId): array
    {
        $studentFees = StudentFee::whereHas('student.enrollments', function ($query) use ($classId) {
            $query->where('class_id', $classId);
        })->get();
       
        return [
            'total_fees' => $studentFees->count(),
            'total_amount' => $studentFees->sum('total_amount'),
            'paid_amount' => $studentFees->sum('total_paid'),
            'pending_amount' => $studentFees->sum('remaining_amount'),
            'collection_percentage' => $studentFees->sum('total_amount') > 0
                ? round(($studentFees->sum('total_paid') / $studentFees->sum('total_amount')) * 100, 2)
                : 0,
        ];
    }

    public function getFeeCollectionReport(string $startDate, string $endDate): array
    {
        $studentFees = StudentFee::whereBetween('paid_date', [$startDate, $endDate])
            ->with(['student.user', 'fee'])
            ->get();
       
        return [
            'total_collections' => $studentFees->sum('total_paid'),
            'total_payments' => $studentFees->count(),
            'collections_by_fee_type' => $studentFees->groupBy('fee.fee_type')->map->sum('total_paid'),
            'collections_by_class' => $studentFees->groupBy('student.currentClass.name')->map->sum('total_paid'),
        ];
    }
}