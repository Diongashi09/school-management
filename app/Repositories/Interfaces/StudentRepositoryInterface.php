<?php

namespace App\Repositories\Interfaces;

use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Student;
    public function create(array $data): Student;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByStatus(string $status): Collection;
    public function getByClass(int $classId): Collection;
    public function getByAcademicYear(int $academicYearId): Collection;
    public function search(string $query): Collection;
    public function getActive(): Collection;
}
