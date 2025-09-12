<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\User;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    // Student Management endpoints

    /**
     * Display a listing of students.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::with(['user', 'currentEnrollment.class', 'currentEnrollment.academicYear']);

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('class_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('class_id', $request->class_id)->where('status', 'active');
            });
        }

        if ($request->has('academic_year_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id)->where('status', 'active');
            });
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($students);
    }

    /**
     * Store a newly created student.
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Create user account first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => 3, // Student role
            ]);

            // Create student profile
            $student = Student::create(array_merge($request->validated(), [
                'user_id' => $user->id,
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Student created successfully',
                'data' => $student->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): JsonResponse
    {
        $student->load(['user', 'enrollments.class', 'enrollments.academicYear', 'parents']);
        
        return response()->json($student);
    }

    /**
     * Update the specified student.
     */
    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Update student profile
            $student->update($request->validated());

            // Update user account if name or email provided
            if ($request->has('name') || $request->has('email')) {
                $student->user->update($request->only(['name', 'email']));
            }

            DB::commit();

            return response()->json([
                'message' => 'Student updated successfully',
                'data' => $student->load('user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Student $student): JsonResponse
    {
        if ($student->enrollments()->where('status', 'active')->exists()) {
            return response()->json([
                'message' => 'Cannot delete student with active enrollments'
            ], 422);
        }

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully'
        ]);
    }

    /**
     * Get students by class.
     */
    public function getByClass(Request $request, $classId): JsonResponse
    {
        $students = Student::with(['user', 'currentEnrollment'])
            ->whereHas('enrollments', function ($q) use ($classId) {
                $q->where('class_id', $classId)->where('status', 'active');
            })
            ->paginate(15);

        return response()->json($students);
    }

    /**
     * Get students by academic year.
     */
    public function getByAcademicYear(Request $request, $academicYearId): JsonResponse
    {
        $students = Student::with(['user', 'currentEnrollment.class'])
            ->whereHas('enrollments', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId)->where('status', 'active');
            })
            ->paginate(15);

        return response()->json($students);
    }

    // Enrollment Management endpoints

    /**
     * Display a listing of enrollments.
     */
    public function indexEnrollments(Request $request): JsonResponse
    {
        $query = Enrollment::with(['student.user', 'class', 'academicYear']);

        if ($request->has('class_id')) {
            $query->byClass($request->class_id);
        }

        if ($request->has('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $enrollments = $query->orderBy('enrollment_date', 'desc')->paginate(15);

        return response()->json($enrollments);
    }

    /**
     * Store a newly created enrollment.
     */
    public function storeEnrollment(StoreEnrollmentRequest $request): JsonResponse
    {
        // Check if student is already enrolled in another class for the same academic year
        $existingEnrollment = Enrollment::where('student_id', $request->student_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('status', 'active')
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'message' => 'Student is already enrolled in another class for this academic year'
            ], 422);
        }

        $enrollment = Enrollment::create($request->validated());

        return response()->json([
            'message' => 'Enrollment created successfully',
            'data' => $enrollment->load(['student.user', 'class', 'academicYear'])
        ], 201);
    }

    /**
     * Display the specified enrollment.
     */
    public function showEnrollment(Enrollment $enrollment): JsonResponse
    {
        $enrollment->load(['student.user', 'class', 'academicYear']);
        
        return response()->json($enrollment);
    }

    /**
     * Update the specified enrollment.
     */
    public function updateEnrollment(UpdateEnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        $enrollment->update($request->validated());

        return response()->json([
            'message' => 'Enrollment updated successfully',
            'data' => $enrollment->load(['student.user', 'class', 'academicYear'])
        ]);
    }

    /**
     * Remove the specified enrollment.
     */
    public function destroyEnrollment(Enrollment $enrollment): JsonResponse
    {
        $enrollment->delete();

        return response()->json([
            'message' => 'Enrollment deleted successfully'
        ]);
    }

    /**
     * Transfer student to another class.
     */
    public function transferStudent(Request $request, Student $student): JsonResponse
    {
        $request->validate([
            'new_class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        DB::beginTransaction();

        try {
            // Deactivate current enrollment
            $currentEnrollment = $student->enrollments()
                ->where('academic_year_id', $request->academic_year_id)
                ->where('status', 'active')
                ->first();

            if ($currentEnrollment) {
                $currentEnrollment->update(['status' => 'withdrawn']);
            }

            // Create new enrollment
            Enrollment::create([
                'student_id' => $student->id,
                'class_id' => $request->new_class_id,
                'academic_year_id' => $request->academic_year_id,
                'enrollment_date' => now(),
                'status' => 'active',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Student transferred successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to transfer student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::active()->count(),
            'graduated_students' => Student::byStatus('graduated')->count(),
            'transferred_students' => Student::byStatus('transferred')->count(),
            'enrolled_this_year' => Student::whereHas('enrollments', function ($q) {
                $q->where('academic_year_id', 2) // Current academic year
                  ->where('status', 'active');
            })->count(),
        ];

        return response()->json($stats);
    }
}
