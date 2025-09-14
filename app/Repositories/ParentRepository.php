<?php

namespace App\Repositories;

use App\Models\Parent;
use App\Repositories\Interfaces\ParentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ParentRepository implements ParentRepositoryInterface
{
    public function getAllParents(): Collection
    {
        return Parent::with(['user', 'students.user'])->get();
    }

    public function getPaginatedParents(int $perPage = 15): LengthAwarePaginator
    {
        return Parent::with(['user', 'students.user'])
            ->paginate($perPage);
    }

    public function getParentById(int $id): ?Parent
    {
        return Parent::with(['user', 'students.user'])->find($id);
    }

    public function createParent(array $data): Parent
    {
        return Parent::create($data);
    }

    public function updateParent(int $id, array $data): bool
    {
        $parent = Parent::find($id);
        if (!$parent) {
            return false;
        }
        return $parent->update($data);
    }

    public function deleteParent(int $id): bool
    {
        $parent = Parent::find($id);
        if (!$parent) {
            return false;
        }
        return $parent->delete();
    }

    public function searchParents(string $search): Collection
    {
        return Parent::with(['user', 'students.user'])
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->get();
    }

    public function getParentsByStatus(string $status): Collection
    {
        $isActive = $status === 'active';
        return Parent::with(['user', 'students.user'])
            ->where('is_active', $isActive)
            ->get();
    }

    public function getParentsByRelationship(string $relationship): Collection
    {
        return Parent::with(['user', 'students.user'])
            ->where('relationship', $relationship)
            ->get();
    }

    public function getActiveParents(): Collection
    {
        return Parent::with(['user', 'students.user'])
            ->where('is_active', true)
            ->get();
    }

    public function getParentsByStudent(int $studentId): Collection
    {
        return Parent::with(['user'])
            ->whereHas('students', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->get();
    }

    public function getPrimaryContacts(): Collection
    {
        return Parent::with(['user', 'students.user'])
            ->where('is_primary_contact', true)
            ->get();
    }

    public function getEmergencyContacts(): Collection
    {
        return Parent::with(['user', 'students.user'])
            ->where('is_emergency_contact', true)
            ->get();
    }
}
```

```php:app/Repositories/StudentParentRepository.php
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
```

```php:app/Services/ParentService.php
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
```

```php:app/Services/StudentParentService.php
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
```

## **6. Form Request Validation**

```php:app/Http/Requests/StoreParentRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'workplace' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'relationship' => 'nullable|string|in:parent,guardian,step-parent,other',
            'is_primary_contact' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
            'relationship.in' => 'Invalid relationship type.',
        ];
    }
}
```

```php:app/Http/Requests/UpdateParentRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parentId = $this->route('parent');
        
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($parentId, 'id')
            ],
            'phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'workplace' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'relationship' => 'nullable|string|in:parent,guardian,step-parent,other',
            'is_primary_contact' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a string.',
            'last_name.string' => 'Last name must be a string.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'relationship.in' => 'Invalid relationship type.',
        ];
    }
}
```

```php:app/Http/Requests/StoreStudentParentRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'parent_id' => 'required|exists:parents,id',
            'relationship' => 'nullable|string|in:parent,guardian,step-parent,other',
            'is_primary_contact' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'can_pickup' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Student is required.',
            'student_id.exists' => 'Selected student does not exist.',
            'parent_id.required' => 'Parent is required.',
            'parent_id.exists' => 'Selected parent does not exist.',
            'relationship.in' => 'Invalid relationship type.',
        ];
    }
}
```

```php:app/Http/Requests/UpdateStudentParentRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relationship' => 'nullable|string|in:parent,guardian,step-parent,other',
            'is_primary_contact' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'can_pickup' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'relationship.in' => 'Invalid relationship type.',
        ];
    }
}
```

## **7. Controllers**

```php:app/Http/Controllers/ParentController.php
<?php

namespace App\Http\Controllers;

use App\Services\ParentService;
use App\Services\StudentParentService;
use App\Http\Requests\StoreParentRequest;
use App\Http\Requests\UpdateParentRequest;
use App\Http\Requests\StoreStudentParentRequest;
use App\Http\Requests\UpdateStudentParentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    protected $parentService;
    protected $studentParentService;

    public function __construct(ParentService $parentService, StudentParentService $studentParentService)
    {
        $this->parentService = $parentService;
        $this->studentParentService = $studentParentService;
    }

    // Parent Management endpoints

    /**
     * Display a listing of parents.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->parentService->getPaginatedParents();

        if ($request->has('status')) {
            $parents = $this->parentService->getParentsByStatus($request->status);
            return response()->json($parents);
        }

        if ($request->has('relationship')) {
            $parents = $this->parentService->getParentsByRelationship($request->relationship);
            return response()->json($parents);
        }

        if ($request->has('active_only') && $request->boolean('active_only')) {
            $parents = $this->parentService->getActiveParents();
            return response()->json($parents);
        }

        if ($request->has('search')) {
            $parents = $this->parentService->searchParents($request->search);
            return response()->json($parents);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created parent.
     */
    public function store(StoreParentRequest $request): JsonResponse
    {
        try {
            $parent = $this->parentService->createParent($request->validated());

            return response()->json([
                'message' => 'Parent created successfully',
                'data' => $parent->load('user')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create parent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified parent.
     */
    public function show(int $id): JsonResponse
    {
        $parent = $this->parentService->getParentById($id);
        
        if (!$parent) {
            return response()->json(['message' => 'Parent not found'], 404);
        }

        return response()->json($parent);
    }

    /**
     * Update the specified parent.
     */
    public function update(UpdateParentRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->parentService->updateParent($id, $request->validated());

            if (!$updated) {
                return response()->json(['message' => 'Parent not found'], 404);
            }

            $parent = $this->parentService->getParentById($id);

            return response()->json([
                'message' => 'Parent updated successfully',
                'data' => $parent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update parent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified parent.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->parentService->deleteParent($id);

            if (!$deleted) {
                return response()->json(['message' => 'Parent not found'], 404);
            }

            return response()->json([
                'message' => 'Parent deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get parents by student.
     */
    public function getByStudent(int $studentId): JsonResponse
    {
        $parents = $this->parentService->getParentsByStudent($studentId);

        return response()->json($parents);
    }

    /**
     * Get primary contacts.
     */
    public function getPrimaryContacts(): JsonResponse
    {
        $parents = $this->parentService->getPrimaryContacts();

        return response()->json($parents);
    }

    /**
     * Get emergency contacts.
     */
    public function getEmergencyContacts(): JsonResponse
    {
        $parents = $this->parentService->getEmergencyContacts();

        return response()->json($parents);
    }

    /**
     * Get parent statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $stats = $this->parentService->getParentStatistics();

        return response()->json($stats);
    }

    // Student-Parent Relationship endpoints

    /**
     * Display a listing of student-parent relationships.
     */
    public function indexRelationships(Request $request): JsonResponse
    {
        $query = $this->studentParentService->getPaginatedRelationships();

        if ($request->has('student_id')) {
            $relationships = $this->studentParentService->getRelationshipsByStudent($request->student_id);
            return response()->json($relationships);
        }

        if ($request->has('parent_id')) {
            $relationships = $this->studentParentService->getRelationshipsByParent($request->parent_id);
            return response()->json($relationships);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created student-parent relationship.
     */
    public function storeRelationship(StoreStudentParentRequest $request): JsonResponse
    {
        try {
            $relationship = $this->studentParentService->createRelationship($request->validated());

            return response()->json([
                'message' => 'Student-parent relationship created successfully',
                'data' => $relationship->load(['student.user', 'parent.user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified student-parent relationship.
     */
    public function showRelationship(int $id): JsonResponse
    {
        $relationship = $this->studentParentService->getRelationshipById($id);
        
        if (!$relationship) {
            return response()->json(['message' => 'Relationship not found'], 404);
        }

        return response()->json($relationship);
    }

    /**
     * Update the specified student-parent relationship.
     */
    public function updateRelationship(UpdateStudentParentRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->studentParentService->updateRelationship($id, $request->validated());

            if (!$updated) {
                return response()->json(['message' => 'Relationship not found'], 404);
            }

            $relationship = $this->studentParentService->getRelationshipById($id);

            return response()->json([
                'message' => 'Student-parent relationship updated successfully',
                'data' => $relationship
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified student-parent relationship.
     */
    public function destroyRelationship(int $id): JsonResponse
    {
        $deleted = $this->studentParentService->deleteRelationship($id);

        if (!$deleted) {
            return response()->json(['message' => 'Relationship not found'], 404);
        }

        return response()->json([
            'message' => 'Student-parent relationship deleted successfully'
        ]);
    }

    /**
     * Assign parent to student.
     */
    public function assignToStudent(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'parent_id' => 'required|exists:parents,id',
            'relationship' => 'nullable|string|in:parent,guardian,step-parent,other',
            'is_primary_contact' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'can_pickup' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $relationship = $this->studentParentService->assignParentToStudent(
                $request->student_id,
                $request->parent_id,
                $request->only(['relationship', 'is_primary_contact', 'is_emergency_contact', 'can_pickup', 'notes'])
            );

            return response()->json([
                'message' => 'Parent assigned to student successfully',
                'data' => $relationship->load(['student.user', 'parent.user'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove parent from student.
     */
    public function removeFromStudent(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'parent_id' => 'required|exists:parents,id',
        ]);

        try {
            $removed = $this->studentParentService->removeParentFromStudent(
                $request->student_id,
                $request->parent_id
            );

            if (!$removed) {
                return response()->json(['message' => 'Relationship not found'], 404);
            }

            return response()->json([
                'message' => 'Parent removed from student successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
```

## **8. Update Repository Service Provider**

```php:app/Providers/RepositoryServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\AcademicYearRepositoryInterface;
use App\Repositories\AcademicYearRepository;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use App\Repositories\SubjectRepository;
use App\Repositories\Interfaces\ClassRepositoryInterface;
use App\Repositories\ClassRepository;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\StudentRepository;
use App\Repositories\Interfaces\TeacherRepositoryInterface;
use App\Repositories\TeacherRepository;
use App\Repositories\Interfaces\ClassTeacherRepositoryInterface;
use App\Repositories\ClassTeacherRepository;
use App\Repositories\Interfaces\ExamRepositoryInterface;
use App\Repositories\ExamRepository;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Repositories\GradeRepository;
use App\Repositories\Interfaces\ParentRepositoryInterface;
use App\Repositories\ParentRepository;
use App\Repositories\Interfaces\StudentParentRepositoryInterface;
use App\Repositories\StudentParentRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Academic Management
        $this->app->bind(AcademicYearRepositoryInterface::class, AcademicYearRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, SubjectRepository::class);
        $this->app->bind(ClassRepositoryInterface::class, ClassRepository::class);

        // Student Management
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);

        // Teacher Management
        $this->app->bind(TeacherRepositoryInterface::class, TeacherRepository::class);
        $this->app->bind(ClassTeacherRepositoryInterface::class, ClassTeacherRepository::class);

        // Grade Management
        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
        $this->app->bind(GradeRepositoryInterface::class, GradeRepository::class);

        // Parent Management
        $this->app->bind(ParentRepositoryInterface::class, ParentRepository::class);
        $this->app->bind(StudentParentRepositoryInterface::class, StudentParentRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
```

## **9. API Routes**

```php:routes/api.php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ParentController;

// ... existing code ...

// Parent Management Routes
Route::middleware(['auth:sanctum', 'permission:manage_parents'])->group(function () {
    // Parent CRUD
    Route::get('/parents', [ParentController::class, 'index']);
    Route::post('/parents', [ParentController::class, 'store']);
    Route::get('/parents/{parent}', [ParentController::class, 'show']);
    Route::put('/parents/{parent}', [ParentController::class, 'update']);
    Route::delete('/parents/{parent}', [ParentController::class, 'destroy']);
    
    // Parent specific endpoints
    Route::get('/parents/student/{studentId}', [ParentController::class, 'getByStudent']);
    Route::get('/parents/primary-contacts', [ParentController::class, 'getPrimaryContacts']);
    Route::get('/parents/emergency-contacts', [ParentController::class, 'getEmergencyContacts']);
    Route::get('/parents/statistics', [ParentController::class, 'getStatistics']);
    
    // Student-Parent Relationships
    Route::get('/student-parents', [ParentController::class, 'indexRelationships']);
    Route::post('/student-parents', [ParentController::class, 'storeRelationship']);
    Route::get('/student-parents/{id}', [ParentController::class, 'showRelationship']);
    Route::put('/student-parents/{id}', [ParentController::class, 'updateRelationship']);
    Route::delete('/student-parents/{id}', [ParentController::class, 'destroyRelationship']);
    
    // Assignment operations
    Route::post('/parents/assign-to-student', [ParentController::class, 'assignToStudent']);
    Route::post('/parents/remove-from-student', [ParentController::class, 'removeFromStudent']);
});

// ... existing code ...
```

## **10. Database Seeder**

```php:database/seeders/ParentSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parent;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentParent;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        // Create parent users
        $parentUsers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Michael
