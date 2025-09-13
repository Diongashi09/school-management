<?php

namespace App\Repositories\Interfaces;

use App\Models\AcademicYear;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AcademicYearRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?AcademicYear;
    public function create(array $data): AcademicYear;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getCurrent(): ?AcademicYear;
    public function search(string $query): Collection;
}
