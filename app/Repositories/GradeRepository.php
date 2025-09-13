<?php

namespace App\Repositories;

use App\Models\Grade;
use App\Repositories\Interfaces\GradeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GradeRepository implements GradeRepositoryInterface
{
    protected $model;

    public function __construct(Grade $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with(['student.user', 'exam.class', 'exam.subject', 'createdBy'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['student.user', 'exam.class', 'exam.subject', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Grade
    {
        return $this->model->find($id);
    }

    public function create(array $data): Grade
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $grade = $this->find($id);
        if (!$grade) {
            return false;
        }
        return $grade->update($data);
    }

    public function delete(int $id): bool
    {
        $grade = $this->find($id);
        if (!$grade) {
            return false;
        }
        return $grade->delete();
    }

    public function getByStudent(int $studentId): Collection
    {
        return $this->model->byStudent($studentId)->get();
    }

    public function getByExam(int $examId): Collection
    {
        return $this->model->byExam($examId)->get();
    }

    public function getByClass(int $classId): Collection
    {
        return $this->model->byClass($classId)->get();
    }

    public function getBySubject(int $subjectId): Collection
    {
        return $this->model->bySubject($subjectId)->get();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->byAcademicYear($academicYearId)->get();
    }

    public function getPassing(): Collection
    {
        return $this->model->passing()->get();
    }

    public function getFailing(): Collection
    {
        return $this->model->failing()->get();
    }

    public function getByStudentAndExam(int $studentId, int $examId): ?Grade
    {
        return $this->model->where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->first();
    }

    public function bulkCreate(array $grades): Collection
    {
        $createdGrades = collect();
        
        foreach ($grades as $gradeData) {
            $grade = $this->create($gradeData);
            $createdGrades->push($grade);
        }
        
        return $createdGrades;
    }
}
