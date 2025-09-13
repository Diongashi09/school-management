<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Repositories\Interfaces\ExamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExamRepository implements ExamRepositoryInterface
{
    protected $model;

    public function __construct(Exam $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['class', 'subject', 'academicYear'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['class', 'subject', 'academicYear'])
            ->orderBy('exam_date', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Exam
    {
        return $this->model->find($id);
    }

    public function create(array $data): Exam
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $exam = $this->find($id);
        if (!$exam) {
            return false;
        }
        return $exam->update($data);
    }

    public function delete(int $id): bool
    {
        $exam = $this->find($id);
        if (!$exam) {
            return false;
        }
        return $exam->delete();
    }

    public function getByClass(int $classId): Collection
    {
        return $this->model->byClass($classId)->get();
    }

    public function getBySubject(int $subjectId): Collection
    {
        return $this->model->bySubject($subjectId)->get();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->byAcademicYear($academicYearId)->get();
    }

    public function getByType(string $type): Collection
    {
        return $this->model->byType($type)->get();
    }

    public function getPublished(): Collection
    {
        return $this->model->published()->get();
    }

    public function getUpcoming(): Collection
    {
        return $this->model->upcoming()->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->where('name', 'like', '%' . $query . '%')->get();
    }

    public function getWithGrades(): Collection
    {
        return $this->model->whereHas('grades')->get();
    }
}
