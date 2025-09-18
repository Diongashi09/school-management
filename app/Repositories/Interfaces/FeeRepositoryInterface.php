<?php
// File: app/Repositories/Interfaces/FeeRepositoryInterface.php

namespace App\Repositories\Interfaces;

use App\Models\Fee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FeeRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Fee;
    public function create(array $data): Fee;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
   
    // Fee specific methods
    public function getByType(string $type): Collection;
    public function getByClass(int $classId): Collection;
    public function getByAcademicYear(int $academicYearId): Collection;
    public function getActive(): Collection;
    public function getMandatory(): Collection;
    public function getOptional(): Collection;
   
    // Statistics
    public function getFeeStatistics(int $academicYearId): array;
    public function getCollectionStatistics(int $academicYearId): array;
    public function getOverdueFees(): Collection;
}