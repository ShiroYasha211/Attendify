<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware('guest')
    ->group(function () {
        Route::get('login', [App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])
            ->name('login');
        Route::post('login', [App\Http\Controllers\Admin\AuthController::class, 'login']);
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('about', [App\Http\Controllers\Admin\DashboardController::class, 'about'])
            ->name('about');
        Route::post('logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])
            ->name('logout');

        // Academic Routes
        Route::resource('universities', App\Http\Controllers\Admin\Academic\UniversityController::class);
        Route::resource('colleges', App\Http\Controllers\Admin\Academic\CollegeController::class);
        Route::resource('majors', App\Http\Controllers\Admin\Academic\MajorController::class);
        // Route::resource('levels', App\Http\Controllers\Admin\Academic\LevelController::class); // Managed automatically by Major
        // Route::resource('terms', App\Http\Controllers\Admin\Academic\TermController::class);   // Managed automatically by Major
        Route::resource('subjects', App\Http\Controllers\Admin\Academic\SubjectController::class);


        // User Management Routes
        Route::resource('delegates', App\Http\Controllers\Admin\DelegateController::class);
        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
        Route::resource('doctors', App\Http\Controllers\Admin\DoctorController::class);

        // Attendance Routes - REMOVED (Delegate Duty Only)
        // Route::get('attendance/create', [App\Http\Controllers\Admin\AttendanceController::class, 'create'])->name('attendance.create');
        // Route::get('attendance/form', [App\Http\Controllers\Admin\AttendanceController::class, 'showCorrectionForm'])->name('attendance.form');
        // Route::post('attendance/store', [App\Http\Controllers\Admin\AttendanceController::class, 'store'])->name('attendance.store');

        // Reports Routes
        Route::get('reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/subject', [App\Http\Controllers\Admin\ReportController::class, 'subjectReport'])->name('reports.subject');
        Route::get('reports/threshold', [App\Http\Controllers\Admin\ReportController::class, 'thresholdReport'])->name('reports.threshold');
    });

Route::prefix('doctor')
    ->name('doctor.')
    ->middleware(['auth', 'role:doctor'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Doctor\DashboardController::class, 'index'])->name('dashboard');
        Route::get('reports/{subject}', [App\Http\Controllers\Doctor\DashboardController::class, 'showSubjectReport'])->name('reports.show');

        // Excuses Routes
        Route::get('excuses', [App\Http\Controllers\Doctor\ExcuseController::class, 'index'])->name('excuses.index');
        Route::put('excuses/{excuse}', [App\Http\Controllers\Doctor\ExcuseController::class, 'update'])->name('excuses.update');

        // Reports Routes
        Route::get('reports', [App\Http\Controllers\Doctor\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{subject}', [App\Http\Controllers\Doctor\DashboardController::class, 'showSubjectReport'])->name('reports.show');

        // Assignments Routes
        Route::resource('assignments', App\Http\Controllers\Doctor\AssignmentController::class)->except(['create', 'edit', 'show']);
    });

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'role:student'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('subjects', App\Http\Controllers\Student\SubjectController::class)->only(['index', 'show']);
        Route::get('schedule', [App\Http\Controllers\Student\ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('assignments', [App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('excuse', [App\Http\Controllers\Student\ExcuseController::class, 'store'])->name('excuse.store');
        Route::get('reminders', [App\Http\Controllers\Student\ReminderController::class, 'index'])->name('reminders.index');
        Route::get('announcements', [App\Http\Controllers\Student\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('alerts', [App\Http\Controllers\Student\AlertController::class, 'index'])->name('alerts.index');
        Route::post('alerts/{id}/read', [App\Http\Controllers\Student\AlertController::class, 'markAsRead'])->name('alerts.read');
    });

Route::prefix('delegate')
    ->name('delegate.')
    ->middleware(['auth', 'role:delegate'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Delegate\DashboardController::class, 'index'])->name('dashboard');

        // Students Management
        Route::resource('students', App\Http\Controllers\Delegate\StudentController::class);

        // Subjects & Schedule
        Route::resource('subjects', App\Http\Controllers\Delegate\SubjectController::class)->only(['index', 'edit', 'update']);
        Route::resource('schedules', App\Http\Controllers\Delegate\ScheduleController::class)->except(['show']);

        // Notifications
        Route::get('notifications', [App\Http\Controllers\Delegate\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications', [App\Http\Controllers\Delegate\NotificationController::class, 'store'])->name('notifications.store');

        // Attendance
        Route::get('attendance', [App\Http\Controllers\Delegate\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/{subject}/create', [App\Http\Controllers\Delegate\AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance/{subject}', [App\Http\Controllers\Delegate\AttendanceController::class, 'store'])->name('attendance.store');
        // Assignments
        Route::resource('assignments', App\Http\Controllers\Delegate\AssignmentController::class)->except(['create', 'edit', 'show']);

        // Announcements
        Route::resource('announcements', App\Http\Controllers\Delegate\AnnouncementController::class)->except(['create', 'edit', 'show']);

        // Reminders
        Route::resource('reminders', App\Http\Controllers\Delegate\ReminderController::class)->except(['create', 'edit', 'show']);

        // Attendance Report Route (Correctly placed inside group)
        Route::get('attendance/{subject}/{date}/report', [App\Http\Controllers\Delegate\AttendanceController::class, 'showReport'])->name('attendance.report');
    });
