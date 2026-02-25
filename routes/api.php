<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QrAttendanceController;

// Admin API Controllers
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\UniversityController as AdminUniversityController;
use App\Http\Controllers\Api\Admin\CollegeController as AdminCollegeController;
use App\Http\Controllers\Api\Admin\MajorController as AdminMajorController;
use App\Http\Controllers\Api\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Api\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Api\Admin\DelegateController as AdminDelegateController;
use App\Http\Controllers\Api\Admin\ClinicalDelegateController as AdminClinicalDelegateController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\Admin\ActivityController as AdminActivityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ══════════════════════════════════════════════════════════════
// Admin API — Public (No Auth Required)
// ══════════════════════════════════════════════════════════════
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
});

// ══════════════════════════════════════════════════════════════
// Admin API — Protected (Sanctum Auth + Admin Role)
// ══════════════════════════════════════════════════════════════
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::get('me', [AdminAuthController::class, 'me']);

    // Dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'index']);

    // Academic — Universities
    Route::apiResource('universities', AdminUniversityController::class);

    // Academic — Colleges
    Route::apiResource('colleges', AdminCollegeController::class);

    // Academic — Majors
    Route::apiResource('majors', AdminMajorController::class);

    // Academic — Subjects
    Route::apiResource('subjects', AdminSubjectController::class);

    // User Management
    Route::get('users', [AdminUserController::class, 'index']);
    Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus']);
    Route::delete('users/{user}', [AdminUserController::class, 'destroy']);
    Route::post('users/bulk-activate', [AdminUserController::class, 'bulkActivate']);
    Route::post('users/bulk-deactivate', [AdminUserController::class, 'bulkDeactivate']);
    Route::post('users/bulk-delete', [AdminUserController::class, 'bulkDelete']);

    // Students
    Route::apiResource('students', AdminStudentController::class);

    // Doctors
    Route::apiResource('doctors', AdminDoctorController::class);

    // Delegates
    Route::apiResource('delegates', AdminDelegateController::class);

    // Clinical Delegates
    Route::apiResource('clinical-delegates', AdminClinicalDelegateController::class)
        ->only(['index', 'store', 'destroy']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('overview', [AdminReportController::class, 'overview']);
        Route::get('subject', [AdminReportController::class, 'subject']);
        Route::get('threshold', [AdminReportController::class, 'threshold']);
        Route::get('doctor-performance', [AdminReportController::class, 'doctorPerformance']);
        Route::get('system-overview', [AdminReportController::class, 'systemOverview']);
    });

    // Settings
    Route::get('settings', [AdminSettingController::class, 'index']);
    Route::put('settings', [AdminSettingController::class, 'update']);

    // Activity Log
    Route::get('activities', [AdminActivityController::class, 'index']);
    Route::delete('activities/cleanup', [AdminActivityController::class, 'cleanup']);
});

// ══════════════════════════════════════════════════════════════
// QR Attendance API
// ══════════════════════════════════════════════════════════════
Route::middleware(['web', 'auth'])->prefix('qr-attendance')->group(function () {

    // ── Delegate Endpoints ──
    Route::post('start', [QrAttendanceController::class, 'startSession']);
    Route::get('{session}/token', [QrAttendanceController::class, 'rotateToken']);
    Route::get('{session}/status', [QrAttendanceController::class, 'getStatus']);
    Route::post('{session}/finalize', [QrAttendanceController::class, 'finalize']);

    // ── Student Endpoint ──
    Route::post('scan', [QrAttendanceController::class, 'scan']);
});
