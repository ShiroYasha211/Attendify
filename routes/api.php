<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QrAttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| QR Code Attendance API endpoints.
| These routes are used by the mobile app for QR-based attendance.
|
| Authentication: Currently uses session-based auth (web guard).
| When Sanctum is installed, switch middleware to 'auth:sanctum'.
|
*/

Route::middleware(['web', 'auth'])->prefix('qr-attendance')->group(function () {

    // ── Delegate Endpoints ──
    Route::post('start', [QrAttendanceController::class, 'startSession']);
    Route::get('{session}/token', [QrAttendanceController::class, 'rotateToken']);
    Route::get('{session}/status', [QrAttendanceController::class, 'getStatus']);
    Route::post('{session}/finalize', [QrAttendanceController::class, 'finalize']);

    // ── Student Endpoint ──
    Route::post('scan', [QrAttendanceController::class, 'scan']);
});
