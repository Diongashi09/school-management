<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository implements StudentRepositoryInterface
{
    protected $model;

    public function __construct(Student $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['user', 'currentEnrollment.class', 'currentEnrollment.academicYear'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['user', 'currentEnrollment.class', 'currentEnrollment.academicYear'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Student
    {
        return $this->model->find($id);
    }

    public function create(array $data): Student
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $student = $this->find($id);
        if (!$student) {
            return false;
        }
        return $student->update($data);
    }

    public function delete(int $id): bool
    {
        $student = $this->find($id);
        if (!$student) {
            return false;
        }
        return $student->delete();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->byStatus($status)->get();
    }

    public function getByClass(int $classId): Collection
    {
        return $this->model->whereHas('enrollments', function ($q) use ($classId) {
            $q->where('class_id', $classId)->where('status', 'active');
        })->with(['user', 'currentEnrollment'])->get();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->whereHas('enrollments', function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId)->where('status', 'active');
        })->with(['user', 'currentEnrollment.class'])->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->search($query)->get();
    }

    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }
}
