<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AcademicController;

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

// Health check route
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});
