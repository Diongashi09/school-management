<?php

namespace App\Repositories\Interfaces;

use App\Models\Exam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExamRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Exam;
    public function create(array $data): Exam;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByClass(int $classId): Collection;
    public function getBySubject(int $subjectId): Collection;
    public function getByAcademicYear(int $academicYearId): Collection;
    public function getByType(string $type): Collection;
    public function getPublished(): Collection;
    public function getUpcoming(): Collection;
    public function search(string $query): Collection;
    public function getWithGrades(): Collection;
}
