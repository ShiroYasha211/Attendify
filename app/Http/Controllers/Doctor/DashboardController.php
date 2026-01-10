<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Excuse;
use App\Models\Inquiry;
use App\Models\DoctorConversation;
use App\Models\Grade;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $doctor = Auth::user();

        // Fetch subjects assigned to this doctor
        $subjects = Subject::where('doctor_id', $doctor->id)
            ->with(['major', 'level', 'term', 'major.college.university'])
            ->get();

        $subjectIds = $subjects->pluck('id');

        // Append student count and attendance stats to each subject
        $subjects->each(function ($subject) {
            $subject->students_count = User::where('role', UserRole::STUDENT)
                ->where('major_id', $subject->major_id)
                ->where('level_id', $subject->level_id)
                ->count();

            // Calculate attendance rate
            $totalAttendances = Attendance::where('subject_id', $subject->id)->count();
            $presentAttendances = Attendance::where('subject_id', $subject->id)
                ->where('status', 'present')
                ->count();
            $subject->attendance_rate = $totalAttendances > 0
                ? round(($presentAttendances / $totalAttendances) * 100)
                : 0;
        });

        // Calculate total students across all doctor's subjects
        $studentsCount = 0;
        $uniqueStudentIds = [];
        foreach ($subjects as $subject) {
            $studentIds = User::where('role', UserRole::STUDENT)
                ->where('major_id', $subject->major_id)
                ->where('level_id', $subject->level_id)
                ->pluck('id')
                ->toArray();
            $uniqueStudentIds = array_merge($uniqueStudentIds, $studentIds);
        }
        $studentsCount = count(array_unique($uniqueStudentIds));

        // Calculate pending excuses
        $pendingExcusesCount = Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
            $q->whereIn('subject_id', $subjectIds);
        })->where('status', 'pending')->count();

        // Pending inquiries count
        $pendingInquiriesCount = Inquiry::whereIn('subject_id', $subjectIds)
            ->where('status', 'forwarded')
            ->count();

        // Unread messages count
        $unreadMessagesCount = DoctorConversation::where('doctor_id', $doctor->id)
            ->get()
            ->sum(function ($conv) use ($doctor) {
                return $conv->unreadCountFor($doctor->id);
            });

        // Recent activities (last 5)
        $recentExcuses = Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
            $q->whereIn('subject_id', $subjectIds);
        })->with(['student', 'attendance.subject'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($excuse) {
                return [
                    'type' => 'excuse',
                    'title' => 'عذر جديد من ' . ($excuse->student->name ?? 'طالب'),
                    'subtitle' => $excuse->attendance->subject->name ?? '',
                    'status' => $excuse->status,
                    'date' => $excuse->created_at,
                ];
            });

        $recentInquiries = Inquiry::whereIn('subject_id', $subjectIds)
            ->with(['student', 'subject'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($inquiry) {
                return [
                    'type' => 'inquiry',
                    'title' => 'استفسار من ' . ($inquiry->student->name ?? 'طالب'),
                    'subtitle' => $inquiry->subject->name ?? '',
                    'status' => $inquiry->status,
                    'date' => $inquiry->created_at,
                ];
            });

        // Merge and sort activities
        $recentActivities = $recentExcuses->concat($recentInquiries)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        // Attendance chart data (last 7 days)
        $attendanceChartData = [];
        $attendanceLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $attendanceLabels[] = $date->format('m/d');

            $dayStats = Attendance::whereIn('subject_id', $subjectIds)
                ->whereDate('date', $date)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $attendanceChartData['present'][] = $dayStats['present'] ?? 0;
            $attendanceChartData['absent'][] = $dayStats['absent'] ?? 0;
        }

        // Grades stats
        $gradesEntered = Grade::whereIn('subject_id', $subjectIds)->count();

        return view('doctor.dashboard', compact(
            'doctor',
            'subjects',
            'studentsCount',
            'pendingExcusesCount',
            'pendingInquiriesCount',
            'unreadMessagesCount',
            'recentActivities',
            'attendanceChartData',
            'attendanceLabels',
            'gradesEntered'
        ));
    }

    public function showSubjectReport(Subject $subject)
    {
        // Ensure the subject belongs to the authenticated doctor
        if ($subject->doctor_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بالوصول لهذا المقرر.');
        }

        // Fetch students for this subject (same logic: match major/level)
        $students = User::where('role', UserRole::STUDENT)
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with(['attendances' => function ($query) use ($subject) {
                $query->where('subject_id', $subject->id);
            }])
            ->get();

        return view('doctor.reports.subject_report', compact('subject', 'students'));
    }
}
