<?php

namespace App\Repositories\Interfaces;

use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Attendance;
    public function create(array $data): Attendance;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    
    // Attendance specific methods
    public function getByDate(string $date): Collection;
    public function getByDateRange(string $startDate, string $endDate): Collection;
    public function getByStudent(int $studentId): Collection;
    public function getByClass(int $classId): Collection;
    public function getByStatus(string $status): Collection;
    public function getByAcademicYear(int $academicYearId): Collection;
    
    // Bulk operations
    public function bulkCreate(array $attendanceData): bool;
    public function bulkUpdate(array $attendanceData): bool;
    
    // Statistics
    public function getAttendanceStats(int $studentId, ?int $academicYearId = null): array;
    public function getClassAttendanceStats(int $classId, string $date): array;
    public function getDailyAttendanceReport(string $date): array;
    public function getMonthlyAttendanceReport(int $month, int $year): array;
    
    // Search and filter
    public function searchByStudentName(string $name): Collection;
    public function getAttendanceByFilters(array $filters): Collection;
}