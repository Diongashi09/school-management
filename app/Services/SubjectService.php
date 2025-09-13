<?php

namespace App\Services;

use App\Models\Subject;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SubjectService
{
    protected $subjectRepository;

    public function __construct(SubjectRepositoryInterface $subjectRepository)
    {
        $this->subjectRepository = $subjectRepository;
    }

    public function getAllSubjects(): Collection
    {
        return $this->subjectRepository->all();
    }

    public function getPaginatedSubjects(int $perPage = 15): LengthAwarePaginator
    {
        return $this->subjectRepository->paginate($perPage);
    }

    public function getSubjectById(int $id): ?Subject
    {
        return $this->subjectRepository->find($id);
    }

    public function createSubject(array $data): Subject
    {
        return $this->subjectRepository->create($data);
    }

    public function updateSubject(int $id, array $data): bool
    {
        return $this->subjectRepository->update($id, $data);
    }

    public function deleteSubject(int $id): bool
    {
        return $this->subjectRepository->delete($id);
    }

    public function getActiveSubjects(): Collection
    {
        return $this->subjectRepository->getActive();
    }

    public function searchSubjects(string $query): Collection
    {
        return $this->subjectRepository->search($query);
    }
}
