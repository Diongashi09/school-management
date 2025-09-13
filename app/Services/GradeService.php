<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ClassModel;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GradeService
{
    protected $gradeRepository;

    public function __construct(GradeRepositoryInterface $gradeRepository)
    {
        $this->gradeRepository = $gradeRepository;
    }

    public function getAllGrades(): Collection
    {
        return $this->gradeRepository->all();
    }

    public function getPaginatedGrades(int $perPage = 15): LengthAwarePaginator
    {
        return $this->gradeRepository->paginate($perPage);
    }

    public function getGradeById(int $id): ?Grade
    {
        return $this->gradeRepository->find($id);
    }

    public function createGrade(array $data): Grade
    {
        // Check if grade already exists for this student and exam
        $existingGrade = $this->gradeRepository->getByStudentAndExam(
            $data['student_id'],
            $data['exam_id']
        );

        if ($existingGrade) {
            throw new \Exception('Grade already exists for this student and exam');
        }

        return $this->gradeRepository->create(array_merge($data, [
            'created_by' => auth()->id(),
        ]));
    }

    public function updateGrade(int $id, array $data): bool
    {
        return $this->gradeRepository->update($id, $data);
    }

    public function deleteGrade(int $id): bool
    {
        return $this->gradeRepository->delete($id);
    }

    public function getGradesByStudent(int $studentId): Collection
    {
        return $this->gradeRepository->getByStudent($studentId);
    }

    public function getGradesByExam(int $examId): Collection
    {
        return $this->gradeRepository->getByExam($examId);
    }

    public function getGradesByClass(int $classId): Collection
    {
        return $this->gradeRepository->getByClass($classId);
    }

    public function getGradesBySubject(int $subjectId): Collection
    {
        return $this->gradeRepository->getBySubject($subjectId);
    }

    public function getGradesByAcademicYear(int $academicYearId): Collection
    {
        return $this->gradeRepository->getByAcademicYear($academicYearId);
    }

    public function getPassingGrades(): Collection
    {
        return $this->gradeRepository->getPassing();
    }

    public function getFailingGrades(): Collection
    {
        return $this->gradeRepository->getFailing();
    }

    public function bulkCreateGrades(int $examId, array $grades): Collection
    {
        DB::beginTransaction();

        try {
            $exam = Exam::findOrFail($examId);
            $createdGrades = collect();

            foreach ($grades as $gradeData) {
                // Check if grade already exists
                $existingGrade = $this->gradeRepository->getByStudentAndExam(
                    $gradeData['student_id'],
                    $examId
                );

                if (!$existingGrade) {
                    $grade = $this->gradeRepository->create([
                        'student_id' => $gradeData['student_id'],
                        'exam_id' => $examId,
                        'obtained_marks' => $gradeData['obtained_marks'],
                        'remarks' => $gradeData['remarks'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    $createdGrades->push($grade->load(['student.user']));
                }
            }

            DB::commit();

            return $createdGrades;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getStudentGradeReport(int $studentId, array $filters = []): array
    {
        $query = Grade::with(['exam.class', 'exam.subject'])
            ->where('student_id', $studentId);

        if (isset($filters['academic_year_id'])) {
            $query->byAcademicYear($filters['academic_year_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->bySubject($filters['subject_id']);
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

        return [
            'student' => Student::with('user')->find($studentId),
            'grades' => $grades,
            'statistics' => $stats
        ];
    }

    public function getClassGradeReport(int $classId, array $filters = []): array
    {
        $query = Grade::with(['student.user', 'exam.subject'])
            ->whereHas('exam', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });

        if (isset($filters['academic_year_id'])) {
            $query->byAcademicYear($filters['academic_year_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->bySubject($filters['subject_id']);
        }

        $grades = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'total_grades' => $grades->count(),
            'average_percentage' => $grades->avg('percentage'),
            'passing_percentage' => $grades->where('is_passing', true)->count() / max($grades->count(), 1) * 100,
            'grade_distribution' => $grades->groupBy('grade')->map->count(),
        ];

        return [
            'class' => ClassModel::with('academicYear')->find($classId),
            'grades' => $grades,
            'statistics' => $stats
        ];
    }

    public function getGradeStatistics(): array
    {
        return [
            'total_grades' => Grade::count(),
            'total_exams' => Exam::count(),
            'published_exams' => Exam::published()->count(),
            'upcoming_exams' => Exam::upcoming()->count(),
            'average_grade_percentage' => Grade::avg('percentage'),
            'passing_grades' => Grade::passing()->count(),
            'failing_grades' => Grade::failing()->count(),
        ];
    }
}
