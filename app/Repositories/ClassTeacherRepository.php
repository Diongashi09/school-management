<?php

namespace App\Repositories;

use App\Models\ClassTeacher;
use App\Repositories\Interfaces\ClassTeacherRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClassTeacherRepository implements ClassTeacherRepositoryInterface
{
    protected $model;

    public function __construct(ClassTeacher $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['teacher.user', 'class', 'subject', 'academicYear'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['teacher.user', 'class', 'subject', 'academicYear'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?ClassTeacher
    {
        return $this->model->find($id);
    }

    public function create(array $data): ClassTeacher
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $classTeacher = $this->find($id);
        if (!$classTeacher) {
            return false;
        }
        return $classTeacher->update($data);
    }

    public function delete(int $id): bool
    {
        $classTeacher = $this->find($id);
        if (!$classTeacher) {
            return false;
        }
        return $classTeacher->delete();
    }

    public function getByTeacher(int $teacherId): Collection
    {
        return $this->model->byTeacher($teacherId)->get();
    }

    public function getByClass(int $classId): Collection
    {
        return $this->model->byClass($classId)->get();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->byAcademicYear($academicYearId)->get();
    }

    public function getBySubject(int $subjectId): Collection
    {
        return $this->model->where('subject_id', $subjectId)->get();
    }

    public function getPrimaryAssignments(): Collection
    {
        return $this->model->where('is_primary', true)->get();
    }

    public function checkExistingAssignment(int $teacherId, int $classId, int $subjectId, int $academicYearId): ?ClassTeacher
    {
        return $this->model->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->first();
    }
}
