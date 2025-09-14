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