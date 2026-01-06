<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\Announcement;
use App\Models\Reminder;
use App\Models\Academic\Assignment;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // 1. Fetch Subjects with Schedule and Deprivation Info
        $subjects = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->get();

        // 2. Announcements
        $announcements = Announcement::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->latest()
            ->take(5)
            ->get();

        // 3. Reminders
        $reminders = Reminder::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->where('notify_at', '<=', now())
            ->where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        // 4. Assignments Count
        $assignmentsCount = Assignment::whereIn('subject_id', $subjects->pluck('id'))
            ->where('due_date', '>=', now())
            ->count();

        // 5. Total Absences (Days)
        // Count total 'absent' records for this student across all subjects
        $totalAbsences = \App\Models\Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->count();

        // 6. Deprivation Warnings (Horman)
        // Check per subject if student is absences >= max_absences - 1
        $warnings = [];
        foreach ($subjects as $subject) {
            $subjectAbsences = \App\Models\Attendance::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('status', 'absent')
                ->count();

            // Logic: Warn if absences >= max - 1
            $threshold = $subject->max_absences ?? 5;
            $remaining = $threshold - $subjectAbsences;

            if ($remaining <= 1 && $remaining >= 0) {
                // Warning: 1 day left or reached limit (if we want to show 'banned' as separate status, we can)
                // User asked: Warn if they are absent 1 more time they get banned. 
                // If max is 5. If they have 4, remaining is 1. Next absence = 5 = Banned.
                $warnings[] = [
                    'subject' => $subject->name,
                    'absences' => $subjectAbsences,
                    'max' => $threshold,
                    'status' => $remaining == 0 ? 'banned' : 'warning'
                ];
            } elseif ($remaining < 0) {
                // Already banned
                $warnings[] = [
                    'subject' => $subject->name,
                    'absences' => $subjectAbsences,
                    'max' => $threshold,
                    'status' => 'banned'
                ];
            }
        }

        // 7. Excuse Deadline Warnings (Old Logic maintained)
        $excuseWarnings = \App\Models\Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->doesntHave('excuse')
            ->whereBetween('date', [now()->subDays(7), now()->subDays(4)])
            ->with('subject')
            ->get();

        return view('student.dashboard', compact('student', 'subjects', 'announcements', 'reminders', 'assignmentsCount', 'excuseWarnings', 'totalAbsences', 'warnings'));
    }
}
