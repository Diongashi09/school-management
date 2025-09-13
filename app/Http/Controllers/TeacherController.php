<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\ClassTeacher;
use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Requests\StoreClassTeacherRequest;
use App\Http\Requests\UpdateClassTeacherRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    // Teacher Management endpoints

    /**
     * Display a listing of teachers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Teacher::with(['user', 'classes', 'subjects']);

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('subject_id')) {
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('subjects.id', $request->subject_id);
            });
        }

        if ($request->has('class_id')) {
            $query->whereHas('classes', function ($q) use ($request) {
                $q->where('classes.id', $request->class_id);
            });
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $teachers = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($teachers);
    }

    /**
     * Store a newly created teacher.
     */
    public function store(StoreTeacherRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Create user account first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => 2, // Teacher role
            ]);

            // Create teacher profile
            $teacher = Teacher::create(array_merge($request->validated(), [
                'user_id' => $user->id,
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Teacher created successfully',
                'data' => $teacher->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(Teacher $teacher): JsonResponse
    {
        $teacher->load(['user', 'classes.academicYear', 'subjects', 'currentAssignments.class', 'currentAssignments.subject']);
        
        return response()->json($teacher);
    }

    /**
     * Update the specified teacher.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Update teacher profile
            $teacher->update($request->validated());

            // Update user account if name or email provided
            if ($request->has('name') || $request->has('email')) {
                $teacher->user->update($request->only(['name', 'email']));
            }

            DB::commit();

            return response()->json([
                'message' => 'Teacher updated successfully',
                'data' => $teacher->load('user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified teacher.
     */
    public function destroy(Teacher $teacher): JsonResponse
    {
        if ($teacher->currentAssignments()->exists()) {
            return response()->json([
                'message' => 'Cannot delete teacher with active class assignments'
            ], 422);
        }

        $teacher->delete();

        return response()->json([
            'message' => 'Teacher deleted successfully'
        ]);
    }

    /**
     * Get teachers by subject.
     */
    public function getBySubject(Request $request, $subjectId): JsonResponse
    {
        $teachers = Teacher::with(['user', 'classes'])
            ->whereHas('subjects', function ($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            })
            ->paginate(15);

        return response()->json($teachers);
    }

    /**
     * Get teachers by class.
     */
    public function getByClass(Request $request, $classId): JsonResponse
    {
        $teachers = Teacher::with(['user', 'subjects'])
            ->whereHas('classes', function ($q) use ($classId) {
                $q->where('classes.id', $classId);
            })
            ->paginate(15);

        return response()->json($teachers);
    }

    /**
     * Get teacher statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_teachers' => Teacher::count(),
            'active_teachers' => Teacher::active()->count(),
            'inactive_teachers' => Teacher::byStatus('inactive')->count(),
            'terminated_teachers' => Teacher::byStatus('terminated')->count(),
            'teachers_with_assignments' => Teacher::whereHas('currentAssignments')->count(),
            'average_experience' => Teacher::active()->avg(DB::raw('DATEDIFF(NOW(), hire_date) / 365')),
        ];

        return response()->json($stats);
    }

    // Class Teacher Assignment endpoints

    /**
     * Display a listing of class teacher assignments.
     */
    public function indexAssignments(Request $request): JsonResponse
    {
        $query = ClassTeacher::with(['teacher.user', 'class', 'subject', 'academicYear']);

        if ($request->has('teacher_id')) {
            $query->byTeacher($request->teacher_id);
        }

        if ($request->has('class_id')) {
            $query->byClass($request->class_id);
        }

        if ($request->has('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->has('is_primary')) {
            $query->where('is_primary', $request->boolean('is_primary'));
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($assignments);
    }

    /**
     * Store a newly created class teacher assignment.
     */
    public function storeAssignment(StoreClassTeacherRequest $request): JsonResponse
    {
        // Check if teacher is already assigned to the same class and subject for the same academic year
        $existingAssignment = ClassTeacher::where('teacher_id', $request->teacher_id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'Teacher is already assigned to this class and subject for this academic year'
            ], 422);
        }

        $assignment = ClassTeacher::create($request->validated());

        return response()->json([
            'message' => 'Class teacher assignment created successfully',
            'data' => $assignment->load(['teacher.user', 'class', 'subject', 'academicYear'])
        ], 201);
    }

    /**
     * Display the specified class teacher assignment.
     */
    public function showAssignment(ClassTeacher $classTeacher): JsonResponse
    {
        $classTeacher->load(['teacher.user', 'class', 'subject', 'academicYear']);
        
        return response()->json($classTeacher);
    }

    /**
     * Update the specified class teacher assignment.
     */
    public function updateAssignment(UpdateClassTeacherRequest $request, ClassTeacher $classTeacher): JsonResponse
    {
        $classTeacher->update($request->validated());

        return response()->json([
            'message' => 'Class teacher assignment updated successfully',
            'data' => $classTeacher->load(['teacher.user', 'class', 'subject', 'academicYear'])
        ]);
    }

    /**
     * Remove the specified class teacher assignment.
     */
    public function destroyAssignment(ClassTeacher $classTeacher): JsonResponse
    {
        $classTeacher->delete();

        return response()->json([
            'message' => 'Class teacher assignment deleted successfully'
        ]);
    }

    /**
     * Assign teacher to class.
     */
    public function assignToClass(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_primary' => 'sometimes|boolean',
        ]);

        // Check if teacher is already assigned to the same class and subject
        $existingAssignment = ClassTeacher::where('teacher_id', $request->teacher_id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'Teacher is already assigned to this class and subject for this academic year'
            ], 422);
        }

        $assignment = ClassTeacher::create([
            'teacher_id' => $request->teacher_id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'academic_year_id' => $request->academic_year_id,
            'is_primary' => $request->boolean('is_primary', false),
        ]);

        return response()->json([
            'message' => 'Teacher assigned to class successfully',
            'data' => $assignment->load(['teacher.user', 'class', 'subject', 'academicYear'])
        ], 201);
    }

    /**
     * Get available teachers for assignment.
     */
    public function getAvailableTeachers(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $assignedTeacherIds = ClassTeacher::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->pluck('teacher_id');

        $availableTeachers = Teacher::with('user')
            ->whereNotIn('id', $assignedTeacherIds)
            ->active()
            ->get();

        return response()->json($availableTeachers);
    }
}
