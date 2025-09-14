<?php

namespace App\Repositories;

use App\Models\StudentParent;
use App\Repositories\Interfaces\StudentParentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentParentRepository implements StudentParentRepositoryInterface
{
    public function getAllRelationships(): Collection
    {
        return StudentParent::with(['student.user', 'parent.user'])->get();
    }

    public function getPaginatedRelationships(int $perPage = 15): LengthAwarePaginator
    {
        return StudentParent::with(['student.user', 'parent.user'])
            ->paginate($perPage);
    }

    public function getRelationshipById(int $id): ?StudentParent
    {
        return StudentParent::with(['student.user', 'parent.user'])->find($id);
    }

    public function createRelationship(array $data): StudentParent
    {
        return StudentParent::create($data);
    }

    public function updateRelationship(int $id, array $data): bool
    {
        $relationship = StudentParent::find($id);
        if (!$relationship) {
            return false;
        }
        return $relationship->update($data);
    }

    public function deleteRelationship(int $id): bool
    {
        $relationship = StudentParent::find($id);
        if (!$relationship) {
            return false;
        }
        return $relationship->delete();
    }

    public function getRelationshipsByStudent(int $studentId): Collection
    {
        return StudentParent::with(['parent.user'])
            ->where('student_id', $studentId)
            ->get();
    }

    public function getRelationshipsByParent(int $parentId): Collection
    {
        return StudentParent::with(['student.user'])
            ->where('parent_id', $parentId)
            ->get();
    }

    public function getPrimaryContactsByStudent(int $studentId): Collection
    {
        return StudentParent::with(['parent.user'])
            ->where('student_id', $studentId)
            ->where('is_primary_contact', true)
            ->get();
    }

    public function getEmergencyContactsByStudent(int $studentId): Collection
    {
        return StudentParent::with(['parent.user'])
            ->where('student_id', $studentId)
            ->where('is_emergency_contact', true)
            ->get();
    }

    public function getPickupAuthorizedByStudent(int $studentId): Collection
    {
        return StudentParent::with(['parent.user'])
            ->where('student_id', $studentId)
            ->where('can_pickup', true)
            ->get();
    }

    public function getRelationshipByStudentAndParent(int $studentId, int $parentId): ?StudentParent
    {
        return StudentParent::with(['student.user', 'parent.user'])
            ->where('student_id', $studentId)
            ->where('parent_id', $parentId)
            ->first();
    }
}