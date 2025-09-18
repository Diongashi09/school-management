<?php
// File: app/Repositories/Interfaces/StudentFeeRepositoryInterface.php

namespace App\Repositories\Interfaces;

use App\Models\StudentFee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentFeeRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?StudentFee;
    public function create(array $data): StudentFee;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
   
    // Student fee specific methods
    public function getByStudent(int $studentId): Collection;
    public function getByStatus(string $status): Collection;
    public function getOverdue(): Collection;
    public function getPaid(): Collection;
    public function getPending(): Collection;
    public function getPartial(): Collection;
   
    // Statistics
    public function getStudentFeeStatistics(int $studentId): array;
    public function getClassFeeStatistics(int $classId): array;
    public function getFeeCollectionReport(string $startDate, string $endDate): array;
}