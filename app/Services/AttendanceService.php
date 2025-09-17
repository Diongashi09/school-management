<?php

namespace App\Services;

use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class AttendanceService
{
    protected $attendanceRepository;

    public function __construct(AttendanceRepositoryInterface $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Get paginated attendance records.
     */
    public function getPaginatedAttendance(int $perPage = 15): LengthAwarePaginator
    {
        return $this->attendanceRepository->paginate($perPage);
    }

    /**
     * Get all attendance records.
     */
    public function getAllAttendance(): Collection
    {
        return $this->attendanceRepository->all();
    }

    /**
     * Find attendance by ID.
     */
    public function findAttendance(int $id): ?Attendance
    {
        return $this->attendanceRepository->find($id);
    }

    /**
     * Create new attendance record.
     */
    public function createAttendance(array $data): Attendance
    {
        // Set default values
        $data['attendance_date'] = $data['attendance_date'] ?? now()->format('Y-m-d');
        $data['status'] = $data['status'] ?? 'present';
        $data['period_type'] = $data['period_type'] ?? 'full_day';

        // Set check-in time if present
        if ($data['status'] === 'present' && !isset($data['check_in_time'])) {
            $data['check_in_time'] = now()->format('H:i:s');
        }

        return $this->attendanceRepository->create($data);
    }

    /**
     * Update attendance record.
     */
    public function updateAttendance(int $id, array $data): bool
    {
        // Update check-out time if status changed to present
        if (isset($data['status']) && $data['status'] === 'present' && !isset($data['check_out_time'])) {
            $data['check_out_time'] = now()->format('H:i:s');
        }

        return $this->attendanceRepository->update($id, $data);
    }

    /**
     * Delete attendance record.
     */
    public function deleteAttendance(int $id): bool
    {
        return $this->attendanceRepository->delete($id);
    }

    /**
     * Get attendance by date.
     */
    public function getAttendanceByDate(string $date): Collection
    {
        return $this->attendanceRepository->getByDate($date);
    }

    /**
     * Get attendance by date range.
     */
    public function getAttendanceByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->attendanceRepository->getByDateRange($startDate, $endDate);
    }

    /**
     * Get attendance by student.
     */
    public function getAttendanceByStudent(int $studentId): Collection
    {
        return $this->attendanceRepository->getByStudent($studentId);
    }

    /**
     * Get attendance by class.
     */
    public function getAttendanceByClass(int $classId): Collection
    {
        return $this->attendanceRepository->getByClass($classId);
    }

    /**
     * Get attendance by status.
     */
    public function getAttendanceByStatus(string $status): Collection
    {
        return $this->attendanceRepository->getByStatus($status);
    }

    /**
     * Get attendance by academic year.
     */
    public function getAttendanceByAcademicYear(int $academicYearId): Collection
    {
        return $this->attendanceRepository->getByAcademicYear($academicYearId);
    }

    /**
     * Bulk create attendance records.
     */
    public function bulkCreateAttendance(array $attendanceData): bool
    {
        // Process each record to set defaults
        $processedData = array_map(function ($data) {
            $data['attendance_date'] = $data['attendance_date'] ?? now()->format('Y-m-d');
            $data['status'] = $data['status'] ?? 'present';
            $data['period_type'] = $data['period_type'] ?? 'full_day';
            
            if ($data['status'] === 'present' && !isset($data['check_in_time'])) {
                $data['check_in_time'] = now()->format('H:i:s');
            }
            
            return $data;
        }, $attendanceData);

        return $this->attendanceRepository->bulkCreate($processedData);
    }

    /**
     * Bulk update attendance records.
     */
    public function bulkUpdateAttendance(array $attendanceData): bool
    {
        return $this->attendanceRepository->bulkUpdate($attendanceData);
    }

    /**
     * Mark attendance for entire class.
     */
    public function markClassAttendance(int $classId, string $date, array $studentsData): bool
    {
        $attendanceData = [];
        
        foreach ($studentsData as $studentData) {
            $attendanceData[] = [
                'student_id' => $studentData['student_id'],
                'class_id' => $classId,
                'teacher_id' => $studentData['teacher_id'],
                'academic_year_id' => $studentData['academic_year_id'],
                'attendance_date' => $date,
                'status' => $studentData['status'] ?? 'present',
                'remarks' => $studentData['remarks'] ?? null,
                'check_in_time' => $studentData['check_in_time'] ?? now()->format('H:i:s'),
                'period_type' => $studentData['period_type'] ?? 'full_day',
                'subject_id' => $studentData['subject_id'] ?? null,
                'period_number' => $studentData['period_number'] ?? null,
            ];
        }

        return $this->bulkCreateAttendance($attendanceData);
    }

    /**
     * Get attendance statistics for a student.
     */
    public function getStudentAttendanceStats(int $studentId, ?int $academicYearId = null): array
    {
        return $this->attendanceRepository->getAttendanceStats($studentId, $academicYearId);
    }

    /**
     * Get attendance statistics for a class on a specific date.
     */
    public function getClassAttendanceStats(int $classId, string $date): array
    {
        return $this->attendanceRepository->getClassAttendanceStats($classId, $date);
    }

    /**
     * Get daily attendance report.
     */
    public function getDailyAttendanceReport(string $date): array
    {
        return $this->attendanceRepository->getDailyAttendanceReport($date);
    }

    /**
     * Get monthly attendance report.
     */
    public function getMonthlyAttendanceReport(int $month, int $year): array
    {
        return $this->attendanceRepository->getMonthlyAttendanceReport($month, $year);
    }

    /**
     * Search attendance by student name.
     */
    public function searchAttendanceByStudentName(string $name): Collection
    {
        return $this->attendanceRepository->searchByStudentName($name);
    }

    /**
     * Get attendance with filters.
     */
    public function getAttendanceByFilters(array $filters): Collection
    {
        return $this->attendanceRepository->getAttendanceByFilters($filters);
    }

    /**
     * Get today's attendance for a class.
     */
    public function getTodayAttendance(int $classId): Collection
    {
        return $this->attendanceRepository->getByDate(now()->format('Y-m-d'))
            ->where('class_id', $classId);
    }

    /**
     * Check if attendance exists for student on date.
     */
    public function attendanceExists(int $studentId, int $classId, string $date, ?int $subjectId = null, ?int $periodNumber = null): bool
    {
        $filters = [
            'student_id' => $studentId,
            'class_id' => $classId,
            'date' => $date,
        ];

        if ($subjectId) {
            $filters['subject_id'] = $subjectId;
        }

        if ($periodNumber) {
            $filters['period_number'] = $periodNumber;
        }

        return $this->getAttendanceByFilters($filters)->isNotEmpty();
    }

    /**
     * Calculate attendance percentage for a student in date range.
     */
    public function calculateAttendancePercentage(int $studentId, string $startDate, string $endDate): float
    {
        $attendanceRecords = $this->attendanceRepository->getByStudent($studentId)
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        $totalDays = $attendanceRecords->count();
        $presentDays = $attendanceRecords->where('is_present', true)->count();

        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
    }
}