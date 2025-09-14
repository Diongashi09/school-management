<?php

namespace App\Services;

use App\Models\Parent;
use App\Models\User;
use App\Repositories\Interfaces\ParentRepositoryInterface;
use App\Repositories\Interfaces\StudentParentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentService
{
    protected $parentRepository;
    protected $studentParentRepository;

    public function __construct(
        ParentRepositoryInterface $parentRepository,
        StudentParentRepositoryInterface $studentParentRepository
    ) {
        $this->parentRepository = $parentRepository;
        $this->studentParentRepository = $studentParentRepository;
    }

    public function getPaginatedParents(): LengthAwarePaginator
    {
        return $this->parentRepository->getPaginatedParents();
    }

    public function getParentById(int $id): ?Parent
    {
        return $this->parentRepository->getParentById($id);
    }

    public function createParent(array $data): Parent
    {
        return DB::transaction(function () use ($data) {
            // Create user account
            $user = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password123'),
            ]);

            // Assign parent role
            $user->assignRole('parent');

            // Create parent profile
            $parentData = $data;
            $parentData['user_id'] = $user->id;
            unset($parentData['password']);

            return $this->parentRepository->createParent($parentData);
        });
    }

    public function updateParent(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $parent = $this->parentRepository->getParentById($id);
            if (!$parent) {
                return false;
            }

            // Update user account if email changed
            if (isset($data['email']) && $data['email'] !== $parent->user->email) {
                $parent->user->update(['email' => $data['email']]);
            }

            // Update parent profile
            return $this->parentRepository->updateParent($id, $data);
        });
    }

    public function deleteParent(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $parent = $this->parentRepository->getParentById($id);
            if (!$parent) {
                return false;
            }

            // Delete all student relationships first
            $this->studentParentRepository->getRelationshipsByParent($id)
                ->each(function ($relationship) {
                    $this->studentParentRepository->deleteRelationship($relationship->id);
                });

            // Delete parent profile
            $this->parentRepository->deleteParent($id);

            // Delete user account
            $parent->user->delete();

            return true;
        });
    }

    public function searchParents(string $search): Collection
    {
        return $this->parentRepository->searchParents($search);
    }

    public function getParentsByStatus(string $status): Collection
    {
        return $this->parentRepository->getParentsByStatus($status);
    }

    public function getParentsByRelationship(string $relationship): Collection
    {
        return $this->parentRepository->getParentsByRelationship($relationship);
    }

    public function getActiveParents(): Collection
    {
        return $this->parentRepository->getActiveParents();
    }

    public function getParentsByStudent(int $studentId): Collection
    {
        return $this->parentRepository->getParentsByStudent($studentId);
    }

    public function getPrimaryContacts(): Collection
    {
        return $this->parentRepository->getPrimaryContacts();
    }

    public function getEmergencyContacts(): Collection
    {
        return $this->parentRepository->getEmergencyContacts();
    }

    public function getParentStatistics(): array
    {
        $totalParents = $this->parentRepository->getAllParents()->count();
        $activeParents = $this->parentRepository->getActiveParents()->count();
        $inactiveParents = $totalParents - $activeParents;
        $primaryContacts = $this->parentRepository->getPrimaryContacts()->count();
        $emergencyContacts = $this->parentRepository->getEmergencyContacts()->count();

        return [
            'total_parents' => $totalParents,
            'active_parents' => $activeParents,
            'inactive_parents' => $inactiveParents,
            'primary_contacts' => $primaryContacts,
            'emergency_contacts' => $emergencyContacts,
        ];
    }
}