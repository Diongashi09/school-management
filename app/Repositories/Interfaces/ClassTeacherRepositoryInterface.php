<?php

namespace App\Repositories\Interfaces;

use App\Models\ClassTeacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ClassTeacherRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?ClassTeacher;
    public function create(array $data): ClassTeacher;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByTeacher(int $teacherId): Collection;
    public function getByClass(int $classId): Collection;
    public function getByAcademicYear(int $academicYearId): Collection;
    public function getBySubject(int $subjectId): Collection;
    public function getPrimaryAssignments(): Collection;
    public function checkExistingAssignment(int $teacherId, int $classId, int $subjectId, int $academicYearId): ?ClassTeacher;
}
