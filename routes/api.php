<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ParentController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

// Academic Management routes
Route::prefix('academic')->middleware('auth:sanctum')->group(function () {
    
    // Academic Years
    Route::prefix('years')->group(function () {
        Route::get('/', [AcademicController::class, 'indexAcademicYears']);
        Route::post('/', [AcademicController::class, 'storeAcademicYear'])->middleware('permission:Manage Academic Years');
        Route::get('/current', [AcademicController::class, 'getCurrentAcademicYear']);
        Route::get('/{academicYear}', [AcademicController::class, 'showAcademicYear']);
        Route::put('/{academicYear}', [AcademicController::class, 'updateAcademicYear'])->middleware('permission:Manage Academic Years');
        Route::delete('/{academicYear}', [AcademicController::class, 'destroyAcademicYear'])->middleware('permission:Manage Academic Years');
    });

    // Subjects
    Route::prefix('subjects')->group(function () {
        Route::get('/', [AcademicController::class, 'indexSubjects']);
        Route::post('/', [AcademicController::class, 'storeSubject'])->middleware('permission:Manage Subjects');
        Route::get('/{subject}', [AcademicController::class, 'showSubject']);
        Route::put('/{subject}', [AcademicController::class, 'updateSubject'])->middleware('permission:Manage Subjects');
        Route::delete('/{subject}', [AcademicController::class, 'destroySubject'])->middleware('permission:Manage Subjects');
    });

    // Classes
    Route::prefix('classes')->group(function () {
        Route::get('/', [AcademicController::class, 'indexClasses']);
        Route::post('/', [AcademicController::class, 'storeClass'])->middleware('permission:Manage Classes');
        Route::get('/{class}', [AcademicController::class, 'showClass']);
        Route::put('/{class}', [AcademicController::class, 'updateClass'])->middleware('permission:Manage Classes');
        Route::delete('/{class}', [AcademicController::class, 'destroyClass'])->middleware('permission:Manage Classes');
        Route::get('/year/{academicYear}', [AcademicController::class, 'getClassesByAcademicYear']);
    });
});

// Student Management routes
Route::prefix('students')->middleware('auth:sanctum')->group(function () {
    
    // Students
    Route::get('/', [StudentController::class, 'index']);
    Route::post('/', [StudentController::class, 'store'])->middleware('permission:Manage Students');
    Route::get('/statistics', [StudentController::class, 'getStatistics']);
    Route::get('/class/{classId}', [StudentController::class, 'getByClass']);
    Route::get('/academic-year/{academicYearId}', [StudentController::class, 'getByAcademicYear']);
    Route::get('/{student}', [StudentController::class, 'show']);
    Route::put('/{student}', [StudentController::class, 'update'])->middleware('permission:Edit Students');
    Route::delete('/{student}', [StudentController::class, 'destroy'])->middleware('permission:Delete Students');
    Route::post('/{student}/transfer', [StudentController::class, 'transferStudent'])->middleware('permission:Manage Students');

    // Enrollments
    Route::prefix('enrollments')->group(function () {
        Route::get('/', [StudentController::class, 'indexEnrollments']);
        Route::post('/', [StudentController::class, 'storeEnrollment'])->middleware('permission:Manage Students');
        Route::get('/{enrollment}', [StudentController::class, 'showEnrollment']);
        Route::put('/{enrollment}', [StudentController::class, 'updateEnrollment'])->middleware('permission:Edit Students');
        Route::delete('/{enrollment}', [StudentController::class, 'destroyEnrollment'])->middleware('permission:Delete Students');
    });
});

// Teacher Management routes
Route::prefix('teachers')->middleware('auth:sanctum')->group(function () {
    
    // Teachers
    Route::get('/', [TeacherController::class, 'index']);
    Route::post('/', [TeacherController::class, 'store'])->middleware('permission:Manage Teachers');
    Route::get('/statistics', [TeacherController::class, 'getStatistics']);
    Route::get('/subject/{subjectId}', [TeacherController::class, 'getBySubject']);
    Route::get('/class/{classId}', [TeacherController::class, 'getByClass']);
    Route::get('/{teacher}', [TeacherController::class, 'show']);
    Route::put('/{teacher}', [TeacherController::class, 'update'])->middleware('permission:Edit Teachers');
    Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->middleware('permission:Delete Teachers');

    // Class Teacher Assignments
    Route::prefix('assignments')->group(function () {
        Route::get('/', [TeacherController::class, 'indexAssignments']);
        Route::post('/', [TeacherController::class, 'storeAssignment'])->middleware('permission:Manage Teachers');
        Route::post('/assign', [TeacherController::class, 'assignToClass'])->middleware('permission:Manage Teachers');
        Route::get('/available', [TeacherController::class, 'getAvailableTeachers']);
        Route::get('/{classTeacher}', [TeacherController::class, 'showAssignment']);
        Route::put('/{classTeacher}', [TeacherController::class, 'updateAssignment'])->middleware('permission:Edit Teachers');
        Route::delete('/{classTeacher}', [TeacherController::class, 'destroyAssignment'])->middleware('permission:Delete Teachers');
    });
});

// Grade Management routes
Route::prefix('grades')->middleware('auth:sanctum')->group(function () {
    
    // Exams
    Route::prefix('exams')->group(function () {
        Route::get('/', [GradeController::class, 'indexExams']);
        Route::post('/', [GradeController::class, 'storeExam'])->middleware('permission:Manage Grades');
        Route::get('/{exam}', [GradeController::class, 'showExam']);
        Route::put('/{exam}', [GradeController::class, 'updateExam'])->middleware('permission:Edit Grades');
        Route::delete('/{exam}', [GradeController::class, 'destroyExam'])->middleware('permission:Delete Grades');
        Route::post('/{exam}/toggle-publish', [GradeController::class, 'togglePublish'])->middleware('permission:Edit Grades');
        Route::get('/{exam}/statistics', [GradeController::class, 'getExamStatistics']);
    });

    // Grades
    Route::get('/', [GradeController::class, 'indexGrades']);
    Route::post('/', [GradeController::class, 'storeGrade'])->middleware('permission:Create Grades');
    Route::get('/statistics', [GradeController::class, 'getGradeStatistics']);
    Route::get('/{grade}', [GradeController::class, 'showGrade']);
    Route::put('/{grade}', [GradeController::class, 'updateGrade'])->middleware('permission:Edit Grades');
    Route::delete('/{grade}', [GradeController::class, 'destroyGrade'])->middleware('permission:Delete Grades');

    // Bulk operations
    Route::post('/exams/{exam}/bulk', [GradeController::class, 'bulkCreateGrades'])->middleware('permission:Create Grades');

    // Reports
    Route::get('/students/{student}/report', [GradeController::class, 'getStudentGradeReport']);
    Route::get('/classes/{class}/report', [GradeController::class, 'getClassGradeReport']);
});

// Parent Management Routes
Route::middleware(['auth:sanctum', 'permission:manage_parents'])->group(function () {
    // Parent CRUD
    Route::get('/parents', [ParentController::class, 'index']);
    Route::post('/parents', [ParentController::class, 'store']);
    Route::get('/parents/{parent}', [ParentController::class, 'show']);
    Route::put('/parents/{parent}', [ParentController::class, 'update']);
    Route::delete('/parents/{parent}', [ParentController::class, 'destroy']);
    
    // Parent specific endpoints
    Route::get('/parents/student/{studentId}', [ParentController::class, 'getByStudent']);
    Route::get('/parents/primary-contacts', [ParentController::class, 'getPrimaryContacts']);
    Route::get('/parents/emergency-contacts', [ParentController::class, 'getEmergencyContacts']);
    Route::get('/parents/statistics', [ParentController::class, 'getStatistics']);
    
    // Student-Parent Relationships
    Route::get('/student-parents', [ParentController::class, 'indexRelationships']);
    Route::post('/student-parents', [ParentController::class, 'storeRelationship']);
    Route::get('/student-parents/{id}', [ParentController::class, 'showRelationship']);
    Route::put('/student-parents/{id}', [ParentController::class, 'updateRelationship']);
    Route::delete('/student-parents/{id}', [ParentController::class, 'destroyRelationship']);
    
    // Assignment operations
    Route::post('/parents/assign-to-student', [ParentController::class, 'assignToStudent']);
    Route::post('/parents/remove-from-student', [ParentController::class, 'removeFromStudent']);
});

// Health check route
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});
Route::prefix('attendance')->middleware('auth:sanctum')->group(function () {
    
    // Basic CRUD operations
    Route::get('/', [AttendanceController::class, 'index']);
    Route::post('/', [AttendanceController::class, 'store'])->middleware('permission:Create Attendance');
    Route::get('/{id}', [AttendanceController::class, 'show']);
    Route::put('/{id}', [AttendanceController::class, 'update'])->middleware('permission:Edit Attendance');
    Route::delete('/{id}', [AttendanceController::class, 'destroy'])->middleware('permission:Delete Attendance');
    
    // Bulk operations
    Route::post('/bulk', [AttendanceController::class, 'bulkStore'])->middleware('permission:Create Attendance');
    Route::post('/mark-class', [AttendanceController::class, 'markClassAttendance'])->middleware('permission:Create Attendance');
    
    // Query operations
    Route::get('/date/{date}', [AttendanceController::class, 'getByDate']);
    Route::get('/student/{studentId}', [AttendanceController::class, 'getByStudent']);
    Route::get('/class/{classId}', [AttendanceController::class, 'getByClass']);
    Route::get('/class/{classId}/today', [AttendanceController::class, 'getTodayAttendance']);
    
    // Statistics and reports
    Route::get('/student/{studentId}/stats', [AttendanceController::class, 'getStudentStats']);
    Route::get('/class/{classId}/stats', [AttendanceController::class, 'getClassStats']);
    Route::get('/reports/daily', [AttendanceController::class, 'getDailyReport']);
    Route::get('/reports/monthly', [AttendanceController::class, 'getMonthlyReport']);
});

