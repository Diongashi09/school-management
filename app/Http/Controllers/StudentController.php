<?php

namespace App\Http\Controllers;

use App\Services\StudentService;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * Display a listing of students.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->studentService->getPaginatedStudents();

        if ($request->has('status')) {
            $students = $this->studentService->getStudentsByStatus($request->status);
            return response()->json($students);
        }

        if ($request->has('class_id')) {
            $students = $this->studentService->getStudentsByClass($request->class_id);
            return response()->json($students);
        }

        if ($request->has('academic_year_id')) {
            $students = $this->studentService->getStudentsByAcademicYear($request->academic_year_id);
            return response()->json($students);
        }

        if ($request->has('search')) {
            $students = $this->studentService->searchStudents($request->search);
            return response()->json($students);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created student.
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        try {
            $student = $this->studentService->createStudent($request->validated());

            return response()->json([
                'message' => 'Student created successfully',
                'data' => $student
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified student.
     */
    public function show(int $id): JsonResponse
    {
        $student = $this->studentService->getStudentById($id);
        
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->load(['user', 'enrollments.class', 'enrollments.academicYear', 'parents']);
        
        return response()->json($student);
    }

    /**
     * Update the specified student.
     */
    public function update(UpdateStudentRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->studentService->updateStudent($id, $request->validated());

            if (!$updated) {
                return response()->json(['message' => 'Student not found'], 404);
            }

            $student = $this->studentService->getStudentById($id);

            return response()->json([
                'message' => 'Student updated successfully',
                'data' => $student->load('user')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified student.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->studentService->deleteStudent($id);

            if (!$deleted) {
                return response()->json(['message' => 'Student not found'], 404);
            }

            return response()->json([
                'message' => 'Student deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get students by class.
     */
    public function getByClass(Request $request, int $classId): JsonResponse
    {
        $students = $this->studentService->getStudentsByClass($classId);

        return response()->json($students);
    }

    /**
     * Get students by academic year.
     */
    public function getByAcademicYear(Request $request, int $academicYearId): JsonResponse
    {
        $students = $this->studentService->getStudentsByAcademicYear($academicYearId);

        return response()->json($students);
    }

    /**
     * Get student statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $stats = $this->studentService->getStudentStatistics();

        return response()->json($stats);
    }
}