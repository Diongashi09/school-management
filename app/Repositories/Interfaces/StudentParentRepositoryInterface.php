<?php

namespace App\Repositories\Interfaces;

use App\Models\StudentParent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentParentRepositoryInterface
{
    public function getAllRelationships(): Collection;
    public function getPaginatedRelationships(int $perPage = 15): LengthAwarePaginator;
    public function getRelationshipById(int $id): ?StudentParent;
    public function createRelationship(array $data): StudentParent;
    public function updateRelationship(int $id, array $data): bool;
    public function deleteRelationship(int $id): bool;
    public function getRelationshipsByStudent(int $studentId): Collection;
    public function getRelationshipsByParent(int $parentId): Collection;
    public function getPrimaryContactsByStudent(int $studentId): Collection;
    public function getEmergencyContactsByStudent(int $studentId): Collection;
    public function getPickupAuthorizedByStudent(int $studentId): Collection;
    public function getRelationshipByStudentAndParent(int $studentId, int $parentId): ?StudentParent;
}
