<?php

namespace App\Http\Controllers;

use App\Services\ExamService;
use App\Services\GradeService;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    protected $examService;
    protected $gradeService;

    public function __construct(ExamService $examService, GradeService $gradeService)
    {
        $this->examService = $examService;
        $this->gradeService = $gradeService;
    }

    // Exam Management endpoints

    /**
     * Display a listing of exams.
     */
    public function indexExams(Request $request): JsonResponse
    {
        $query = $this->examService->getPaginatedExams();

        if ($request->has('class_id')) {
            $exams = $this->examService->getExamsByClass($request->class_id);
            return response()->json($exams);
        }

        if ($request->has('subject_id')) {
            $exams = $this->examService->getExamsBySubject($request->subject_id);
            return response()->json($exams);
        }

        if ($request->has('academic_year_id')) {
            $exams = $this->examService->getExamsByAcademicYear($request->academic_year_id);
            return response()->json($exams);
        }

        if ($request->has('exam_type')) {
            $exams = $this->examService->getExamsByType($request->exam_type);
            return response()->json($exams);
        }

        if ($request->has('published_only') && $request->boolean('published_only')) {
            $exams = $this->examService->getPublishedExams();
            return response()->json($exams);
        }

        if ($request->has('upcoming_only') && $request->boolean('upcoming_only')) {
            $exams = $this->examService->getUpcomingExams();
            return response()->json($exams);
        }

        if ($request->has('search')) {
            $exams = $this->examService->searchExams($request->search);
            return response()->json($exams);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created exam.
     */
    public function storeExam(StoreExamRequest $request): JsonResponse
    {
        $exam = $this->examService->createExam($request->validated());

        return response()->json([
            'message' => 'Exam created successfully',
            'data' => $exam->load(['class', 'subject', 'academicYear'])
        ], 201);
    }

    /**
     * Display the specified exam.
     */
    public function showExam(int $id): JsonResponse
    {
        $exam = $this->examService->getExamById($id);
        
        if (!$exam) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $exam->load(['class', 'subject', 'academicYear', 'grades.student.user']);
        
        return response()->json($exam);
    }

    /**
     * Update the specified exam.
     */
    public function updateExam(UpdateExamRequest $request, int $id): JsonResponse
    {
        $updated = $this->examService->updateExam($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Exam not found'], 404);
        }

        $exam = $this->examService->getExamById($id);

        return response()->json([
            'message' => 'Exam updated successfully',
            'data' => $exam->load(['class', 'subject', 'academicYear'])
        ]);
    }

    /**
     * Remove the specified exam.
     */
    public function destroyExam(int $id): JsonResponse
    {
        try {
            $deleted = $this->examService->deleteExam($id);

            if (!$deleted) {
                return response()->json(['message' => 'Exam not found'], 404);
            }

            return response()->json([
                'message' => 'Exam deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Publish/unpublish an exam.
     */
    public function togglePublish(int $id): JsonResponse
    {
        try {
            $exam = $this->examService->togglePublish($id);

            return response()->json([
                'message' => $exam->is_published ? 'Exam published successfully' : 'Exam unpublished successfully',
                'data' => $exam
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get exam statistics.
     */
    public function getExamStatistics(int $id): JsonResponse
    {
        try {
            $stats = $this->examService->getExamStatistics($id);

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 404);
        }
    }

    // Grade Management endpoints

    /**
     * Display a listing of grades.
     */
    public function indexGrades(Request $request): JsonResponse
    {
        $query = $this->gradeService->getPaginatedGrades();

        if ($request->has('student_id')) {
            $grades = $this->gradeService->getGradesByStudent($request->student_id);
            return response()->json($grades);
        }

        if ($request->has('exam_id')) {
            $grades = $this->gradeService->getGradesByExam($request->exam_id);
            return response()->json($grades);
        }

        if ($request->has('class_id')) {
            $grades = $this->gradeService->getGradesByClass($request->class_id);
            return response()->json($grades);
        }

        if ($request->has('subject_id')) {
            $grades = $this->gradeService->getGradesBySubject($request->subject_id);
            return response()->json($grades);
        }

        if ($request->has('academic_year_id')) {
            $grades = $this->gradeService->getGradesByAcademicYear($request->academic_year_id);
            return response()->json($grades);
        }

        if ($request->has('passing_only') && $request->boolean('passing_only')) {
            $grades = $this->gradeService->getPassingGrades();
            return response()->json($grades);
        }

        if ($request->has('failing_only') && $request->boolean('failing_only')) {
            $grades = $this->gradeService->getFailingGrades();
            return response()->json($grades);
        }

        return response()->json($query);
    }

    /**
     * Store a newly created grade.
     */
    public function storeGrade(StoreGradeRequest $request): JsonResponse
    {
        try {
            $grade = $this->gradeService->createGrade($request->validated());

            return response()->json([
                'message' => 'Grade created successfully',
                'data' => $grade->load(['student.user', 'exam', 'createdBy'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified grade.
     */
    public function showGrade(int $id): JsonResponse
    {
        $grade = $this->gradeService->getGradeById($id);
        
        if (!$grade) {
            return response()->json(['message' => 'Grade not found'], 404);
        }

        $grade->load(['student.user', 'exam.class', 'exam.subject', 'createdBy']);
        
        return response()->json($grade);
    }

    /**
     * Update the specified grade.
     */
    public function updateGrade(UpdateGradeRequest $request, int $id): JsonResponse
    {
        $updated = $this->gradeService->updateGrade($id, $request->validated());

        if (!$updated) {
            return response()->json(['message' => 'Grade not found'], 404);
        }

        $grade = $this->gradeService->getGradeById($id);

        return response()->json([
            'message' => 'Grade updated successfully',
            'data' => $grade->load(['student.user', 'exam', 'createdBy'])
        ]);
    }

    /**
     * Remove the specified grade.
     */
    public function destroyGrade(int $id): JsonResponse
    {
        $deleted = $this->gradeService->deleteGrade($id);

        if (!$deleted) {
            return response()->json(['message' => 'Grade not found'], 404);
        }

        return response()->json([
            'message' => 'Grade deleted successfully'
        ]);
    }

    /**
     * Bulk create grades for an exam.
     */
    public function bulkCreateGrades(Request $request, int $examId): JsonResponse
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.obtained_marks' => 'required|numeric|min:0',
            'grades.*.remarks' => 'nullable|string',
        ]);

        try {
            $createdGrades = $this->gradeService->bulkCreateGrades($examId, $request->grades);

            return response()->json([
                'message' => 'Grades created successfully',
                'data' => $createdGrades
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create grades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's grade report.
     */
    public function getStudentGradeReport(int $studentId, Request $request): JsonResponse
    {
        $filters = $request->only(['academic_year_id', 'subject_id']);
        $report = $this->gradeService->getStudentGradeReport($studentId, $filters);

        return response()->json($report);
    }

    /**
     * Get class grade report.
     */
    public function getClassGradeReport(int $classId, Request $request): JsonResponse
    {
        $filters = $request->only(['academic_year_id', 'subject_id']);
        $report = $this->gradeService->getClassGradeReport($classId, $filters);

        return response()->json($report);
    }

    /**
     * Get grade statistics.
     */
    public function getGradeStatistics(): JsonResponse
    {
        $stats = $this->gradeService->getGradeStatistics();

        return response()->json($stats);
    }
}