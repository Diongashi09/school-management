<?php

namespace App\Services;

use App\Models\StudentParent;
use App\Repositories\Interfaces\StudentParentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StudentParentService
{
    protected $studentParentRepository;

    public function __construct(StudentParentRepositoryInterface $studentParentRepository)
    {
        $this->studentParentRepository = $studentParentRepository;
    }

    public function getPaginatedRelationships(): LengthAwarePaginator
    {
        return $this->studentParentRepository->getPaginatedRelationships();
    }

    public function getRelationshipById(int $id): ?StudentParent
    {
        return $this->studentParentRepository->getRelationshipById($id);
    }

    public function createRelationship(array $data): StudentParent
    {
        return DB::transaction(function () use ($data) {
            // Check if relationship already exists
            $existing = $this->studentParentRepository->getRelationshipByStudentAndParent(
                $data['student_id'],
                $data['parent_id']
            );

            if ($existing) {
                throw new \Exception('Relationship already exists between this student and parent.');
            }

            // If this is set as primary contact, remove primary status from other relationships
            if ($data['is_primary_contact'] ?? false) {
                $this->removePrimaryContactStatus($data['student_id']);
            }

            // If this is set as emergency contact, remove emergency status from other relationships
            if ($data['is_emergency_contact'] ?? false) {
                $this->removeEmergencyContactStatus($data['student_id']);
            }

            return $this->studentParentRepository->createRelationship($data);
        });
    }

    public function updateRelationship(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $relationship = $this->studentParentRepository->getRelationshipById($id);
            if (!$relationship) {
                return false;
            }

            // If this is set as primary contact, remove primary status from other relationships
            if ($data['is_primary_contact'] ?? false) {
                $this->removePrimaryContactStatus($relationship->student_id, $id);
            }

            // If this is set as emergency contact, remove emergency status from other relationships
            if ($data['is_emergency_contact'] ?? false) {
                $this->removeEmergencyContactStatus($relationship->student_id, $id);
            }

            return $this->studentParentRepository->updateRelationship($id, $data);
        });
    }

    public function deleteRelationship(int $id): bool
    {
        return $this->studentParentRepository->deleteRelationship($id);
    }

    public function getRelationshipsByStudent(int $studentId): Collection
    {
        return $this->studentParentRepository->getRelationshipsByStudent($studentId);
    }

    public function getRelationshipsByParent(int $parentId): Collection
    {
        return $this->studentParentRepository->getRelationshipsByParent($parentId);
    }

    public function getPrimaryContactsByStudent(int $studentId): Collection
    {
        return $this->studentParentRepository->getPrimaryContactsByStudent($studentId);
    }

    public function getEmergencyContactsByStudent(int $studentId): Collection
    {
        return $this->studentParentRepository->getEmergencyContactsByStudent($studentId);
    }

    public function getPickupAuthorizedByStudent(int $studentId): Collection
    {
        return $this->studentParentRepository->getPickupAuthorizedByStudent($studentId);
    }

    public function getRelationshipByStudentAndParent(int $studentId, int $parentId): ?StudentParent
    {
        return $this->studentParentRepository->getRelationshipByStudentAndParent($studentId, $parentId);
    }

    public function assignParentToStudent(int $studentId, int $parentId, array $additionalData = []): StudentParent
    {
        $data = array_merge([
            'student_id' => $studentId,
            'parent_id' => $parentId,
        ], $additionalData);

        return $this->createRelationship($data);
    }

    public function removeParentFromStudent(int $studentId, int $parentId): bool
    {
        $relationship = $this->getRelationshipByStudentAndParent($studentId, $parentId);
        if (!$relationship) {
            return false;
        }

        return $this->deleteRelationship($relationship->id);
    }

    protected function removePrimaryContactStatus(int $studentId, int $excludeId = null): void
    {
        $query = $this->studentParentRepository->getRelationshipsByStudent($studentId);
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        $query->each(function ($relationship) {
            $this->studentParentRepository->updateRelationship($relationship->id, [
                'is_primary_contact' => false
            ]);
        });
    }

    protected function removeEmergencyContactStatus(int $studentId, int $excludeId = null): void
    {
        $query = $this->studentParentRepository->getRelationshipsByStudent($studentId);
        if ($excludeId) {
            $query = $query->where('id', '!=', $excludeId);
        }
        $query->each(function ($relationship) {
            $this->studentParentRepository->updateRelationship($relationship->id, [
                'is_emergency_contact' => false
            ]);
        });
    }
}