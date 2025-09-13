<?php

namespace App\Repositories;

use App\Models\AcademicYear;
use App\Repositories\Interfaces\AcademicYearRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AcademicYearRepository implements AcademicYearRepositoryInterface
{
    protected $model;

    public function __construct(AcademicYear $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('start_date', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?AcademicYear
    {
        return $this->model->find($id);
    }

    public function create(array $data): AcademicYear
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $academicYear = $this->find($id);
        if (!$academicYear) {
            return false;
        }
        return $academicYear->update($data);
    }

    public function delete(int $id): bool
    {
        $academicYear = $this->find($id);
        if (!$academicYear) {
            return false;
        }
        return $academicYear->delete();
    }

    public function getCurrent(): ?AcademicYear
    {
        return $this->model->current()->first();
    }

    public function search(string $query): Collection
    {
        return $this->model->where('name', 'like', '%' . $query . '%')->get();
    }
}
