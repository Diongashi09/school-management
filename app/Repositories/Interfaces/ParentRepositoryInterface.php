<?php

namespace App\Repositories\Interfaces;

use App\Models\Parent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ParentRepositoryInterface
{
    public function getAllParents(): Collection;
    public function getPaginatedParents(int $perPage = 15): LengthAwarePaginator;
    public function getParentById(int $id): ?Parent;
    public function createParent(array $data): Parent;
    public function updateParent(int $id, array $data): bool;
    public function deleteParent(int $id): bool;
    public function searchParents(string $search): Collection;
    public function getParentsByStatus(string $status): Collection;
    public function getParentsByRelationship(string $relationship): Collection;
    public function getActiveParents(): Collection;
    public function getParentsByStudent(int $studentId): Collection;
    public function getPrimaryContacts(): Collection;
    public function getEmergencyContacts(): Collection;
}
