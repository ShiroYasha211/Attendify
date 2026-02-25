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
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'status'])
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
        Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
        Route::patch('users/{user}/status', [App\Http\Controllers\Admin\UserController::class, 'updateStatus'])->name('users.status');
        Route::delete('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Bulk Actions
        Route::post('users/bulk-activate', [App\Http\Controllers\Admin\UserController::class, 'bulkActivate'])->name('users.bulk-activate');
        Route::post('users/bulk-deactivate', [App\Http\Controllers\Admin\UserController::class, 'bulkDeactivate'])->name('users.bulk-deactivate');
        Route::post('users/bulk-delete', [App\Http\Controllers\Admin\UserController::class, 'bulkDelete'])->name('users.bulk-delete');

        Route::resource('delegates', App\Http\Controllers\Admin\DelegateController::class);
        Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
        Route::resource('doctors', App\Http\Controllers\Admin\DoctorController::class);

        // Clinical Delegate Management
        Route::resource('clinical-delegates', App\Http\Controllers\Admin\ClinicalDelegateController::class)
            ->only(['index', 'store', 'destroy']);

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

        // Settings Routes
        Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    });

Route::prefix('doctor')
    ->name('doctor.')
    ->middleware(['auth', 'role:doctor', 'status'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Doctor\DashboardController::class, 'index'])->name('dashboard');

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

        // Clinical Training Hub
        Route::prefix('clinical')->name('clinical.')->group(function () {
            // Dashboard / Overview of Centers & Departments
            Route::get('/', [App\Http\Controllers\Doctor\ClinicalController::class, 'index'])->name('index');

            // Manage Training Centers
            Route::resource('training-centers', App\Http\Controllers\Doctor\Clinical\TrainingCenterController::class)->except(['show']);

            // Manage Clinical Departments
            Route::resource('departments', App\Http\Controllers\Doctor\Clinical\ClinicalDepartmentController::class)->except(['show']);

            // Manage Body Systems
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
    });

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'role:student', 'status'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('subjects', App\Http\Controllers\Student\SubjectController::class)->only(['index', 'show']);

        Route::get('assignments', [App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/{assignment}', [App\Http\Controllers\Student\AssignmentController::class, 'show'])->name('assignments.show');
        Route::post('assignments/{assignment}/submit', [App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');
        Route::get('attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');
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

        // Grades
        Route::get('grades', [App\Http\Controllers\Student\GradeController::class, 'index'])->name('grades.index');

        // Notifications
        Route::get('notifications', [App\Http\Controllers\Student\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/read', [App\Http\Controllers\Student\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('notifications/mark-all-read', [App\Http\Controllers\Student\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

        // Messages (Chat System)
        Route::get('messages', [App\Http\Controllers\Student\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/start', [App\Http\Controllers\Student\MessageController::class, 'start'])->name('messages.start');
        Route::get('messages/{conversation}', [App\Http\Controllers\Student\MessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{conversation}/send', [App\Http\Controllers\Student\MessageController::class, 'send'])->name('messages.send');

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
        Route::post('library/{resource}/download', [App\Http\Controllers\Student\LibraryController::class, 'incrementDownload'])->name('library.download');
    });

Route::prefix('delegate')
    ->name('delegate.')
    ->middleware(['auth', 'role:delegate', 'status'])
    ->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Delegate\DashboardController::class, 'index'])->name('dashboard');

        // Doctor Chat
        Route::post('doctor-chat/{conversation}/send', [App\Http\Controllers\Delegate\DoctorChatController::class, 'send'])->name('doctor-chat.send');

        // Shared Library Access for Delegate
        Route::get('library', [App\Http\Controllers\Student\LibraryController::class, 'index'])->name('library.index');
        Route::post('library/{resource}/download', [App\Http\Controllers\Student\LibraryController::class, 'incrementDownload'])->name('library.download');

        // Students Management
        Route::get('students/template', [App\Http\Controllers\Delegate\StudentController::class, 'downloadTemplate'])->name('students.template');
        Route::post('students/import', [App\Http\Controllers\Delegate\StudentController::class, 'import'])->name('students.import');
        Route::resource('students', App\Http\Controllers\Delegate\StudentController::class);

        // Subjects & Schedule
        Route::resource('subjects', App\Http\Controllers\Delegate\SubjectController::class)->except(['create', 'show']);
        Route::resource('schedules', App\Http\Controllers\Delegate\ScheduleController::class)->except(['show']);

        // Notifications
        Route::get('notifications', [App\Http\Controllers\Delegate\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications', [App\Http\Controllers\Delegate\NotificationController::class, 'store'])->name('notifications.store');

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
    });
