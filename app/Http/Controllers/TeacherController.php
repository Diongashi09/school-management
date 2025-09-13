<?php

namespace App\Http\Controllers;

use App\Services\TeacherService;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Requests\StoreClassTeacherRequest;
use App\Http\Requests\UpdateClassTeacherRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    protected $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    // Teacher Management endpoints

    /**
     * Display a listing of teachers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->teacherService->getPaginatedTeachers();

        if ($request->has('status')) {
            $teachers = $this->teacherService->getTeachersByStatus($request->status);
            return response()->json($teachers);
        }

        if ($request->has('subject_id')) {
            $teachers = $this->teacherService->getTeachersBySubject($request->subject_id);
            return response()->json($teachers);
        }

        if ($request->has('class_id')) {
            $teachers = $this->teacherService->getTeachersByClass($request->class_id);
            return response()->json($teachers);
        }

        if ($request->has('search')) {
            $teachers = $this->teacherService->searchTeachers($request->search);
            return response()->json($teachers);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created teacher.
     */
    public function store(StoreTeacherRequest $request): JsonResponse
    {
        try {
            $teacher = $this->teacherService->createTeacher($request->validated());

            return response()->json([
                'message' => 'Teacher created successfully',
                'data' => $teacher
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(int $id): JsonResponse
    {
        $teacher = $this->teacherService->getTeacherById($id);
        
        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $teacher->load(['user', 'classes.academicYear', 'subjects', 'currentAssignments.class', 'currentAssignments.subject']);
        
        return response()->json($teacher);
    }

    /**
     * Update the specified teacher.
     */
    public function update(UpdateTeacherRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->teacherService->updateTeacher($id, $request->validated());

            if (!$updated) {
                return response()->json(['message' => 'Teacher not found'], 404);
            }

            $teacher = $this->teacherService->getTeacherById($id);

            return response()->json([
                'message' => 'Teacher updated successfully',
                'data' => $teacher->load('user')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified teacher.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->teacherService->deleteTeacher($id);

            if (!$deleted) {
                return response()->json(['message' => 'Teacher not found'], 404);
            }

            return response()->json([
                'message' => 'Teacher deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get teachers by subject.
     */
    public function getBySubject(Request $request, int $subjectId): JsonResponse
    {
        $teachers = $this->teacherService->getTeachersBySubject($subjectId);

        return response()->json($teachers);
    }

    /**
     * Get teachers by class.
     */
    public function getByClass(Request $request, int $classId): JsonResponse
    {
        $teachers = $this->teacherService->getTeachersByClass($classId);

        return response()->json($teachers);
    }

    /**
     * Get teacher statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $stats = $this->teacherService->getTeacherStatistics();

        return response()->json($stats);
    }

    // Class Teacher Assignment endpoints

    /**
     * Display a listing of class teacher assignments.
     */
    public function indexAssignments(Request $request): JsonResponse
    {
        $query = $this->teacherService->getPaginatedAssignments();

        if ($request->has('teacher_id')) {
            $assignments = $this->teacherService->getAssignmentsByTeacher($request->teacher_id);
            return response()->json($assignments);
        }

        if ($request->has('class_id')) {
            $assignments = $this->teacherService->getAssignmentsByClass($request->class_id);
            return response()->json($assignments);
        }

        if ($request->has('academic_year_id')) {
            $assignments = $this->teacherService->getAssignmentsByAcademicYear($request->academic_year_id);
            return response()->json($assignments);
        }

        if ($request->has('is_primary')) {
            $assignments = $this->teacherService->getAllAssignments()
                ->where('is_primary', $request->boolean('is_primary'));
            return response()->json($assignments);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created class teacher assignment.
     */
    public function storeAssignment(StoreClassTeacherRequest $request): JsonResponse
    {
        try {
            $assignment = $this->teacherService->createAssignment($request->validated());

            return response()->json([
                'message' => 'Class teacher assignment created successfully',
                'data' => $assignment->load(['teacher.user', 'class', 'subject', 'academicYear'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified class teacher assignment.
     */
    public function showAssignment(int $id): JsonResponse
    {
        $assignment = $this->teacherService->getAssignmentById($id);
        
        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $assignment->load(['teacher.user', 'class', 'subject', 'academicYear']);
        
        return response()->json($assignment);
    }

    /**
     * Update the specified class teacher assignment.
     */
    public function updateAssignment(UpdateClassTeacherRequest $request, int $id): JsonResponse
    {
        $updated = $this->teacherService->updateAssignment($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $assignment = $this->teacherService->getAssignmentById($id);

        return response()->json([
            'message' => 'Class teacher assignment updated successfully',
            'data' => $assignment->load(['teacher.user', 'class', 'subject', 'academicYear'])
        ]);
    }

    /**
     * Remove the specified class teacher assignment.
     */
    public function destroyAssignment(int $id): JsonResponse
    {
        $deleted = $this->teacherService->deleteAssignment($id);

        if (!$deleted) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        return response()->json([
            'message' => 'Class teacher assignment deleted successfully'
        ]);
    }

    /**
     * Assign teacher to class.
     */
    public function assignToClass(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_primary' => 'sometimes|boolean',
        ]);

        try {
            $assignment = $this->teacherService->createAssignment([
                'teacher_id' => $request->teacher_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'academic_year_id' => $request->academic_year_id,
                'is_primary' => $request->boolean('is_primary', false),
            ]);

            return response()->json([
                'message' => 'Teacher assigned to class successfully',
                'data' => $assignment->load(['teacher.user', 'class', 'subject', 'academicYear'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get available teachers for assignment.
     */
    public function getAvailableTeachers(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $availableTeachers = $this->teacherService->getAvailableTeachers(
            $request->class_id,
            $request->subject_id,
            $request->academic_year_id
        );

        return response()->json($availableTeachers);
    }
}