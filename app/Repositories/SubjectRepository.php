<?php

namespace App\Repositories;

use App\Models\Subject;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SubjectRepository implements SubjectRepositoryInterface
{
    protected $model;

    public function __construct(Subject $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('name')->paginate($perPage);
    }

    public function find(int $id): ?Subject
    {
        return $this->model->find($id);
    }

    public function create(array $data): Subject
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $subject = $this->find($id);
        if (!$subject) {
            return false;
        }
        return $subject->update($data);
    }

    public function delete(int $id): bool
    {
        $subject = $this->find($id);
        if (!$subject) {
            return false;
        }
        return $subject->delete();
    }

    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
              ->orWhere('code', 'like', '%' . $query . '%');
        })->get();
    }
}
