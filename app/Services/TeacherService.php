<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use App\Repositories\Interfaces\TeacherRepositoryInterface;
use App\Repositories\Interfaces\ClassTeacherRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TeacherService
{
    protected $teacherRepository;
    protected $classTeacherRepository;

    public function __construct(
        TeacherRepositoryInterface $teacherRepository,
        ClassTeacherRepositoryInterface $classTeacherRepository
    ) {
        $this->teacherRepository = $teacherRepository;
        $this->classTeacherRepository = $classTeacherRepository;
    }

    public function getAllTeachers(): Collection
    {
        return $this->teacherRepository->all();
    }

    public function getPaginatedTeachers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->teacherRepository->paginate($perPage);
    }

    public function getTeacherById(int $id): ?Teacher
    {
        return $this->teacherRepository->find($id);
    }

    public function createTeacher(array $data): Teacher
    {
        DB::beginTransaction();

        try {
            // Create user account first
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'role_id' => 2, // Teacher role
            ]);

            // Remove user account fields from teacher data
            unset($data['name'], $data['email'], $data['password'], $data['password_confirmation']);

            // Create teacher profile
            $teacher = $this->teacherRepository->create(array_merge($data, [
                'user_id' => $user->id,
            ]));

            DB::commit();

            return $teacher->load('user');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTeacher(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $teacher = $this->getTeacherById($id);
            if (!$teacher) {
                return false;
            }

            // Update teacher profile
            $updated = $this->teacherRepository->update($id, $data);

            // Update user account if name or email provided
            if (isset($data['name']) || isset($data['email'])) {
                $userData = array_intersect_key($data, array_flip(['name', 'email']));
                $teacher->user->update($userData);
            }

            DB::commit();

            return $updated;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteTeacher(int $id): bool
    {
        $teacher = $this->getTeacherById($id);
        
        if (!$teacher) {
            return false;
        }

        if ($teacher->currentAssignments()->exists()) {
            throw new \Exception('Cannot delete teacher with active class assignments');
        }

        return $this->teacherRepository->delete($id);
    }

    public function getTeachersByStatus(string $status): Collection
    {
        return $this->teacherRepository->getByStatus($status);
    }

    public function getTeachersBySubject(int $subjectId): Collection
    {
        return $this->teacherRepository->getBySubject($subjectId);
    }

    public function getTeachersByClass(int $classId): Collection
    {
        return $this->teacherRepository->getByClass($classId);
    }

    public function searchTeachers(string $query): Collection
    {
        return $this->teacherRepository->search($query);
    }

    public function getActiveTeachers(): Collection
    {
        return $this->teacherRepository->getActive();
    }

    public function getTeachersWithAssignments(): Collection
    {
        return $this->teacherRepository->getWithAssignments();
    }

    public function getTeacherStatistics(): array
    {
        return [
            'total_teachers' => Teacher::count(),
            'active_teachers' => Teacher::active()->count(),
            'inactive_teachers' => Teacher::byStatus('inactive')->count(),
            'terminated_teachers' => Teacher::byStatus('terminated')->count(),
            'teachers_with_assignments' => Teacher::whereHas('currentAssignments')->count(),
            'average_experience' => Teacher::active()->avg(DB::raw('DATEDIFF(NOW(), hire_date) / 365')),
        ];
    }

    // Class Teacher Assignment methods

    public function getAllAssignments(): Collection
    {
        return $this->classTeacherRepository->all();
    }

    public function getPaginatedAssignments(int $perPage = 15): LengthAwarePaginator
    {
        return $this->classTeacherRepository->paginate($perPage);
    }

    public function getAssignmentById(int $id): ?\App\Models\ClassTeacher
    {
        return $this->classTeacherRepository->find($id);
    }

    public function createAssignment(array $data): \App\Models\ClassTeacher
    {
        // Check if teacher is already assigned to the same class and subject for the same academic year
        $existingAssignment = $this->classTeacherRepository->checkExistingAssignment(
            $data['teacher_id'],
            $data['class_id'],
            $data['subject_id'],
            $data['academic_year_id']
        );

        if ($existingAssignment) {
            throw new \Exception('Teacher is already assigned to this class and subject for this academic year');
        }

        return $this->classTeacherRepository->create($data);
    }

    public function updateAssignment(int $id, array $data): bool
    {
        return $this->classTeacherRepository->update($id, $data);
    }

    public function deleteAssignment(int $id): bool
    {
        return $this->classTeacherRepository->delete($id);
    }

    public function getAssignmentsByTeacher(int $teacherId): Collection
    {
        return $this->classTeacherRepository->getByTeacher($teacherId);
    }

    public function getAssignmentsByClass(int $classId): Collection
    {
        return $this->classTeacherRepository->getByClass($classId);
    }

    public function getAssignmentsByAcademicYear(int $academicYearId): Collection
    {
        return $this->classTeacherRepository->getByAcademicYear($academicYearId);
    }

    public function getAvailableTeachers(int $classId, int $subjectId, int $academicYearId): Collection
    {
        $assignedTeacherIds = $this->classTeacherRepository->getByClass($classId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->pluck('teacher_id');

        return Teacher::with('user')
            ->whereNotIn('id', $assignedTeacherIds)
            ->active()
            ->get();
    }
}
