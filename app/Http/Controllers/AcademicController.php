<?php

namespace App\Http\Controllers;

use App\Services\AcademicYearService;
use App\Services\SubjectService;
use App\Services\ClassService;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicController extends Controller
{
    protected $academicYearService;
    protected $subjectService;
    protected $classService;

    public function __construct(
        AcademicYearService $academicYearService,
        SubjectService $subjectService,
        ClassService $classService
    ) {
        $this->academicYearService = $academicYearService;
        $this->subjectService = $subjectService;
        $this->classService = $classService;
    }

    // Academic Years endpoints

    /**
     * Display a listing of academic years.
     */
    public function indexAcademicYears(Request $request): JsonResponse
    {
        $query = $this->academicYearService->getPaginatedAcademicYears();

        if ($request->has('current_only') && $request->boolean('current_only')) {
            $currentYear = $this->academicYearService->getCurrentAcademicYear();
            return response()->json($currentYear);
        }

        if ($request->has('search')) {
            $academicYears = $this->academicYearService->searchAcademicYears($request->search);
            return response()->json($academicYears);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created academic year.
     */
    public function storeAcademicYear(StoreAcademicYearRequest $request): JsonResponse
    {
        $academicYear = $this->academicYearService->createAcademicYear($request->validated());

        return response()->json([
            'message' => 'Academic year created successfully',
            'data' => $academicYear
        ], 201);
    }

    /**
     * Display the specified academic year.
     */
    public function showAcademicYear(int $id): JsonResponse
    {
        $academicYear = $this->academicYearService->getAcademicYearById($id);
        
        if (!$academicYear) {
            return response()->json(['message' => 'Academic year not found'], 404);
        }

        $academicYear->load('classes');
        
        return response()->json($academicYear);
    }

    /**
     * Update the specified academic year.
     */
    public function updateAcademicYear(UpdateAcademicYearRequest $request, int $id): JsonResponse
    {
        $updated = $this->academicYearService->updateAcademicYear($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Academic year not found'], 404);
        }

        $academicYear = $this->academicYearService->getAcademicYearById($id);

        return response()->json([
            'message' => 'Academic year updated successfully',
            'data' => $academicYear
        ]);
    }

    /**
     * Remove the specified academic year.
     */
    public function destroyAcademicYear(int $id): JsonResponse
    {
        try {
            $deleted = $this->academicYearService->deleteAcademicYear($id);

            if (!$deleted) {
                return response()->json(['message' => 'Academic year not found'], 404);
            }

            return response()->json([
                'message' => 'Academic year deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // Subjects endpoints

    /**
     * Display a listing of subjects.
     */
    public function indexSubjects(Request $request): JsonResponse
    {
        $query = $this->subjectService->getPaginatedSubjects();

        if ($request->has('active_only') && $request->boolean('active_only')) {
            $subjects = $this->subjectService->getActiveSubjects();
            return response()->json($subjects);
        }

        if ($request->has('search')) {
            $subjects = $this->subjectService->searchSubjects($request->search);
            return response()->json($subjects);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created subject.
     */
    public function storeSubject(StoreSubjectRequest $request): JsonResponse
    {
        $subject = $this->subjectService->createSubject($request->validated());

        return response()->json([
            'message' => 'Subject created successfully',
            'data' => $subject
        ], 201);
    }

    /**
     * Display the specified subject.
     */
    public function showSubject(int $id): JsonResponse
    {
        $subject = $this->subjectService->getSubjectById($id);
        
        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        return response()->json($subject);
    }

    /**
     * Update the specified subject.
     */
    public function updateSubject(UpdateSubjectRequest $request, int $id): JsonResponse
    {
        $updated = $this->subjectService->updateSubject($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        $subject = $this->subjectService->getSubjectById($id);

        return response()->json([
            'message' => 'Subject updated successfully',
            'data' => $subject
        ]);
    }

    /**
     * Remove the specified subject.
     */
    public function destroySubject(int $id): JsonResponse
    {
        $deleted = $this->subjectService->deleteSubject($id);

        if (!$deleted) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        return response()->json([
            'message' => 'Subject deleted successfully'
        ]);
    }

    // Classes endpoints

    /**
     * Display a listing of classes.
     */
    public function indexClasses(Request $request): JsonResponse
    {
        $query = $this->classService->getPaginatedClasses();

        if ($request->has('academic_year_id')) {
            $classes = $this->classService->getClassesByAcademicYear($request->academic_year_id);
            return response()->json($classes);
        }

        if ($request->has('grade_level')) {
            $classes = $this->classService->getClassesByGrade($request->grade_level);
            return response()->json($classes);
        }

        if ($request->has('active_only') && $request->boolean('active_only')) {
            $classes = $this->classService->getActiveClasses();
            return response()->json($classes);
        }

        if ($request->has('search')) {
            $classes = $this->classService->searchClasses($request->search);
            return response()->json($classes);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created class.
     */
    public function storeClass(StoreClassRequest $request): JsonResponse
    {
        $class = $this->classService->createClass($request->validated());

        return response()->json([
            'message' => 'Class created successfully',
            'data' => $class->load('academicYear')
        ], 201);
    }

    /**
     * Display the specified class.
     */
    public function showClass(int $id): JsonResponse
    {
        $class = $this->classService->getClassById($id);
        
        if (!$class) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        $class->load(['academicYear', 'teachers', 'students']);
        
        return response()->json($class);
    }

    /**
     * Update the specified class.
     */
    public function updateClass(UpdateClassRequest $request, int $id): JsonResponse
    {
        $updated = $this->classService->updateClass($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        $class = $this->classService->getClassById($id);

        return response()->json([
            'message' => 'Class updated successfully',
            'data' => $class->load('academicYear')
        ]);
    }

    /**
     * Remove the specified class.
     */
    public function destroyClass(int $id): JsonResponse
    {
        $deleted = $this->classService->deleteClass($id);

        if (!$deleted) {
            return response()->json(['message' => 'Class not found'], 404);
        }

        return response()->json([
            'message' => 'Class deleted successfully'
        ]);
    }

    /**
     * Get classes by academic year.
     */
    public function getClassesByAcademicYear(int $academicYearId): JsonResponse
    {
        $classes = $this->classService->getClassesByAcademicYear($academicYearId);

        return response()->json($classes);
    }

    /**
     * Get current academic year.
     */
    public function getCurrentAcademicYear(): JsonResponse
    {
        $currentYear = $this->academicYearService->getCurrentAcademicYear();

        return response()->json($currentYear);
    }
}