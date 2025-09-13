<?php

namespace App\Repositories\Interfaces;

use App\Models\Grade;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface GradeRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Grade;
    public function create(array $data): Grade;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByStudent(int $studentId): Collection;
    public function getByExam(int $examId): Collection;
    public function getByClass(int $classId): Collection;
    public function getBySubject(int $subjectId): Collection;
    public function getByAcademicYear(int $academicYearId): Collection;
    public function getPassing(): Collection;
    public function getFailing(): Collection;
    public function getByStudentAndExam(int $studentId, int $examId): ?Grade;
    public function bulkCreate(array $grades): Collection;
}
