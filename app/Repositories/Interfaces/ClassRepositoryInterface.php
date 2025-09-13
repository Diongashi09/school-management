<?php

namespace App\Repositories\Interfaces;

use App\Models\ClassModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ClassRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?ClassModel;
    public function create(array $data): ClassModel;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByAcademicYear(int $academicYearId): Collection;
    public function getByGrade(int $gradeLevel): Collection;
    public function getActive(): Collection;
    public function search(string $query): Collection;
}
