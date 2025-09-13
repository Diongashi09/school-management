<?php

namespace App\Repositories;

use App\Models\Teacher;
use App\Repositories\Interfaces\TeacherRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TeacherRepository implements TeacherRepositoryInterface
{
    protected $model;

    public function __construct(Teacher $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['user', 'classes', 'subjects'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['user', 'classes', 'subjects'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Teacher
    {
        return $this->model->find($id);
    }

    public function create(array $data): Teacher
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $teacher = $this->find($id);
        if (!$teacher) {
            return false;
        }
        return $teacher->update($data);
    }

    public function delete(int $id): bool
    {
        $teacher = $this->find($id);
        if (!$teacher) {
            return false;
        }
        return $teacher->delete();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->byStatus($status)->get();
    }

    public function getBySubject(int $subjectId): Collection
    {
        return $this->model->whereHas('subjects', function ($q) use ($subjectId) {
            $q->where('subjects.id', $subjectId);
        })->with(['user', 'classes'])->get();
    }

    public function getByClass(int $classId): Collection
    {
        return $this->model->whereHas('classes', function ($q) use ($classId) {
            $q->where('classes.id', $classId);
        })->with(['user', 'subjects'])->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->search($query)->get();
    }

    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function getWithAssignments(): Collection
    {
        return $this->model->whereHas('currentAssignments')->get();
    }
}
