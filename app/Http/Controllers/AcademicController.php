<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\ClassModel;
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
    // Academic Years endpoints

    /**
     * Display a listing of academic years.
     */
    public function indexAcademicYears(Request $request): JsonResponse
    {
        $query = AcademicYear::query();

        if ($request->has('current_only') && $request->boolean('current_only')) {
            $query->current();
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $academicYears = $query->orderBy('start_date', 'desc')->paginate(15);

        return response()->json($academicYears);
    }

    /**
     * Store a newly created academic year.
     */
    public function storeAcademicYear(StoreAcademicYearRequest $request): JsonResponse
    {
        $academicYear = AcademicYear::create($request->validated());

        return response()->json([
            'message' => 'Academic year created successfully',
            'data' => $academicYear
        ], 201);
    }

    /**
     * Display the specified academic year.
     */
    public function showAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        $academicYear->load('classes');
        
        return response()->json($academicYear);
    }

    /**
     * Update the specified academic year.
     */
    public function updateAcademicYear(UpdateAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $academicYear->update($request->validated());

        return response()->json([
            'message' => 'Academic year updated successfully',
            'data' => $academicYear
        ]);
    }

    /**
     * Remove the specified academic year.
     */
    public function destroyAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        if ($academicYear->is_current) {
            return response()->json([
                'message' => 'Cannot delete current academic year'
            ], 422);
        }

        $academicYear->delete();

        return response()->json([
            'message' => 'Academic year deleted successfully'
        ]);
    }

    // Subjects endpoints

    /**
     * Display a listing of subjects.
     */
    public function indexSubjects(Request $request): JsonResponse
    {
        $query = Subject::query();

        if ($request->has('active_only') && $request->boolean('active_only')) {
            $query->active();
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $subjects = $query->orderBy('name')->paginate(15);

        return response()->json($subjects);
    }

    /**
     * Store a newly created subject.
     */
    public function storeSubject(StoreSubjectRequest $request): JsonResponse
    {
        $subject = Subject::create($request->validated());

        return response()->json([
            'message' => 'Subject created successfully',
            'data' => $subject
        ], 201);
    }

    /**
     * Display the specified subject.
     */
    public function showSubject(Subject $subject): JsonResponse
    {
        return response()->json($subject);
    }

    /**
     * Update the specified subject.
     */
    public function updateSubject(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $subject->update($request->validated());

        return response()->json([
            'message' => 'Subject updated successfully',
            'data' => $subject
        ]);
    }

    /**
     * Remove the specified subject.
     */
    public function destroySubject(Subject $subject): JsonResponse
    {
        $subject->delete();

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
        $query = ClassModel::with(['academicYear', 'teachers']);

        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->has('grade_level')) {
            $query->byGrade($request->grade_level);
        }

        if ($request->has('active_only') && $request->boolean('active_only')) {
            $query->active();
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $classes = $query->orderBy('grade_level')->orderBy('name')->paginate(15);

        return response()->json($classes);
    }

    /**
     * Store a newly created class.
     */
    public function storeClass(StoreClassRequest $request): JsonResponse
    {
        $class = ClassModel::create($request->validated());

        return response()->json([
            'message' => 'Class created successfully',
            'data' => $class->load('academicYear')
        ], 201);
    }

    /**
     * Display the specified class.
     */
    public function showClass(ClassModel $class): JsonResponse
    {
        $class->load(['academicYear', 'teachers', 'students']);
        
        return response()->json($class);
    }

    /**
     * Update the specified class.
     */
    public function updateClass(UpdateClassRequest $request, ClassModel $class): JsonResponse
    {
        $class->update($request->validated());

        return response()->json([
            'message' => 'Class updated successfully',
            'data' => $class->load('academicYear')
        ]);
    }

    /**
     * Remove the specified class.
     */
    public function destroyClass(ClassModel $class): JsonResponse
    {
        $class->delete();

        return response()->json([
            'message' => 'Class deleted successfully'
        ]);
    }

    /**
     * Get classes by academic year.
     */
    public function getClassesByAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        $classes = $academicYear->classes()->active()->get();

        return response()->json($classes);
    }

    /**
     * Get current academic year.
     */
    public function getCurrentAcademicYear(): JsonResponse
    {
        $currentYear = AcademicYear::current()->first();

        return response()->json($currentYear);
    }
}
