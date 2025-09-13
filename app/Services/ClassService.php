<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Repositories\Interfaces\ClassRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClassService
{
    protected $classRepository;

    public function __construct(ClassRepositoryInterface $classRepository)
    {
        $this->classRepository = $classRepository;
    }

    public function getAllClasses(): Collection
    {
        return $this->classRepository->all();
    }

    public function getPaginatedClasses(int $perPage = 15): LengthAwarePaginator
    {
        return $this->classRepository->paginate($perPage);
    }

    public function getClassById(int $id): ?ClassModel
    {
        return $this->classRepository->find($id);
    }

    public function createClass(array $data): ClassModel
    {
        return $this->classRepository->create($data);
    }

    public function updateClass(int $id, array $data): bool
    {
        return $this->classRepository->update($id, $data);
    }

    public function deleteClass(int $id): bool
    {
        return $this->classRepository->delete($id);
    }

    public function getClassesByAcademicYear(int $academicYearId): Collection
    {
        return $this->classRepository->getByAcademicYear($academicYearId);
    }

    public function getClassesByGrade(int $gradeLevel): Collection
    {
        return $this->classRepository->getByGrade($gradeLevel);
    }

    public function getActiveClasses(): Collection
    {
        return $this->classRepository->getActive();
    }

    public function searchClasses(string $query): Collection
    {
        return $this->classRepository->search($query);
    }
}
