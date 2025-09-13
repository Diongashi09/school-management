<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    // Exam Management endpoints

    /**
     * Display a listing of exams.
     */
    public function indexExams(Request $request): JsonResponse
    {
        $query = Exam::with(['class', 'subject', 'academicYear']);

        if ($request->has('class_id')) {
            $query->byClass($request->class_id);
        }

        if ($request->has('subject_id')) {
            $query->bySubject($request->subject_id);
        }

        if ($request->has('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->has('exam_type')) {
            $query->byType($request->exam_type);
        }

        if ($request->has('published_only') && $request->boolean('published_only')) {
            $query->published();
        }

        if ($request->has('upcoming_only') && $request->boolean('upcoming_only')) {
            $query->upcoming();
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $exams = $query->orderBy('exam_date', 'desc')->paginate(15);

        return response()->json($exams);
    }

    /**
     * Store a newly created exam.
     */
    public function storeExam(StoreExamRequest $request): JsonResponse
    {
        $exam = Exam::create($request->validated());

        return response()->json([
            'message' => 'Exam created successfully',
            'data' => $exam->load(['class', 'subject', 'academicYear'])
        ], 201);
    }

    /**
     * Display the specified exam.
     */
    public function showExam(Exam $exam): JsonResponse
    {
        $exam->load(['class', 'subject', 'academicYear', 'grades.student.user']);
        
        return response()->json($exam);
    }

    /**
     * Update the specified exam.
     */
    public function updateExam(UpdateExamRequest $request, Exam $exam): JsonResponse
    {
        $exam->update($request->validated());

        return response()->json([
            'message' => 'Exam updated successfully',
            'data' => $exam->load(['class', 'subject', 'academicYear'])
        ]);
    }

    /**
     * Remove the specified exam.
     */
    public function destroyExam(Exam $exam): JsonResponse
    {
        if ($exam->grades()->exists()) {
            return response()->json([
                'message' => 'Cannot delete exam with existing grades'
            ], 422);
        }

        $exam->delete();

        return response()->json([
            'message' => 'Exam deleted successfully'
        ]);
    }

    /**
     * Publish/unpublish an exam.
     */
    public function togglePublish(Exam $exam): JsonResponse
    {
        $exam->update(['is_published' => !$exam->is_published]);

        return response()->json([
            'message' => $exam->is_published ? 'Exam published successfully' : 'Exam unpublished successfully',
            'data' => $exam
        ]);
    }

    /**
     * Get exam statistics.
     */
    public function getExamStatistics(Exam $exam): JsonResponse
    {
        $stats = [
            'total_students' => $exam->grades()->count(),
            'average_marks' => $exam->average_marks,
            'highest_marks' => $exam->highest_marks,
            'lowest_marks' => $exam->lowest_marks,
            'pass_percentage' => $exam->pass_percentage,
            'passing_students' => $exam->grades()->where('obtained_marks', '>=', $exam->passing_marks)->count(),
            'failing_students' => $exam->grades()->where('obtained_marks', '<', $exam->passing_marks)->count(),
        ];

        return response()->json($stats);
    }

    // Grade Management endpoints

    /**
     * Display a listing of grades.
     */
    public function indexGrades(Request $request): JsonResponse
    {
        $query = Grade::with(['student.user', 'exam.class', 'exam.subject', 'createdBy']);

        if ($request->has('student_id')) {
            $query->byStudent($request->student_id);
        }

        if ($request->has('exam_id')) {
            $query->byExam($request->exam_id);
        }

        if ($request->has('class_id')) {
            $query->byClass($request->class_id);
        }

        if ($request->has('subject_id')) {
            $query->bySubject($request->subject_id);
        }

        if ($request->has('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->has('passing_only') && $request->boolean('passing_only')) {
            $query->passing();
        }

        if ($request->has('failing_only') && $request->boolean('failing_only')) {
            $query->failing();
        }

        $grades = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($grades);
    }

    /**
     * Store a newly created grade.
     */
    public function storeGrade(StoreGradeRequest $request): JsonResponse
    {
        // Check if grade already exists for this student and exam
        $existingGrade = Grade::where('student_id', $request->student_id)
            ->where('exam_id', $request->exam_id)
            ->first();

        if ($existingGrade) {
            return response()->json([
                'message' => 'Grade already exists for this student and exam'
            ], 422);
        }

        $grade = Grade::create(array_merge($request->validated(), [
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'message' => 'Grade created successfully',
            'data' => $grade->load(['student.user', 'exam', 'createdBy'])
        ], 201);
    }

    /**
     * Display the specified grade.
     */
    public function showGrade(Grade $grade): JsonResponse
    {
        $grade->load(['student.user', 'exam.class', 'exam.subject', 'createdBy']);
        
        return response()->json($grade);
    }

    /**
     * Update the specified grade.
     */
    public function updateGrade(UpdateGradeRequest $request, Grade $grade): JsonResponse
    {
        $grade->update($request->validated());

        return response()->json([
            'message' => 'Grade updated successfully',
            'data' => $grade->load(['student.user', 'exam', 'createdBy'])
        ]);
    }

    /**
     * Remove the specified grade.
     */
    public function destroyGrade(Grade $grade): JsonResponse
    {
        $grade->delete();

        return response()->json([
            'message' => 'Grade deleted successfully'
        ]);
    }

    /**
     * Bulk create grades for an exam.
     */
    public function bulkCreateGrades(Request $request, Exam $exam): JsonResponse
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.obtained_marks' => 'required|numeric|min:0|max:' . $exam->total_marks,
            'grades.*.remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $createdGrades = [];

            foreach ($request->grades as $gradeData) {
                // Check if grade already exists
                $existingGrade = Grade::where('student_id', $gradeData['student_id'])
                    ->where('exam_id', $exam->id)
                    ->first();

                if (!$existingGrade) {
                    $grade = Grade::create([
                        'student_id' => $gradeData['student_id'],
                        'exam_id' => $exam->id,
                        'obtained_marks' => $gradeData['obtained_marks'],
                        'remarks' => $gradeData['remarks'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    $createdGrades[] = $grade->load(['student.user']);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Grades created successfully',
                'data' => $createdGrades
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create grades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's grade report.
     */
    public function getStudentGradeReport(Student $student, Request $request): JsonResponse
    {
        $query = Grade::with(['exam.class', 'exam.subject'])
            ->where('student_id', $student->id);

        if ($request->has('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->has('subject_id')) {
            $query->bySubject($request->subject_id);
        }

        $grades = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_exams' => $grades->count(),
            'average_percentage' => $grades->avg('percentage'),
            'highest_grade' => $grades->max('grade'),
            'lowest_grade' => $grades->min('grade'),
            'passing_exams' => $grades->where('is_passing', true)->count(),
            'failing_exams' => $grades->where('is_passing', false)->count(),
        ];

        return response()->json([
            'student' => $student->load('user'),
            'grades' => $grades,
            'statistics' => $stats
        ]);
    }

    /**
     * Get class grade report.
     */
    public function getClassGradeReport(ClassModel $class, Request $request): JsonResponse
    {
        $query = Grade::with(['student.user', 'exam.subject'])
            ->whereHas('exam', function ($q) use ($class) {
                $q->where('class_id', $class->id);
            });

        if ($request->has('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->has('subject_id')) {
            $query->bySubject($request->subject_id);
        }

        $grades = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_grades' => $grades->count(),
            'average_percentage' => $grades->avg('percentage'),
            'passing_percentage' => $grades->where('is_passing', true)->count() / max($grades->count(), 1) * 100,
            'grade_distribution' => $grades->groupBy('grade')->map->count(),
        ];

        return response()->json([
            'class' => $class->load('academicYear'),
            'grades' => $grades,
            'statistics' => $stats
        ]);
    }

    /**
     * Get grade statistics.
     */
    public function getGradeStatistics(): JsonResponse
    {
        $stats = [
            'total_grades' => Grade::count(),
            'total_exams' => Exam::count(),
            'published_exams' => Exam::published()->count(),
            'upcoming_exams' => Exam::upcoming()->count(),
            'average_grade_percentage' => Grade::avg('percentage'),
            'passing_grades' => Grade::passing()->count(),
            'failing_grades' => Grade::failing()->count(),
        ];

        return response()->json($stats);
    }
}
