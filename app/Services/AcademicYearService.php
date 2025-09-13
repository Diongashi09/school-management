<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Repositories\Interfaces\AcademicYearRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AcademicYearService
{
    protected $academicYearRepository;

    public function __construct(AcademicYearRepositoryInterface $academicYearRepository)
    {
        $this->academicYearRepository = $academicYearRepository;
    }

    public function getAllAcademicYears(): Collection
    {
        return $this->academicYearRepository->all();
    }

    public function getPaginatedAcademicYears(int $perPage = 15): LengthAwarePaginator
    {
        return $this->academicYearRepository->paginate($perPage);
    }

    public function getAcademicYearById(int $id): ?AcademicYear
    {
        return $this->academicYearRepository->find($id);
    }

    public function createAcademicYear(array $data): AcademicYear
    {
        return $this->academicYearRepository->create($data);
    }

    public function updateAcademicYear(int $id, array $data): bool
    {
        return $this->academicYearRepository->update($id, $data);
    }

    public function deleteAcademicYear(int $id): bool
    {
        $academicYear = $this->getAcademicYearById($id);
        
        if (!$academicYear) {
            return false;
        }

        if ($academicYear->is_current) {
            throw new \Exception('Cannot delete current academic year');
        }

        return $this->academicYearRepository->delete($id);
    }

    public function getCurrentAcademicYear(): ?AcademicYear
    {
        return $this->academicYearRepository->getCurrent();
    }

    public function searchAcademicYears(string $query): Collection
    {
        return $this->academicYearRepository->search($query);
    }
}
