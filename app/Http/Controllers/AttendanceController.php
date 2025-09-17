<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Requests\BulkAttendanceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Handle filters
            if ($request->hasAny(['student_id', 'class_id', 'status', 'date', 'start_date', 'end_date', 'academic_year_id', 'subject_id'])) {
                $filters = $request->only(['student_id', 'class_id', 'status', 'date', 'start_date', 'end_date', 'academic_year_id', 'subject_id']);
                $attendance = $this->attendanceService->getAttendanceByFilters($filters);
                return response()->json([
                    'success' => true,
                    'data' => $attendance,
                    'message' => 'Attendance records retrieved successfully'
                ]);
            }

            // Handle search by student name
            if ($request->has('search')) {
                $attendance = $this->attendanceService->searchAttendanceByStudentName($request->search);
                return response()->json([
                    'success' => true,
                    'data' => $attendance,
                    'message' => 'Attendance records retrieved successfully'
                ]);
            }

            // Default pagination
            $attendance = $this->attendanceService->getPaginatedAttendance($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Attendance records retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created attendance record.
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->createAttendance($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Attendance record created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified attendance record.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->findAttendance($id);
            
            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Attendance record retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified attendance record.
     */
    public function update(UpdateAttendanceRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->attendanceService->updateAttendance($id, $request->validated());
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found or failed to update'
                ], 404);
            }
            
            $attendance = $this->attendanceService->findAttendance($id);
            
            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Attendance record updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->attendanceService->deleteAttendance($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance by date.
     */
    public function getByDate(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        try {
            $attendance = $this->attendanceService->getAttendanceByDate($request->date);
            
            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Attendance records retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance by student.
     */
    public function getByStudent(int $studentId): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->getAttendanceByStudent($studentId);
            
            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Student attendance records retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student attendance records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance by class.
     */
    public function getByClass(int $classId): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->getAttendanceByClass($classId);
            
            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Class attendance records retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class attendance records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create attendance records.
     */
    public function bulkStore(BulkAttendanceRequest $request): JsonResponse
    {
        try {
            $success = $this->attendanceService->bulkCreateAttendance($request->validated()['attendance_records']);
            
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create attendance records'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance records created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark attendance for entire class.
     */
    public function markClassAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.teacher_id' => 'required|exists:teachers,id',
            'students.*.academic_year_id' => 'required|exists:academic_years,id',
            'students.*.status' => 'required|in:present,absent,late,excused,partial',
            'students.*.remarks' => 'nullable|string',
            'students.*.subject_id' => 'nullable|exists:subjects,id',
            'students.*.period_number' => 'nullable|integer',
        ]);

        try {
            $success = $this->attendanceService->markClassAttendance(
                $request->class_id,
                $request->date,
                $request->students
            );
            
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark class attendance'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Class attendance marked successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark class attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics for a student.
     */
    public function getStudentStats(Request $request, int $studentId): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id'
        ]);

        try {
            $stats = $this->attendanceService->getStudentAttendanceStats(
                $studentId,
                $request->academic_year_id
            );
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Student attendance statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student attendance statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics for a class.
     */
    public function getClassStats(Request $request, int $classId): JsonResponse
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        try {
            $stats = $this->attendanceService->getClassAttendanceStats($classId, $request->date);
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Class attendance statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class attendance statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily attendance report.
     */
    public function getDailyReport(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        try {
            $report = $this->attendanceService->getDailyAttendanceReport($request->date);
            
            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Daily attendance report retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve daily attendance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly attendance report.
     */
    public function getMonthlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100'
        ]);

        try {
            $report = $this->attendanceService->getMonthlyAttendanceReport(
                $request->month,
                $request->year
            );
            
            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Monthly attendance report retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve monthly attendance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's attendance for a class.
     */
    public function getTodayAttendance(int $classId): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->getTodayAttendance($classId);
            
            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => "Today's attendance retrieved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Failed to retrieve today's attendance",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}