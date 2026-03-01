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

// Delegate API Controllers
use App\Http\Controllers\Api\Delegate\AuthController as DelegateAuthController;
use App\Http\Controllers\Api\Delegate\DashboardController as DelegateDashboardController;
use App\Http\Controllers\Api\Delegate\SubjectController as DelegateSubjectController;
use App\Http\Controllers\Api\Delegate\ScheduleController as DelegateScheduleController;
use App\Http\Controllers\Api\Delegate\ExamScheduleController as DelegateExamScheduleController;
use App\Http\Controllers\Api\Delegate\AssignmentController as DelegateAssignmentController;
use App\Http\Controllers\Api\Delegate\ResourceController as DelegateResourceController;
use App\Http\Controllers\Api\Delegate\AnnouncementController as DelegateAnnouncementController;
use App\Http\Controllers\Api\Delegate\ReminderController as DelegateReminderController;
use App\Http\Controllers\Api\Delegate\MessageController as DelegateMessageController;
use App\Http\Controllers\Api\Delegate\InquiryController as DelegateInquiryController;
use App\Http\Controllers\Api\Delegate\DoctorChatController as DelegateDoctorChatController;
use App\Http\Controllers\Api\Delegate\AttendanceController as DelegateAttendanceController;
use App\Http\Controllers\Api\Delegate\NotificationController as DelegateNotificationController;

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
    Route::apiResource('universities', AdminUniversityController::class)->names('admin.universities');

    // Academic — Colleges
    Route::apiResource('colleges', AdminCollegeController::class)->names('admin.colleges');

    // Academic — Majors
    Route::apiResource('majors', AdminMajorController::class)->names('admin.majors');

    // Academic — Subjects
    Route::apiResource('subjects', AdminSubjectController::class)->names('admin.subjects');

    // User Management
    Route::get('users', [AdminUserController::class, 'index']);
    Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus']);
    Route::delete('users/{user}', [AdminUserController::class, 'destroy']);
    Route::post('users/bulk-activate', [AdminUserController::class, 'bulkActivate']);
    Route::post('users/bulk-deactivate', [AdminUserController::class, 'bulkDeactivate']);
    Route::post('users/bulk-delete', [AdminUserController::class, 'bulkDelete']);

    // Students
    Route::apiResource('students', AdminStudentController::class)->names('admin.students');

    // Doctors
    Route::apiResource('doctors', AdminDoctorController::class)->names('admin.doctors');

    // Delegates
    Route::apiResource('delegates', AdminDelegateController::class)->names('admin.delegates');

    // Clinical Delegates
    Route::apiResource('clinical-delegates', AdminClinicalDelegateController::class)
        ->only(['index', 'store', 'destroy'])->names('admin.clinical-delegates');

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
// Delegate API — Public (No Auth Required)
// ══════════════════════════════════════════════════════════════
Route::prefix('delegate')->group(function () {
    Route::post('login', [DelegateAuthController::class, 'login']);
});

// ══════════════════════════════════════════════════════════════
// Delegate API — Protected (Sanctum Auth + Delegate Role)
// ══════════════════════════════════════════════════════════════
Route::prefix('delegate')->middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('logout', [DelegateAuthController::class, 'logout']);
    Route::get('me', [DelegateAuthController::class, 'me']);

    // Dashboard
    Route::get('dashboard', [DelegateDashboardController::class, 'index']);

    // Academic
    Route::apiResource('subjects', DelegateSubjectController::class)->only(['index', 'show'])->names('delegate.subjects');
    Route::apiResource('schedules', DelegateScheduleController::class)->except(['show'])->names('delegate.schedules');
    Route::apiResource('exam-schedules', DelegateExamScheduleController::class)->except(['show'])->names('delegate.exam-schedules');
    Route::apiResource('assignments', DelegateAssignmentController::class)->except(['show'])->names('delegate.assignments');
    Route::apiResource('resources', DelegateResourceController::class)->only(['index', 'store', 'destroy'])->names('delegate.resources');

    // Communication
    Route::apiResource('announcements', DelegateAnnouncementController::class)->except(['show'])->names('delegate.announcements');
    Route::apiResource('reminders', DelegateReminderController::class)->except(['show'])->names('delegate.reminders');

    // Messaging
    Route::get('messages', [DelegateMessageController::class, 'index']);
    Route::get('messages/{user}', [DelegateMessageController::class, 'show']);
    Route::post('messages', [DelegateMessageController::class, 'store']);

    Route::get('doctor-chats', [DelegateDoctorChatController::class, 'index']);
    Route::get('doctor-chats/{doctor}', [DelegateDoctorChatController::class, 'show']);
    Route::post('doctor-chats', [DelegateDoctorChatController::class, 'store']);

    Route::get('inquiries', [DelegateInquiryController::class, 'index']);
    Route::get('inquiries/{inquiry}', [DelegateInquiryController::class, 'show']);
    Route::post('inquiries/{inquiry}/reply', [DelegateInquiryController::class, 'storeReply']);
    Route::patch('inquiries/{inquiry}/status', [DelegateInquiryController::class, 'updateStatus']);

    // Tracking
    Route::get('attendances', [DelegateAttendanceController::class, 'index']);
    Route::post('attendances', [DelegateAttendanceController::class, 'store']);
    Route::get('attendances/{subject}/{date}', [DelegateAttendanceController::class, 'show']);

    Route::get('notifications', [DelegateNotificationController::class, 'index']);
    Route::post('notifications', [DelegateNotificationController::class, 'store']);
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

// ══════════════════════════════════════════════════════════════
// Student API Controllers
// ══════════════════════════════════════════════════════════════
use App\Http\Controllers\Api\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Api\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Api\Student\SubjectController as StudentSubjectController;
use App\Http\Controllers\Api\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Api\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Api\Student\Clinical\LogbookController as StudentLogbookController;
use App\Http\Controllers\Api\Student\Clinical\EvaluationController as StudentEvaluationController;
use App\Http\Controllers\Api\Student\Clinical\MockExamController as StudentMockExamController;

// ══════════════════════════════════════════════════════════════
// Student API — Public (No Auth Required)
// ══════════════════════════════════════════════════════════════
Route::prefix('student')->group(function () {
    Route::post('login', [StudentAuthController::class, 'login']);
});

// ══════════════════════════════════════════════════════════════
// Student API — Protected (Sanctum Auth + CheckUserStatus)
// ══════════════════════════════════════════════════════════════
Route::prefix('student')->middleware(['auth:sanctum', \App\Http\Middleware\CheckUserStatus::class])->group(function () {

    // Auth & Profile
    Route::post('logout', [StudentAuthController::class, 'logout']);
    Route::get('me', [StudentAuthController::class, 'me']);

    // Dashboard
    Route::get('dashboard', [StudentDashboardController::class, 'index']);

    // Subjects, Lectures & Library
    Route::get('subjects', [StudentSubjectController::class, 'index']);
    Route::get('subjects/{subject_id}', [StudentSubjectController::class, 'show']);
    Route::post('lectures/{lecture_id}/toggle-listen', [StudentSubjectController::class, 'toggleListen']);

    // Assignments
    Route::get('assignments', [StudentAssignmentController::class, 'index']);
    Route::post('assignments/{assignment_id}/submit', [StudentAssignmentController::class, 'submit']);

    // Attendance Info
    Route::get('attendance', [StudentAttendanceController::class, 'index']);

    // Clinical Section
    Route::prefix('clinical')->group(function () {

        // Logbook
        Route::get('logbook', [StudentLogbookController::class, 'index']);
        Route::post('logbook', [StudentLogbookController::class, 'store']);
        Route::put('logbook/{log_id}', [StudentLogbookController::class, 'update']);
        Route::delete('logbook/{log_id}', [StudentLogbookController::class, 'destroy']);
        Route::get('logbook/export-pdf', [StudentLogbookController::class, 'exportPdf']);

        // Evaluations (OSCE)
        Route::get('evaluations', [StudentEvaluationController::class, 'index']);

        // Mock Exams
        Route::get('mock-exams', [StudentMockExamController::class, 'index']);
        Route::post('mock-exams/custom', [StudentMockExamController::class, 'storeCustom']);
        Route::delete('mock-exams/custom/{checklist_id}', [StudentMockExamController::class, 'destroyCustom']);
        Route::get('mock-exams/{checklist_id}/take', [StudentMockExamController::class, 'take']);
        Route::post('mock-exams/{checklist_id}/submit', [StudentMockExamController::class, 'submit']);
        Route::get('mock-exams/results/{evaluation_id}', [StudentMockExamController::class, 'showResult']);
    });
});

// ══════════════════════════════════════════════════════════════
// Doctor API Controllers
// ══════════════════════════════════════════════════════════════
use App\Http\Controllers\Api\Doctor\AuthController as DoctorAuthController;
use App\Http\Controllers\Api\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Api\Doctor\ExcuseController as DoctorExcuseController;
use App\Http\Controllers\Api\Doctor\ReportController as DoctorReportController;
use App\Http\Controllers\Api\Doctor\AssignmentController as DoctorAssignmentController;
use App\Http\Controllers\Api\Doctor\InquiryController as DoctorInquiryController;
use App\Http\Controllers\Api\Doctor\GradeController as DoctorGradeController;
use App\Http\Controllers\Api\Doctor\MessageController as DoctorMessageController;
use App\Http\Controllers\Api\Doctor\NotificationController as DoctorNotificationController;
use App\Http\Controllers\Api\Doctor\Clinical\ClinicalController as DoctorClinicalController;
use App\Http\Controllers\Api\Doctor\Clinical\TrainingCenterController as DoctorTrainingCenterController;
use App\Http\Controllers\Api\Doctor\Clinical\DepartmentController as DoctorDepartmentController;
use App\Http\Controllers\Api\Doctor\Clinical\BodySystemController as DoctorBodySystemController;
use App\Http\Controllers\Api\Doctor\Clinical\CaseController as DoctorCaseController;
use App\Http\Controllers\Api\Doctor\Clinical\CaseAssignmentController as DoctorCaseAssignmentController;
use App\Http\Controllers\Api\Doctor\Clinical\LogbookController as DoctorLogbookController;
use App\Http\Controllers\Api\Doctor\Clinical\EvaluationController as DoctorEvaluationController;

// ══════════════════════════════════════════════════════════════
// Doctor API — Public (No Auth Required)
// ══════════════════════════════════════════════════════════════
Route::prefix('doctor')->group(function () {
    Route::post('login', [DoctorAuthController::class, 'login']);
});

// ══════════════════════════════════════════════════════════════
// Doctor API — Protected (Sanctum Auth + Doctor Role)
// ══════════════════════════════════════════════════════════════
Route::prefix('doctor')->middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('logout', [DoctorAuthController::class, 'logout']);
    Route::get('me', [DoctorAuthController::class, 'me']);

    // Dashboard
    Route::get('dashboard', [DoctorDashboardController::class, 'index']);

    // Excuses
    Route::get('excuses', [DoctorExcuseController::class, 'index']);
    Route::put('excuses/{excuse}', [DoctorExcuseController::class, 'update']);

    // Reports
    Route::get('reports', [DoctorReportController::class, 'index']);
    Route::get('reports/{subject}', [DoctorReportController::class, 'show']);

    // Assignments
    Route::get('assignments', [DoctorAssignmentController::class, 'index']);
    Route::post('assignments', [DoctorAssignmentController::class, 'store']);
    Route::put('assignments/{assignment}', [DoctorAssignmentController::class, 'update']);
    Route::delete('assignments/{assignment}', [DoctorAssignmentController::class, 'destroy']);
    Route::get('assignments/{assignment}/submissions', [DoctorAssignmentController::class, 'submissions']);
    Route::post('submissions/{submission}/review', [DoctorAssignmentController::class, 'reviewSubmission']);

    // Inquiries
    Route::get('inquiries', [DoctorInquiryController::class, 'index']);
    Route::get('inquiries/{id}', [DoctorInquiryController::class, 'show']);
    Route::post('inquiries/{id}/answer', [DoctorInquiryController::class, 'answer']);

    // Grades
    Route::get('grades', [DoctorGradeController::class, 'index']);
    Route::get('grades/{subject}', [DoctorGradeController::class, 'show']);
    Route::post('grades/{subject}', [DoctorGradeController::class, 'store']);
    Route::get('grades/{subject}/report', [DoctorGradeController::class, 'report']);
    Route::post('grades/{subject}/note/{student}', [DoctorGradeController::class, 'storeNote']);

    // Messages
    Route::get('messages', [DoctorMessageController::class, 'index']);
    Route::get('messages/{conversation}', [DoctorMessageController::class, 'show']);
    Route::post('messages', [DoctorMessageController::class, 'store']);
    Route::post('messages/{conversation}/send', [DoctorMessageController::class, 'send']);

    // Notifications
    Route::get('notifications', [DoctorNotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [DoctorNotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [DoctorNotificationController::class, 'markAllAsRead']);

    // Clinical Section
    Route::prefix('clinical')->group(function () {

        // Overview
        Route::get('overview', [DoctorClinicalController::class, 'index']);

        // Training Centers
        Route::get('training-centers', [DoctorTrainingCenterController::class, 'index']);
        Route::post('training-centers', [DoctorTrainingCenterController::class, 'store']);
        Route::put('training-centers/{id}', [DoctorTrainingCenterController::class, 'update']);
        Route::delete('training-centers/{id}', [DoctorTrainingCenterController::class, 'destroy']);

        // Departments
        Route::get('departments', [DoctorDepartmentController::class, 'index']);
        Route::post('departments', [DoctorDepartmentController::class, 'store']);
        Route::put('departments/{id}', [DoctorDepartmentController::class, 'update']);
        Route::delete('departments/{id}', [DoctorDepartmentController::class, 'destroy']);

        // Body Systems
        Route::get('body-systems', [DoctorBodySystemController::class, 'index']);
        Route::post('body-systems', [DoctorBodySystemController::class, 'store']);
        Route::put('body-systems/{id}', [DoctorBodySystemController::class, 'update']);
        Route::delete('body-systems/{id}', [DoctorBodySystemController::class, 'destroy']);

        // Cases
        Route::get('cases', [DoctorCaseController::class, 'index']);
        Route::post('cases', [DoctorCaseController::class, 'store']);
        Route::get('cases/{id}', [DoctorCaseController::class, 'show']);
        Route::put('cases/{id}', [DoctorCaseController::class, 'update']);
        Route::delete('cases/{id}', [DoctorCaseController::class, 'destroy']);

        // Case Assignments
        Route::get('assignments', [DoctorCaseAssignmentController::class, 'index']);
        Route::post('assignments', [DoctorCaseAssignmentController::class, 'store']);

        // Logbook / QR
        Route::post('logbook/scan', [DoctorLogbookController::class, 'processQr']);
        Route::post('logbook/confirm', [DoctorLogbookController::class, 'confirm']);
        Route::get('logbook/records', [DoctorLogbookController::class, 'records']);
        Route::post('logbook/manual', [DoctorLogbookController::class, 'manualAttendance']);

        // Evaluations
        Route::get('evaluations/checklists', [DoctorEvaluationController::class, 'checklists']);
        Route::post('evaluations/checklists', [DoctorEvaluationController::class, 'storeChecklist']);
        Route::put('evaluations/checklists/{id}', [DoctorEvaluationController::class, 'updateChecklist']);
        Route::delete('evaluations/checklists/{id}', [DoctorEvaluationController::class, 'destroyChecklist']);
        Route::get('evaluations/start-data', [DoctorEvaluationController::class, 'startData']);
        Route::post('evaluations/submit', [DoctorEvaluationController::class, 'submit']);
        Route::get('evaluations/results', [DoctorEvaluationController::class, 'results']);
        Route::get('evaluations/results/{id}', [DoctorEvaluationController::class, 'showResult']);
    });
});
