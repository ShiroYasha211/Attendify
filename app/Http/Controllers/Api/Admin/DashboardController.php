<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Academic\Subject;
use App\Models\Academic\Major;
use App\Models\Academic\University;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;

class DashboardController extends AdminApiController
{
    /**
     * GET /api/admin/dashboard
     */
    public function index()
    {
        $userStats = [
            'students_count' => User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])->count(),
            'doctors_count' => User::where('role', UserRole::DOCTOR)->count(),
            'delegates_count' => User::where('role', UserRole::DELEGATE)->count(),
            'pending_users' => User::where('status', 'inactive')->count(),
        ];

        $academicStats = [
            'universities_count' => University::count(),
            'majors_count' => Major::count(),
            'subjects_count' => Subject::count(),
        ];

        // Attendance stats
        $totalAttendance = Attendance::count();
        $statusCounts = Attendance::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $attendanceStats = [
            'total' => $totalAttendance,
            'present' => $statusCounts->get('present', 0),
            'absent' => $statusCounts->get('absent', 0),
            'late' => $statusCounts->get('late', 0),
            'excused' => $statusCounts->get('excused', 0),
            'attendance_rate' => $totalAttendance > 0
                ? round((($statusCounts->get('present', 0) + $statusCounts->get('late', 0) + $statusCounts->get('excused', 0)) / $totalAttendance) * 100, 1)
                : 0,
        ];

        // At-risk students
        $subjectsMaxAbsences = Subject::pluck('max_absences', 'id');
        $absences = Attendance::where('status', 'absent')
            ->select('student_id', 'subject_id', DB::raw('count(*) as absent_count'))
            ->groupBy('student_id', 'subject_id')
            ->get();

        $atRiskStudents = 0;
        foreach ($absences as $record) {
            $max = $subjectsMaxAbsences->get($record->subject_id) ?? 4;
            if ($record->absent_count >= $max) {
                $atRiskStudents++;
            }
        }

        // Today
        $todayAttendance = Attendance::whereDate('date', today())->count();
        $todayAbsent = Attendance::whereDate('date', today())->where('status', 'absent')->count();

        // 7-day trend
        $weeklyTrend = Attendance::select(
            DB::raw('DATE(date) as day'),
            DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
            DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent'),
            DB::raw('COUNT(*) as total')
        )
            ->where('date', '>=', now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('day')
            ->get();

        // Top absent subjects
        $topAbsentSubjects = Subject::select('subjects.id', 'subjects.name')
            ->selectRaw('(SELECT COUNT(*) FROM attendances WHERE attendances.subject_id = subjects.id AND attendances.status = "absent") as absent_count')
            ->having('absent_count', '>', 0)
            ->orderByDesc('absent_count')
            ->take(5)
            ->get();

        // Students per major
        $studentsPerMajor = Major::select('majors.id', 'majors.name')
            ->selectRaw('(SELECT COUNT(*) FROM users WHERE users.major_id = majors.id AND users.role = ?) as students_count', [UserRole::STUDENT->value])
            ->orderByDesc('students_count')
            ->take(5)
            ->get();

        return $this->success([
            'user_stats' => $userStats,
            'academic_stats' => $academicStats,
            'attendance_stats' => $attendanceStats,
            'at_risk_students' => $atRiskStudents,
            'today_attendance' => $todayAttendance,
            'today_absent' => $todayAbsent,
            'weekly_trend' => $weeklyTrend,
            'top_absent_subjects' => $topAbsentSubjects,
            'students_per_major' => $studentsPerMajor,
        ]);
    }
}
