<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Academic\Subject;
use App\Models\Academic\Assignment;
use App\Models\ExamSchedule;
use App\Models\User;
use App\Enums\UserRole;

class DashboardController extends DelegateApiController
{
    /**
     * Get dashboard overview statistics for the delegate.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        // 1. Total Subjects in the delegate's batch
        $totalSubjects = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->count();

        // 2. Total Students in the delegate's batch
        $totalStudents = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->count();

        // 3. Active Assignments (Deadline >= today)
        $activeAssignments = Assignment::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->where('due_date', '>=', now()->toDateString())
            ->count();

        // 4. Upcoming Exams
        $upcomingExams = ExamSchedule::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->where('exam_date', '>=', now()->toDateString())
            ->count();

        return $this->success([
            'stats' => [
                'total_subjects' => $totalSubjects,
                'total_students' => $totalStudents,
                'active_assignments' => $activeAssignments,
                'upcoming_exams' => $upcomingExams,
            ]
        ], 'تم جلب الإحصائيات بنجاح');
    }
}
