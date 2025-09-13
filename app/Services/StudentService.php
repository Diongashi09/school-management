<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentService
{
    protected $studentRepository;

    public function __construct(StudentRepositoryInterface $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function getAllStudents(): Collection
    {
        return $this->studentRepository->all();
    }

    public function getPaginatedStudents(int $perPage = 15): LengthAwarePaginator
    {
        return $this->studentRepository->paginate($perPage);
    }

    public function getStudentById(int $id): ?Student
    {
        return $this->studentRepository->find($id);
    }

    public function createStudent(array $data): Student
    {
        DB::beginTransaction();

        try {
            // Create user account first
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'role_id' => 3, // Student role
            ]);

            // Remove user account fields from student data
            unset($data['name'], $data['email'], $data['password'], $data['password_confirmation']);

            // Create student profile
            $student = $this->studentRepository->create(array_merge($data, [
                'user_id' => $user->id,
            ]));

            DB::commit();

            return $student->load('user');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateStudent(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $student = $this->getStudentById($id);
            if (!$student) {
                return false;
            }

            // Update student profile
            $updated = $this->studentRepository->update($id, $data);

            // Update user account if name or email provided
            if (isset($data['name']) || isset($data['email'])) {
                $userData = array_intersect_key($data, array_flip(['name', 'email']));
                $student->user->update($userData);
            }

            DB::commit();

            return $updated;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteStudent(int $id): bool
    {
        $student = $this->getStudentById($id);
        
        if (!$student) {
            return false;
        }

        if ($student->enrollments()->where('status', 'active')->exists()) {
            throw new \Exception('Cannot delete student with active enrollments');
        }

        return $this->studentRepository->delete($id);
    }

    public function getStudentsByStatus(string $status): Collection
    {
        return $this->studentRepository->getByStatus($status);
    }

    public function getStudentsByClass(int $classId): Collection
    {
        return $this->studentRepository->getByClass($classId);
    }

    public function getStudentsByAcademicYear(int $academicYearId): Collection
    {
        return $this->studentRepository->getByAcademicYear($academicYearId);
    }

    public function searchStudents(string $query): Collection
    {
        return $this->studentRepository->search($query);
    }

    public function getActiveStudents(): Collection
    {
        return $this->studentRepository->getActive();
    }

    public function getStudentStatistics(): array
    {
        return [
            'total_students' => Student::count(),
            'active_students' => Student::active()->count(),
            'graduated_students' => Student::byStatus('graduated')->count(),
            'transferred_students' => Student::byStatus('transferred')->count(),
            'enrolled_this_year' => Student::whereHas('enrollments', function ($q) {
                $q->where('academic_year_id', 2) // Current academic year
                  ->where('status', 'active');
            })->count(),
        ];
    }
}
