<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Academic\Subject;
use App\Models\Attendance;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $subjects = Subject::where('doctor_id', Auth::id())
            ->with(['major', 'level', 'term'])
            ->get();

        // Add statistics to each subject
        $subjects->each(function ($subject) {
            // Students count
            $subject->students_count = User::where('role', UserRole::STUDENT)
                ->where('major_id', $subject->major_id)
                ->where('level_id', $subject->level_id)
                ->count();

            // Attendance stats
            $totalAttendances = Attendance::where('subject_id', $subject->id)->count();
            $presentCount = Attendance::where('subject_id', $subject->id)
                ->where('status', 'present')
                ->count();
            $absentCount = Attendance::where('subject_id', $subject->id)
                ->where('status', 'absent')
                ->count();
            $excusedCount = Attendance::where('subject_id', $subject->id)
                ->where('status', 'excused')
                ->count();

            $subject->total_attendances = $totalAttendances;
            $subject->present_count = $presentCount;
            $subject->absent_count = $absentCount;
            $subject->excused_count = $excusedCount;
            $subject->attendance_rate = $totalAttendances > 0
                ? round(($presentCount / $totalAttendances) * 100)
                : 0;

            // Lectures count (unique dates)
            $subject->lectures_count = Attendance::where('subject_id', $subject->id)
                ->distinct('date')
                ->count('date');
        });

        return view('doctor.reports.index', compact('subjects'));
    }
}
