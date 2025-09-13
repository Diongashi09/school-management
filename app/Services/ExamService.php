<?php

namespace App\Services;

use App\Models\Exam;
use App\Repositories\Interfaces\ExamRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExamService
{
    protected $examRepository;

    public function __construct(ExamRepositoryInterface $examRepository)
    {
        $this->examRepository = $examRepository;
    }

    public function getAllExams(): Collection
    {
        return $this->examRepository->all();
    }

    public function getPaginatedExams(int $perPage = 15): LengthAwarePaginator
    {
        return $this->examRepository->paginate($perPage);
    }

    public function getExamById(int $id): ?Exam
    {
        return $this->examRepository->find($id);
    }

    public function createExam(array $data): Exam
    {
        return $this->examRepository->create($data);
    }

    public function updateExam(int $id, array $data): bool
    {
        return $this->examRepository->update($id, $data);
    }

    public function deleteExam(int $id): bool
    {
        $exam = $this->getExamById($id);
        
        if (!$exam) {
            return false;
        }

        if ($exam->grades()->exists()) {
            throw new \Exception('Cannot delete exam with existing grades');
        }

        return $this->examRepository->delete($id);
    }

    public function getExamsByClass(int $classId): Collection
    {
        return $this->examRepository->getByClass($classId);
    }

    public function getExamsBySubject(int $subjectId): Collection
    {
        return $this->examRepository->getBySubject($subjectId);
    }

    public function getExamsByAcademicYear(int $academicYearId): Collection
    {
        return $this->examRepository->getByAcademicYear($academicYearId);
    }

    public function getExamsByType(string $type): Collection
    {
        return $this->examRepository->getByType($type);
    }

    public function getPublishedExams(): Collection
    {
        return $this->examRepository->getPublished();
    }

    public function getUpcomingExams(): Collection
    {
        return $this->examRepository->getUpcoming();
    }

    public function searchExams(string $query): Collection
    {
        return $this->examRepository->search($query);
    }

    public function togglePublish(int $id): Exam
    {
        $exam = $this->getExamById($id);
        
        if (!$exam) {
            throw new \Exception('Exam not found');
        }

        $exam->update(['is_published' => !$exam->is_published]);
        
        return $exam;
    }

    public function getExamStatistics(int $id): array
    {
        $exam = $this->getExamById($id);
        
        if (!$exam) {
            throw new \Exception('Exam not found');
        }

        return [
            'total_students' => $exam->grades()->count(),
            'average_marks' => $exam->average_marks,
            'highest_marks' => $exam->highest_marks,
            'lowest_marks' => $exam->lowest_marks,
            'pass_percentage' => $exam->pass_percentage,
            'passing_students' => $exam->grades()->where('obtained_marks', '>=', $exam->passing_marks)->count(),
            'failing_students' => $exam->grades()->where('obtained_marks', '<', $exam->passing_marks)->count(),
        ];
    }
}
