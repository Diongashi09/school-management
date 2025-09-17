<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function all(): Collection
    {
        return Attendance::with(['student.user', 'class', 'subject', 'teacher.user', 'academicYear'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Attendance::with(['student.user', 'class', 'subject', 'teacher.user', 'academicYear'])
            ->orderBy('attendance_date', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Attendance
    {
        return Attendance::with(['student.user', 'class', 'subject', 'teacher.user', 'academicYear'])
            ->find($id);
    }

    public function create(array $data): Attendance
    {
        return Attendance::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Attendance::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return Attendance::destroy($id);
    }

    public function getByDate(string $date): Collection
    {
        return Attendance::with(['student.user', 'class', 'subject', 'teacher.user'])
            ->byDate($date)
            ->orderBy('class_id')
            ->orderBy('student_id')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return Attendance::with(['student.user', 'class', 'subject', 'teacher.user'])
            ->byDateRange($startDate, $endDate)
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    public function getByStudent(int $studentId): Collection
    {
        return Attendance::with(['class', 'subject', 'teacher.user', 'academicYear'])
            ->byStudent($studentId)
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    public function getByClass(int $classId): Collection
    {
        return Attendance::with(['student.user', 'subject', 'teacher.user'])
            ->byClass($classId)
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return Attendance::with(['student.user', 'class', 'subject', 'teacher.user'])
            ->byStatus($status)
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    public function getByAcademicYear(int $academicYearId): Collection
    {
        return Attendance::with(['student.user', 'class', 'subject', 'teacher.user'])
            ->byAcademicYear($academicYearId)
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    public function bulkCreate(array $attendanceData): bool
    {
        try {
            DB::beginTransaction();
            
            foreach ($attendanceData as $data) {
                Attendance::create($data);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function bulkUpdate(array $attendanceData): bool
    {
        try {
            DB::beginTransaction();
            
            foreach ($attendanceData as $data) {
                if (isset($data['id'])) {
                    $id = $data['id'];
                    unset($data['id']);
                    Attendance::where('id', $id)->update($data);
                }
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function getAttendanceStats(int $studentId, ?int $academicYearId = null): array
    {
        $query = Attendance::where('student_id', $studentId);
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_days,
            SUM(CASE WHEN status IN ("present", "late", "partial") THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN status = "excused" THEN 1 ELSE 0 END) as excused_days
        ')->first();

        $presentDays = $stats->present_days ?? 0;
        $totalDays = $stats->total_days ?? 0;
        
        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $stats->absent_days ?? 0,
            'late_days' => $stats->late_days ?? 0,
            'excused_days' => $stats->excused_days ?? 0,
            'attendance_percentage' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0,
        ];
    }

    public function getClassAttendanceStats(int $classId, string $date): array
    {
        $stats = Attendance::where('class_id', $classId)
            ->byDate($date)
            ->selectRaw('
                COUNT(*) as total_students,
                SUM(CASE WHEN status IN ("present", "late", "partial") THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_count
            ')->first();

        $totalStudents = $stats->total_students ?? 0;
        $presentCount = $stats->present_count ?? 0;

        return [
            'date' => $date,
            'total_students' => $totalStudents,
            'present_count' => $presentCount,
            'absent_count' => $stats->absent_count ?? 0,
            'late_count' => $stats->late_count ?? 0,
            'attendance_percentage' => $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 2) : 0,
        ];
    }

    public function getDailyAttendanceReport(string $date): array
    {
        return Attendance::with(['student.user', 'class'])
            ->byDate($date)
            ->get()
            ->groupBy('class_id')
            ->map(function ($classAttendances) {
                $class = $classAttendances->first()->class;
                $totalStudents = $classAttendances->count();
                $presentCount = $classAttendances->where('is_present', true)->count();
                
                return [
                    'class' => $class,
                    'total_students' => $totalStudents,
                    'present_count' => $presentCount,
                    'absent_count' => $totalStudents - $presentCount,
                    'attendance_percentage' => $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 2) : 0,
                    'students' => $classAttendances->values(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function getMonthlyAttendanceReport(int $month, int $year): array
    {
        return Attendance::whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->with(['student.user', 'class'])
            ->get()
            ->groupBy(['class_id', function($item) {
                return $item->attendance_date->format('Y-m-d');
            }])
            ->map(function ($classDays) {
                return $classDays->map(function ($dayAttendances) {
                    $totalStudents = $dayAttendances->count();
                    $presentCount = $dayAttendances->where('is_present', true)->count();
                    
                    return [
                        'date' => $dayAttendances->first()->attendance_date->format('Y-m-d'),
                        'class' => $dayAttendances->first()->class,
                        'total_students' => $totalStudents,
                        'present_count' => $presentCount,
                        'absent_count' => $totalStudents - $presentCount,
                        'attendance_percentage' => $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 2) : 0,
                    ];
                });
            })
            ->toArray();
    }

    public function searchByStudentName(string $name): Collection
    {
        return Attendance::with(['student.user', 'class', 'subject', 'teacher.user'])
            ->whereHas('student.user', function ($query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    public function getAttendanceByFilters(array $filters): Collection
    {
        $query = Attendance::with(['student.user', 'class', 'subject', 'teacher.user', 'academicYear']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date'])) {
            $query->byDate($filters['date']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        return $query->orderBy('attendance_date', 'desc')->get();
    }
}