<?php
// File: app/Repositories/FeeRepository.php

namespace App\Repositories;

use App\Models\Fee;
use App\Repositories\Interfaces\FeeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FeeRepository implements FeeRepositoryInterface
{
    public function all(): Collection
    {
        return Fee::with(['academicYear', 'class'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Fee::with(['academicYear', 'class'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Fee
    {
        return Fee::with(['academicYear', 'class'])->find($id);
    }

    public function create(array $data): Fee
    {
        return Fee::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Fee::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Fee::destroy($id);
    }

    public function getByType(string $type): Collection
    {
        return Fee::with(['academicYear', 'class'])
            ->byType($type)
            ->active()
            ->get();
    }

    public function getByClass(int $classId): Collection
    {
        return Fee::with(['academicYear', 'class'])
            ->byClass($classId)
            ->active()
            ->get();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return Fee::with(['academicYear', 'class'])
            ->byAcademicYear($academicYearId)
            ->active()
            ->get();
    }

    public function getActive(): Collection
    {
        return Fee::with(['academicYear', 'class'])
            ->active()
            ->get();
    }

    public function getMandatory(): Collection
    {
        return Fee::with(['academicYear', 'class'])
            ->mandatory()
            ->active()
            ->get();
    }

    public function getOptional(): Collection
    {
        return Fee::with(['academicYear', 'class'])
            ->optional()
            ->active()
            ->get();
    }

    public function getFeeStatistics(int $academicYearId): array
    {
        $fees = Fee::where('academic_year_id', $academicYearId)->get();
       
        return [
            'total_fees' => $fees->count(),
            'total_amount' => $fees->sum('amount'),
            'mandatory_fees' => $fees->where('is_mandatory', true)->count(),
            'optional_fees' => $fees->where('is_mandatory', false)->count(),
            'active_fees' => $fees->where('is_active', true)->count(),
            'fees_by_type' => $fees->groupBy('fee_type')->map->count(),
        ];
    }

    public function getCollectionStatistics(int $academicYearId): array
    {
        $fees = Fee::where('academic_year_id', $academicYearId)->with('studentFees')->get();
       
        $totalAmount = $fees->sum('amount');
        $collectedAmount = $fees->sum('total_collected');
        $pendingAmount = $fees->sum('total_pending');
       
        return [
            'total_amount' => $totalAmount,
            'collected_amount' => $collectedAmount,
            'pending_amount' => $pendingAmount,
            'collection_percentage' => $totalAmount > 0 ? round(($collectedAmount / $totalAmount) * 100, 2) : 0,
        ];
    }

    public function getOverdueFees(): Collection
    {
        return Fee::with(['academicYear', 'class'])
            ->where('due_date', '<', now())
            ->active()
            ->get();
    }
}