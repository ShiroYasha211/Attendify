<?php

use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::redirect('/', '/admin/login');

Route::prefix('admin')
    ->name('admin.')
    ->middleware('guest')
    ->group(function () {
        Route::get('login', [App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])
            ->name('login');
        Route::post('login', [App\Http\Controllers\Admin\AuthController::class, 'login']);

        Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])
            ->name('register');
        Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'status'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Finance & Earnings
        Route::get('finance', [App\Http\Controllers\Admin\FinanceController::class, 'index'])->name('finance.index');

        // Profile & Password
        Route::get('profile/password', [App\Http\Controllers\Auth\PasswordController::class, 'edit'])->name('profile.password');
        Route::put('profile/password', [App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('profile.password.update');

        Route::get('about', [App\Http\Controllers\Admin\DashboardController::class, 'about'])
            ->name('about');
        Route::post('logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])
            ->name('logout');

        // Academic Routes
        Route::resource('universities', App\Http\Controllers\Admin\Academic\UniversityController::class);
        Route::resource('colleges', App\Http\Controllers\Admin\Academic\CollegeController::class);
        Route::resource('majors', App\Http\Controllers\Admin\Academic\MajorController::class);
        Route::resource('subjects', App\Http\Controllers\Admin\Academic\SubjectController::class);

        // Clinical Constants
        Route::prefix('clinical')->name('clinical.')->group(function () {
            Route::resource('departments', App\Http\Controllers\Admin\Clinical\ClinicalDepartmentController::class)->except(['show']);
            Route::resource('body-systems', App\Http\Controllers\Admin\Clinical\BodySystemController::class)->except(['show']);
            Route::resource('checklists', App\Http\Controllers\Admin\Clinical\EvaluationChecklistController::class);
        });


        // User Management Routes
        Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
        Route::patch('users/{user}/status', [App\Http\Controllers\Admin\UserController::class, 'updateStatus'])->name('users.status');
        Route::patch('users/{user}/activate-subscription', [App\Http\Controllers\Admin\UserController::class, 'activateSubscription'])->name('users.activate-subscription');
        Route::post('users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/kick', [App\Http\Controllers\Admin\UserController::class, 'kickSession'])->name('users.kick');
        Route::delete('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Bulk Actions
        Route::post('users/bulk-activate', [App\Http\Controllers\Admin\UserController::class, 'bulkActivate'])->name('users.bulk-activate');
        Route::post('users/bulk-deactivate', [App\Http\Controllers\Admin\UserController::class, 'bulkDeactivate'])->name('users.bulk-deactivate');
        Route::post('users/bulk-delete', [App\Http\Controllers\Admin\UserController::class, 'bulkDelete'])->name('users.bulk-delete');

        Route::resource('delegates', App\Http\Controllers\Admin\DelegateController::class);
        
        // Delegate Transfer Routes
        Route::get('delegates-transfer', [App\Http\Controllers\Admin\DelegateTransferController::class, 'index'])->name('delegates.transfer.index');
        Route::get('delegates-transfer/{major}/{level}', [App\Http\Controllers\Admin\DelegateTransferController::class, 'show'])->name('delegates.transfer.show');
        Route::post('delegates-transfer', [App\Http\Controllers\Admin\DelegateTransferController::class, 'transfer'])->name('delegates.transfer.perform');
        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
        Route::post('students/{student}/permissions', [App\Http\Controllers\Admin\StudentController::class, 'updatePermissions'])->name('students.permissions');
        Route::resource('doctors', App\Http\Controllers\Admin\DoctorController::class);

        // Clinical Delegate Management
        Route::resource('clinical-delegates', App\Http\Controllers\Admin\ClinicalDelegateController::class)
            ->only(['index', 'store', 'destroy']);

        // Registration Requests
        Route::get('registration-requests', [App\Http\Controllers\Admin\RegistrationRequestController::class, 'index'])
            ->name('registration_requests.index');
        Route::post('registration-requests/{user}/approve', [App\Http\Controllers\Admin\RegistrationRequestController::class, 'approve'])
            ->name('registration_requests.approve');
        Route::post('registration-requests/{user}/reject', [App\Http\Controllers\Admin\RegistrationRequestController::class, 'reject'])
            ->name('registration_requests.reject');

        // Attendance Routes - REMOVED (Delegate Duty Only)
        // Route::get('attendance/create', [App\Http\Controllers\Admin\AttendanceController::class, 'create'])->name('attendance.create');
        // Route::get('attendance/form', [App\Http\Controllers\Admin\AttendanceController::class, 'showCorrectionForm'])->name('attendance.form');
        // Route::post('attendance/store', [App\Http\Controllers\Admin\AttendanceController::class, 'store'])->name('attendance.store');

        // Reports Routes
        Route::get('reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/subject', [App\Http\Controllers\Admin\ReportController::class, 'subjectReport'])->name('reports.subject');
        Route::get('reports/threshold', [App\Http\Controllers\Admin\ReportController::class, 'thresholdReport'])->name('reports.threshold');
        Route::get('reports/level-summary', [App\Http\Controllers\Admin\ReportController::class, 'levelSummary'])->name('reports.level-summary');
        Route::get('reports/doctor-performance', [App\Http\Controllers\Admin\ReportController::class, 'doctorPerformance'])->name('reports.doctor-performance');
        Route::get('reports/assignments', [App\Http\Controllers\Admin\ReportController::class, 'assignmentsReport'])->name('reports.assignments');
        Route::get('reports/system-overview', [App\Http\Controllers\Admin\ReportController::class, 'systemOverview'])->name('reports.system-overview');


        // Activity Log Routes
        Route::get('activities', [App\Http\Controllers\Admin\ActivityController::class, 'index'])->name('activities.index');
        Route::delete('activities/cleanup', [App\Http\Controllers\Admin\ActivityController::class, 'cleanup'])->name('activities.cleanup');

        // Shared Library Management
        Route::resource('library', App\Http\Controllers\Admin\LibraryController::class)->names('library');
        Route::get('library/{resource}/download', [App\Http\Controllers\Admin\LibraryController::class, 'download'])->name('library.download');

        // Storage Management Routes
        Route::get('storage', [App\Http\Controllers\Admin\StorageController::class, 'index'])->name('storage.index');
        Route::delete('storage/{type}/{id}', [App\Http\Controllers\Admin\StorageController::class, 'destroy'])->name('storage.destroy');

        // Subscription & Packages Management
        Route::patch('packages/{package}/toggle', [App\Http\Controllers\Admin\PackageController::class, 'toggleStatus'])->name('packages.toggle');
        Route::resource('packages', App\Http\Controllers\Admin\PackageController::class);
        Route::get('packages/{package}/subscribers', [App\Http\Controllers\Admin\PackageController::class, 'subscribers'])->name('packages.subscribers');
        Route::post('subscriptions/{subscription}/cancel', [App\Http\Controllers\Admin\PackageController::class, 'cancelSubscription'])->name('subscriptions.cancel');
        Route::get('cards', [App\Http\Controllers\Admin\CardController::class, 'index'])->name('cards.index');
        Route::post('cards/generate', [App\Http\Controllers\Admin\CardController::class, 'generate'])->name('cards.generate');
        Route::delete('cards/{card}', [App\Http\Controllers\Admin\CardController::class, 'destroy'])->name('cards.destroy');

        // Settings Routes
        Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    });

Route::prefix('doctor')
    ->name('doctor.')
    ->middleware(['auth', 'role:doctor', 'status', 'subscribed'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Doctor\DashboardController::class, 'index'])->name('dashboard');

        // Profile & Password
        Route::get('profile/password', [App\Http\Controllers\Auth\PasswordController::class, 'edit'])->name('profile.password');
        Route::put('profile/password', [App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('profile.password.update');

        // Excuses Routes
        Route::get('excuses', [App\Http\Controllers\Doctor\ExcuseController::class, 'index'])->name('excuses.index');
        Route::put('excuses/{excuse}', [App\Http\Controllers\Doctor\ExcuseController::class, 'update'])->name('excuses.update');

        // Reports Routes
        Route::get('reports', [App\Http\Controllers\Doctor\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{subject}', [App\Http\Controllers\Doctor\DashboardController::class, 'showSubjectReport'])->name('reports.show');

        // Assignments Routes
        Route::resource('assignments', App\Http\Controllers\Doctor\AssignmentController::class)->except(['create', 'edit', 'show']);
        Route::get('assignments/{assignment}/submissions', [App\Http\Controllers\Doctor\AssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::post('submissions/{submission}/review', [App\Http\Controllers\Doctor\AssignmentController::class, 'reviewSubmission'])->name('submissions.review');

        // Inquiries Routes (Student Questions)
        Route::get('inquiries', [App\Http\Controllers\Doctor\InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/{inquiry}', [App\Http\Controllers\Doctor\InquiryController::class, 'show'])->name('inquiries.show');
        Route::post('inquiries/{inquiry}/answer', [App\Http\Controllers\Doctor\InquiryController::class, 'answer'])->name('inquiries.answer');

        // Grades Routes
        Route::get('grades', [App\Http\Controllers\Doctor\GradeController::class, 'index'])->name('grades.index');
        Route::get('grades/{subject}', [App\Http\Controllers\Doctor\GradeController::class, 'show'])->name('grades.show');
        Route::post('grades/{subject}', [App\Http\Controllers\Doctor\GradeController::class, 'store'])->name('grades.store');
        Route::get('grades/{subject}/report', [App\Http\Controllers\Doctor\GradeController::class, 'report'])->name('grades.report');
        Route::post('grades/{subject}/note/{student}', [App\Http\Controllers\Doctor\GradeController::class, 'storeNote'])->name('grades.storeNote');

        // Attendance Routes
        Route::get('attendance', [App\Http\Controllers\Doctor\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/{subject}/create', [App\Http\Controllers\Doctor\AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('attendance/{subject}', [App\Http\Controllers\Doctor\AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('attendance/{subject}/toggle-delegate', [App\Http\Controllers\Doctor\AttendanceController::class, 'toggleDelegateAttendance'])->name('attendance.toggle-delegate');
        Route::get('attendance/{subject}/{date}/report', [App\Http\Controllers\Doctor\AttendanceController::class, 'showReport'])->name('attendance.report');

        // Messages (Chat with Delegates)
        Route::get('messages', [App\Http\Controllers\Doctor\DoctorMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/create', [App\Http\Controllers\Doctor\DoctorMessageController::class, 'create'])->name('messages.create');
        Route::post('messages/start', [App\Http\Controllers\Doctor\DoctorMessageController::class, 'store'])->name('messages.store');
        Route::get('messages/{conversation}', [App\Http\Controllers\Doctor\DoctorMessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{conversation}/send', [App\Http\Controllers\Doctor\DoctorMessageController::class, 'send'])->name('messages.send');

        // Notifications
        Route::get('notifications', [App\Http\Controllers\Doctor\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read', [App\Http\Controllers\Doctor\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('notifications/mark-all-read', [App\Http\Controllers\Doctor\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

        // Financial Ledger
        Route::get('ledger', [App\Http\Controllers\FinancialController::class, 'ledger'])->name('ledger');
        Route::get('ledger/export', [App\Http\Controllers\FinancialController::class, 'exportPdf'])->name('ledger.export');

        // Clinical Training Hub
        Route::prefix('clinical')->name('clinical.')->group(function () {
            // Dashboard / Overview of Centers & Departments
            Route::get('/', [App\Http\Controllers\Doctor\ClinicalController::class, 'index'])->name('index');

            // Manage Training Centers
            Route::resource('training-centers', App\Http\Controllers\Doctor\Clinical\TrainingCenterController::class)->except(['show']);

            // Manage Clinical Departments
            Route::post('departments/restore', [App\Http\Controllers\Doctor\Clinical\ClinicalDepartmentController::class, 'restoreDefaults'])->name('departments.restore');
            Route::resource('departments', App\Http\Controllers\Doctor\Clinical\ClinicalDepartmentController::class)->except(['show']);

            // Manage Body Systems
            Route::post('body-systems/restore', [App\Http\Controllers\Doctor\Clinical\BodySystemController::class, 'restoreDefaults'])->name('body-systems.restore');
            Route::resource('body-systems', App\Http\Controllers\Doctor\Clinical\BodySystemController::class)->except(['show']);

            // Manage Clinical Cases
            Route::resource('cases', App\Http\Controllers\Doctor\Clinical\ClinicalCaseController::class);

            // Assign Cases to Students
            Route::get('assignments', [App\Http\Controllers\Doctor\Clinical\AssignmentController::class, 'index'])->name('assignments.index');
            Route::post('assignments', [App\Http\Controllers\Doctor\Clinical\AssignmentController::class, 'store'])->name('assignments.store');

            // QR Scanner & Logbook Records (Doctor scans student QR)
            Route::get('scanner', [App\Http\Controllers\Doctor\Clinical\LogbookScannerController::class, 'scanner'])->name('scanner');
            Route::post('scanner/process', [App\Http\Controllers\Doctor\Clinical\LogbookScannerController::class, 'processQr'])->name('scanner.process');
            Route::post('scanner/confirm', [App\Http\Controllers\Doctor\Clinical\LogbookScannerController::class, 'confirm'])->name('scanner.confirm');
            Route::get('logbook-records', [App\Http\Controllers\Doctor\Clinical\LogbookScannerController::class, 'records'])->name('logbook-records');
            Route::get('manual-attendance', [App\Http\Controllers\Doctor\Clinical\LogbookScannerController::class, 'manualAttendance'])->name('manual-attendance');
            Route::post('manual-attendance', [App\Http\Controllers\Doctor\Clinical\LogbookScannerController::class, 'storeManualAttendance'])->name('manual-attendance.store');

            // OSCE Evaluations
            Route::prefix('evaluations')->name('evaluations.')->group(function () {
                Route::post('checklists/restore', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'restoreDefaults'])->name('checklists.restore');
                Route::get('checklists', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'checklists'])->name('checklists');
                Route::get('checklists/create', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'createChecklist'])->name('checklists.create');
                Route::post('checklists', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'storeChecklist'])->name('checklists.store');
                Route::get('checklists/{id}/edit', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'editChecklist'])->name('checklists.edit');
                Route::put('checklists/{id}', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'updateChecklist'])->name('checklists.update');
                Route::delete('checklists/{id}', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'destroyChecklist'])->name('checklists.destroy');
                Route::get('start', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'startEvaluation'])->name('start');
                Route::post('live', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'liveEvaluate'])->name('live');
                Route::post('submit', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'submitEvaluation'])->name('submit');
                Route::get('results', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'results'])->name('results');
                Route::get('results/{id}', [App\Http\Controllers\Doctor\Clinical\EvaluationController::class, 'showResult'])->name('results.show');
            });
        });

        // Shared Subscription Routes
        Route::get('subscription', [App\Http\Controllers\Student\SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('subscription/redeem', [App\Http\Controllers\Student\SubscriptionController::class, 'redeem'])->name('subscription.redeem');
        Route::post('subscription/subscribe', [App\Http\Controllers\Student\SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
        Route::post('subscription/auto-renew', [App\Http\Controllers\Student\SubscriptionController::class, 'toggleAutoRenew'])->name('subscription.toggleAutoRenew');

        // Card Generation (Balance-based)
        Route::get('cards-generate', [App\Http\Controllers\Student\CardGenerationController::class, 'index'])->name('cards.generate.index');
        Route::post('cards-generate', [App\Http\Controllers\Student\CardGenerationController::class, 'generate'])->name('cards.generate.store');

        // Shared Study Library (Unified)
        Route::get('library', [App\Http\Controllers\Student\LibraryController::class, 'index'])->name('library.index');
        Route::get('library/create', [App\Http\Controllers\Student\LibraryController::class, 'create'])->name('library.create');
        Route::post('library/upload', [App\Http\Controllers\Student\LibraryController::class, 'store'])->name('library.store');
        Route::get('library/{resource}/download', [App\Http\Controllers\Student\LibraryController::class, 'incrementDownload'])->name('library.download');
    });

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'role:student', 'status', 'subscribed'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');

        // Profile & Password
        Route::get('profile/password', [App\Http\Controllers\Auth\PasswordController::class, 'edit'])->name('profile.password');
        Route::put('profile/password', [App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('profile.password.update');

        Route::resource('subjects', App\Http\Controllers\Student\SubjectController::class)->only(['index', 'show']);

        Route::get('assignments', [App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('assignments/preference', [App\Http\Controllers\Student\AssignmentController::class, 'updatePreference'])->name('assignments.updatePreference');
        Route::get('assignments/{assignment}/details', [App\Http\Controllers\Student\AssignmentController::class, 'getDetails'])->name('assignments.getDetails');
        Route::get('assignments/{assignment}', [App\Http\Controllers\Student\AssignmentController::class, 'show'])->name('assignments.show');
        Route::post('assignments/{assignment}/submit', [App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');
        Route::post('assignments/{assignment}/priority', [App\Http\Controllers\Student\AssignmentController::class, 'updatePriority'])->name('assignments.updatePriority');
        // Deprecated: Global Attendance page is merged into Subject Details
        // Route::get('attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/scan', function () {
            return view('student.attendance.scan');
        })->name('attendance.scan');
        Route::post('excuse', [App\Http\Controllers\Student\ExcuseController::class, 'store'])->name('excuse.store');
        Route::get('reminders', [App\Http\Controllers\Student\ReminderController::class, 'index'])->name('reminders.index');
        Route::get('resources', [App\Http\Controllers\Student\ResourceController::class, 'index'])->name('resources.index');
        Route::get('announcements', [App\Http\Controllers\Student\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('alerts', [App\Http\Controllers\Student\AlertController::class, 'index'])->name('alerts.index');
        Route::post('alerts/{id}/read', [App\Http\Controllers\Student\AlertController::class, 'markAsRead'])->name('alerts.read');

        // Exam Schedules
        Route::get('exams', [App\Http\Controllers\Student\ExamScheduleController::class, 'index'])->name('exams.index');

        // Grades - MOVED to Subject Details
        // Route::get('grades', [App\Http\Controllers\Student\GradeController::class, 'index'])->name('grades.index');

        // Notifications
        Route::get('notifications', [App\Http\Controllers\Student\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read', [App\Http\Controllers\Student\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('notifications/mark-all-read', [App\Http\Controllers\Student\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

        // Financial Ledger
        Route::get('ledger', [App\Http\Controllers\FinancialController::class, 'ledger'])->name('ledger');
        Route::get('ledger/export', [App\Http\Controllers\FinancialController::class, 'exportPdf'])->name('ledger.export');

        // Messages (Chat System)
        Route::get('messages', [App\Http\Controllers\Student\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/start', [App\Http\Controllers\Student\MessageController::class, 'start'])->name('messages.start');
        Route::get('messages/{conversation}', [App\Http\Controllers\Student\MessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{conversation}/send', [App\Http\Controllers\Student\MessageController::class, 'send'])->name('messages.send');

        // Card Generation (Balance-based)
        Route::get('cards-generate', [App\Http\Controllers\Student\CardGenerationController::class, 'index'])->name('cards.generate.index');
        Route::post('cards-generate', [App\Http\Controllers\Student\CardGenerationController::class, 'generate'])->name('cards.generate.store');

        // Inquiries (Doctor Questions)
        Route::get('inquiries', [App\Http\Controllers\Student\InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/create', [App\Http\Controllers\Student\InquiryController::class, 'create'])->name('inquiries.create');
        Route::post('inquiries', [App\Http\Controllers\Student\InquiryController::class, 'store'])->name('inquiries.store');
        Route::get('inquiries/{inquiry}', [App\Http\Controllers\Student\InquiryController::class, 'show'])->name('inquiries.show');

        Route::prefix('clinical')->name('clinical.')->group(function () {
            Route::get('/', [App\Http\Controllers\Student\Clinical\LogbookController::class, 'index'])->name('index');
            Route::get('daily-log/create', [App\Http\Controllers\Student\Clinical\LogbookController::class, 'createDailyLog'])->name('daily-log.create');
            Route::post('daily-log', [App\Http\Controllers\Student\Clinical\LogbookController::class, 'storeDailyLog'])->name('daily-log.store');
            Route::get('daily-log/{id}/qr', [App\Http\Controllers\Student\Clinical\LogbookController::class, 'showQr'])->name('show-qr');
            Route::post('daily-log/{id}/regenerate', [App\Http\Controllers\Student\Clinical\LogbookController::class, 'regenerateQr'])->name('daily-log.regenerate');
            Route::delete('daily-log/{id}/cancel', [App\Http\Controllers\Student\Clinical\LogbookController::class, 'cancelDailyLog'])->name('daily-log.cancel');
            Route::get('logbook', [App\Http\Controllers\Student\Clinical\LogbookController::class, 'myLogbook'])->name('logbook');
            Route::get('logbook/export-pdf', [App\Http\Controllers\Student\Clinical\LogbookController::class, 'exportPdf'])->name('logbook.export_pdf');
            // Route::post('logbook/store', [LogbookController::class, 'store'])->name('logbook.store'); // This line was malformed in the instruction, assuming it's not needed or should be fixed.

            // Mock OSCE Exams
            Route::get('mock-exams', [App\Http\Controllers\Student\Clinical\MockExamController::class, 'index'])->name('mock.index');
            Route::get('mock-exams/create-custom', [App\Http\Controllers\Student\Clinical\MockExamController::class, 'createCustom'])->name('mock.create_custom');
            Route::post('mock-exams/store-custom', [App\Http\Controllers\Student\Clinical\MockExamController::class, 'storeCustom'])->name('mock.store_custom');
            Route::delete('mock-exams/{id}/destroy-custom', [App\Http\Controllers\Student\Clinical\MockExamController::class, 'destroyCustom'])->name('mock.destroy_custom');
            Route::get('mock-exams/take/{checklist}', [App\Http\Controllers\Student\Clinical\MockExamController::class, 'take'])->name('mock.take');
            Route::post('mock-exams/store', [App\Http\Controllers\Student\Clinical\MockExamController::class, 'store'])->name('mock.store');
            Route::get('mock-exams/{id}', [App\Http\Controllers\Student\Clinical\MockExamController::class, 'show'])->name('mock.show');

            Route::get('evaluations', [App\Http\Controllers\Student\Clinical\EvaluationController::class, 'index'])->name('evaluations');
            Route::get('evaluations/{id}', [App\Http\Controllers\Student\Clinical\EvaluationController::class, 'show'])->name('evaluations.show');
        });

        // PDF Reports
        Route::get('reports/attendance', [App\Http\Controllers\Student\ReportController::class, 'attendancePdf'])->name('reports.attendance');
        Route::get('reports/grades', [App\Http\Controllers\Student\ReportController::class, 'gradesPdf'])->name('reports.grades');
        Route::get('reports/exams', [App\Http\Controllers\Student\ReportController::class, 'examsPdf'])->name('reports.exams');

        // Lectures Tracking
        Route::get('lectures/{subject}', [App\Http\Controllers\Student\LectureController::class, 'index'])->name('lectures.index');
        Route::post('lectures/toggle/{lecture}', [App\Http\Controllers\Student\LectureController::class, 'toggleStatus'])->name('lectures.toggle');

        // Study Schedule (Read-Only Mirror of Delegate's Schedule)
        Route::get('schedules', [App\Http\Controllers\Student\ScheduleController::class, 'index'])->name('schedules.index');

        // Student Schedule (Smart Study Hub)
        Route::get('schedule', [App\Http\Controllers\Student\StudentScheduleController::class, 'index'])->name('schedule.index');
        Route::post('schedule', [App\Http\Controllers\Student\StudentScheduleController::class, 'store'])->name('schedule.store');
        Route::post('schedule/custom-task', [App\Http\Controllers\Student\StudentScheduleController::class, 'storeCustomTask'])->name('schedule.storeCustomTask');
        Route::get('schedule/check-reminders', [App\Http\Controllers\Student\StudentScheduleController::class, 'checkReminders'])->name('schedule.checkReminders');
        Route::put('schedule/{id}', [App\Http\Controllers\Student\StudentScheduleController::class, 'update'])->name('schedule.update');
        Route::delete('schedule/{id}', [App\Http\Controllers\Student\StudentScheduleController::class, 'destroy'])->name('schedule.destroy');
        Route::post('schedule/reorder', [App\Http\Controllers\Student\StudentScheduleController::class, 'reorder'])->name('schedule.reorder');

        // Shared Study Library
        Route::get('library', [App\Http\Controllers\Student\LibraryController::class, 'index'])->name('library.index');
        Route::get('library/create', [App\Http\Controllers\Student\LibraryController::class, 'create'])->name('library.create');
        Route::post('library/upload', [App\Http\Controllers\Student\LibraryController::class, 'store'])->name('library.store');
        Route::get('library/{resource}/download', [App\Http\Controllers\Student\LibraryController::class, 'incrementDownload'])->name('library.download');

        // Shared Subscription Routes
        Route::get('subscription', [App\Http\Controllers\Student\SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('subscription/redeem', [App\Http\Controllers\Student\SubscriptionController::class, 'redeem'])->name('subscription.redeem');
        Route::post('subscription/subscribe', [App\Http\Controllers\Student\SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
        Route::post('subscription/auto-renew', [App\Http\Controllers\Student\SubscriptionController::class, 'toggleAutoRenew'])->name('subscription.toggleAutoRenew');
    });

Route::prefix('delegate')
    ->name('delegate.')
    ->middleware(['auth', 'role:delegate', 'status', 'subscribed'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Delegate\DashboardController::class, 'index'])->name('dashboard');

        // Profile & Password
        Route::get('profile/password', [App\Http\Controllers\Auth\PasswordController::class, 'edit'])->name('profile.password');
        Route::put('profile/password', [App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('profile.password.update');

        // Doctor Chat
        Route::post('doctor-chat/{conversation}/send', [App\Http\Controllers\Delegate\DoctorChatController::class, 'send'])->name('doctor-chat.send');

        // Shared Library Access for Delegate
        Route::get('library', [App\Http\Controllers\Student\LibraryController::class, 'index'])->name('library.index');
        Route::get('library/create', [App\Http\Controllers\Student\LibraryController::class, 'create'])->name('library.create');
        Route::post('library', [App\Http\Controllers\Student\LibraryController::class, 'store'])->name('library.store');
        Route::get('library/{resource}/download', [App\Http\Controllers\Student\LibraryController::class, 'incrementDownload'])->name('library.download');

        // Shared Subscription Routes
        Route::get('subscription', [App\Http\Controllers\Student\SubscriptionController::class, 'index'])->name('subscription.index');
        Route::post('subscription/redeem', [App\Http\Controllers\Student\SubscriptionController::class, 'redeem'])->name('subscription.redeem');
        Route::post('subscription/subscribe', [App\Http\Controllers\Student\SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
        Route::post('subscription/auto-renew', [App\Http\Controllers\Student\SubscriptionController::class, 'toggleAutoRenew'])->name('subscription.toggleAutoRenew');

        // Card Generation (Balance-based)
        Route::get('cards-generate', [App\Http\Controllers\Student\CardGenerationController::class, 'index'])->name('cards.generate.index');
        Route::post('cards-generate', [App\Http\Controllers\Student\CardGenerationController::class, 'generate'])->name('cards.generate.store');

        // Students Management
        Route::get('students/template', [App\Http\Controllers\Delegate\StudentController::class, 'downloadTemplate'])->name('students.template');
        Route::post('students/import', [App\Http\Controllers\Delegate\StudentController::class, 'import'])->name('students.import');
        Route::post('students/{student}/permissions', [App\Http\Controllers\Delegate\StudentController::class, 'updatePermissions'])->name('students.permissions');
        Route::resource('students', App\Http\Controllers\Delegate\StudentController::class);

        // Subjects & Schedule
        Route::resource('subjects', App\Http\Controllers\Delegate\SubjectController::class)->except(['create', 'show']);
        Route::resource('schedules', App\Http\Controllers\Delegate\ScheduleController::class)->except(['show']);

        // Notifications
        Route::get('notifications', [App\Http\Controllers\Delegate\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications', [App\Http\Controllers\Delegate\NotificationController::class, 'store'])->name('notifications.store');

        // Financial Ledger
        Route::get('ledger', [App\Http\Controllers\FinancialController::class, 'ledger'])->name('ledger');
        Route::get('ledger/export', [App\Http\Controllers\FinancialController::class, 'exportPdf'])->name('ledger.export');

        // Attendance
        Route::get('attendance', [App\Http\Controllers\Delegate\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/{subject}/create', [App\Http\Controllers\Delegate\AttendanceController::class, 'create'])->name('attendance.create');
        Route::get('attendance/{subject}/check', [App\Http\Controllers\Delegate\AttendanceController::class, 'check'])->name('attendance.check');
        Route::post('attendance/{subject}', [App\Http\Controllers\Delegate\AttendanceController::class, 'store'])->name('attendance.store');
        // Assignments
        Route::resource('assignments', App\Http\Controllers\Delegate\AssignmentController::class)->except(['create', 'edit', 'show']);

        // Announcements
        Route::resource('announcements', App\Http\Controllers\Delegate\AnnouncementController::class)->except(['create', 'edit', 'show']);
        Route::post('announcements/{announcement}/toggle-pin', [App\Http\Controllers\Delegate\AnnouncementController::class, 'togglePin'])->name('announcements.togglePin');

        // Reminders
        Route::resource('reminders', App\Http\Controllers\Delegate\ReminderController::class)->except(['create', 'edit', 'show']);

        // Exam Schedules
        Route::resource('exams', App\Http\Controllers\Delegate\ExamScheduleController::class);

        // Course Resources
        Route::get('resources', [App\Http\Controllers\Delegate\ResourceController::class, 'index'])->name('resources.index');
        Route::get('resources/search-library', [App\Http\Controllers\Delegate\ResourceController::class, 'searchLibrary'])->name('resources.search-library');
        Route::get('resources/create', [App\Http\Controllers\Delegate\ResourceController::class, 'create'])->name('resources.create');
        Route::post('resources', [App\Http\Controllers\Delegate\ResourceController::class, 'store'])->name('resources.store');
        Route::get('resources/{resource}/edit', [App\Http\Controllers\Delegate\ResourceController::class, 'edit'])->name('resources.edit');
        Route::put('resources/{resource}', [App\Http\Controllers\Delegate\ResourceController::class, 'update'])->name('resources.update');
        Route::post('resources/import', [App\Http\Controllers\Delegate\ResourceController::class, 'import'])->name('resources.import');
        Route::delete('resources/{resource}', [App\Http\Controllers\Delegate\ResourceController::class, 'destroy'])->name('resources.destroy');

        // Attendance Report Route (Correctly placed inside group)
        Route::get('attendance/{subject}/{date}/report', [App\Http\Controllers\Delegate\AttendanceController::class, 'showReport'])->name('attendance.report');

        // Grades - REMOVED (Doctor Only)
        // Route::get('grades', [App\Http\Controllers\Delegate\GradeController::class, 'index'])->name('grades.index');
        // Route::get('grades/create', [App\Http\Controllers\Delegate\GradeController::class, 'create'])->name('grades.create');
        // Route::get('grades/{subject}', [App\Http\Controllers\Delegate\GradeController::class, 'show'])->name('grades.show');
        // Route::post('grades/quick', [App\Http\Controllers\Delegate\GradeController::class, 'storeQuick'])->name('grades.storeQuick');
        // Route::post('grades/excel', [App\Http\Controllers\Delegate\GradeController::class, 'storeExcel'])->name('grades.storeExcel');
        // Route::get('grades/template/download', [App\Http\Controllers\Delegate\GradeController::class, 'downloadTemplate'])->name('grades.downloadTemplate');
        // Route::put('grades/{grade}', [App\Http\Controllers\Delegate\GradeController::class, 'update'])->name('grades.update');
        // Route::delete('grades/{grade}', [App\Http\Controllers\Delegate\GradeController::class, 'destroy'])->name('grades.destroy');

        // Messages (Chat System)
        Route::get('messages', [App\Http\Controllers\Delegate\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/create', [App\Http\Controllers\Delegate\MessageController::class, 'create'])->name('messages.create');
        Route::post('messages/start', [App\Http\Controllers\Delegate\MessageController::class, 'store'])->name('messages.store');
        Route::get('messages/{conversation}', [App\Http\Controllers\Delegate\MessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{conversation}/send', [App\Http\Controllers\Delegate\MessageController::class, 'send'])->name('messages.send');

        // Student Inquiries
        Route::get('inquiries', [App\Http\Controllers\Delegate\InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('inquiries/{inquiry}', [App\Http\Controllers\Delegate\InquiryController::class, 'show'])->name('inquiries.show');
        Route::post('inquiries/{inquiry}/forward', [App\Http\Controllers\Delegate\InquiryController::class, 'forward'])->name('inquiries.forward');
        Route::post('inquiries/{inquiry}/answer', [App\Http\Controllers\Delegate\InquiryController::class, 'answer'])->name('inquiries.answer');
        Route::post('inquiries/{inquiry}/close', [App\Http\Controllers\Delegate\InquiryController::class, 'close'])->name('inquiries.close');

        // Doctor Chat (Chat with Doctors)
        Route::get('doctor-chat', [App\Http\Controllers\Delegate\DoctorChatController::class, 'index'])->name('doctor-chat.index');
        Route::get('doctor-chat/create', [App\Http\Controllers\Delegate\DoctorChatController::class, 'create'])->name('doctor-chat.create');
        Route::post('doctor-chat/start', [App\Http\Controllers\Delegate\DoctorChatController::class, 'store'])->name('doctor-chat.store');
        Route::get('doctor-chat/{conversation}', [App\Http\Controllers\Delegate\DoctorChatController::class, 'show'])->name('doctor-chat.show');
        Route::post('doctor-chat/{conversation}/send', [App\Http\Controllers\Delegate\DoctorChatController::class, 'send'])->name('doctor-chat.send');

        // Clinical Access for Practical Delegates (Delegate Role)
        Route::middleware('clinical_delegate')->group(function () {
            Route::prefix('clinical')->name('clinical.')->group(function () {
                // Sub-delegations management
                Route::get('delegations', [App\Http\Controllers\Delegate\Clinical\SubDelegationController::class, 'index'])->name('delegations.index');
                Route::post('delegations', [App\Http\Controllers\Delegate\Clinical\SubDelegationController::class, 'store'])->name('delegations.store');
                Route::patch('delegations/{delegation}/revoke', [App\Http\Controllers\Delegate\Clinical\SubDelegationController::class, 'revoke'])->name('delegations.revoke');

                Route::get('cases/pending', [App\Http\Controllers\Delegate\Clinical\ClinicalCaseController::class, 'pending'])->name('cases.pending');
                Route::post('cases/{case}/approve', [App\Http\Controllers\Delegate\Clinical\ClinicalCaseController::class, 'approve'])->name('cases.approve');
                Route::post('cases/{case}/reject', [App\Http\Controllers\Delegate\Clinical\ClinicalCaseController::class, 'reject'])->name('cases.reject');

                Route::resource('cases', App\Http\Controllers\Delegate\Clinical\ClinicalCaseController::class);
            });
        });
    });

// Separate Clinical Access for Students who are Practical Delegates
Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'role:student', 'status', 'clinical_delegate'])
    ->group(function () {
        Route::prefix('clinical')->name('clinical.')->group(function () {
            Route::get('cases/pending', [App\Http\Controllers\Delegate\Clinical\ClinicalCaseController::class, 'pending'])->name('cases.pending');
            Route::resource('cases', App\Http\Controllers\Delegate\Clinical\ClinicalCaseController::class);
        });
    });
