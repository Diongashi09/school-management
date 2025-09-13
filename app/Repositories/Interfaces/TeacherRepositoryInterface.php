<?php

namespace App\Repositories\Interfaces;

use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Teacher;
    public function create(array $data): Teacher;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByStatus(string $status): Collection;
    public function getBySubject(int $subjectId): Collection;
    public function getByClass(int $classId): Collection;
    public function search(string $query): Collection;
    public function getActive(): Collection;
    public function getWithAssignments(): Collection;
}
