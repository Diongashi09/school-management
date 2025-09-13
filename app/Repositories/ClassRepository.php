<?php

namespace App\Repositories;

use App\Models\ClassModel;
use App\Repositories\Interfaces\ClassRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClassRepository implements ClassRepositoryInterface
{
    protected $model;

    public function __construct(ClassModel $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['academicYear', 'teachers'])
            ->orderBy('grade_level')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $id): ?ClassModel
    {
        return $this->model->find($id);
    }

    public function create(array $data): ClassModel
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $class = $this->find($id);
        if (!$class) {
            return false;
        }
        return $class->update($data);
    }

    public function delete(int $id): bool
    {
        $class = $this->find($id);
        if (!$class) {
            return false;
        }
        return $class->delete();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->where('academic_year_id', $academicYearId)->get();
    }

    public function getByGrade(int $gradeLevel): Collection
    {
        return $this->model->byGrade($gradeLevel)->get();
    }

    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->where('name', 'like', '%' . $query . '%')->get();
    }
}
