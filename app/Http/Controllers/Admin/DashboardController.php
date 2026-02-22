<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Academic\Subject;
use App\Models\Academic\University;
use App\Models\Academic\Major;

class DashboardController extends Controller
{
    /**
     * عرض لوحة تحكم المدير المحسّنة.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== UserRole::ADMIN) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'ليس لديك صلاحية الوصول إلى لوحة الإدارة.']);
        }

        // 1. إحصائيات المستخدمين
        $userStats = [
            'students_count' => User::where('role', UserRole::STUDENT)->count(),
            'doctors_count' => User::where('role', UserRole::DOCTOR)->count(),
            'delegates_count' => User::where('role', UserRole::DELEGATE)->count(),
            'pending_users' => User::where('status', 'inactive')->count(),
        ];

        // 2. إحصائيات أكاديمية
        $academicStats = [
            'universities_count' => University::count(),
            'majors_count' => Major::count(),
            'subjects_count' => Subject::count(),
        ];

        // 3. إحصائيات الحضور
        $totalAttendance = Attendance::count();
        $presentCount = Attendance::where('status', 'present')->count();
        $absentCount = Attendance::where('status', 'absent')->count();
        $lateCount = Attendance::where('status', 'late')->count();
        $excusedCount = Attendance::where('status', 'excused')->count();

        $attendanceStats = [
            'total' => $totalAttendance,
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'excused' => $excusedCount,
            'attendance_rate' => $totalAttendance > 0
                ? round((($presentCount + $lateCount + $excusedCount) / $totalAttendance) * 100, 1)
                : 0,
        ];

        // 4. الطلاب المعرضون للحرمان (أكثر من 75% غياب في أي مادة)
        $atRiskStudents = $this->getAtRiskStudents();

        // 5. أكثر المواد غياباً
        $topAbsentSubjects = $this->getTopAbsentSubjects(5);

        // 6. توزيع الطلاب حسب التخصصات
        $studentsPerMajor = Major::withCount(['levels' => function ($query) {
            // Count students through levels
        }])
            ->select('majors.*')
            ->selectRaw('(SELECT COUNT(*) FROM users WHERE users.major_id = majors.id AND users.role = ?) as students_count', [UserRole::STUDENT->value])
            ->orderByDesc('students_count')
            ->take(5)
            ->get();

        // 7. آخر سجلات الحضور
        $latestAttendance = Attendance::with(['student', 'subject', 'recorder'])
            ->latest('date')
            ->take(5)
            ->get();

        // 8. آخر المستخدمين المسجلين
        $latestUsers = User::latest()
            ->take(5)
            ->get();

        // 9. حضور اليوم
        $todayAttendance = Attendance::whereDate('date', today())->count();
        $todayAbsent = Attendance::whereDate('date', today())->where('status', 'absent')->count();

        return view('admin.dashboard', compact(
            'user',
            'userStats',
            'academicStats',
            'attendanceStats',
            'atRiskStudents',
            'topAbsentSubjects',
            'studentsPerMajor',
            'latestAttendance',
            'latestUsers',
            'todayAttendance',
            'todayAbsent'
        ));
    }

    /**
     * Get students at risk of deprivation (high absence rate).
     */
    private function getAtRiskStudents(): int
    {
        // جلب الإعداد الخاص لكل مادة مرة واحدة
        $subjectsMaxAbsences = Subject::pluck('max_absences', 'id');

        // جلب إحصائيات الغياب المجمعة لكل طالب ولكل مادة في استعلام واحد
        $absences = Attendance::where('status', 'absent')
            ->select('student_id', 'subject_id', DB::raw('count(*) as absent_count'))
            ->groupBy('student_id', 'subject_id')
            ->get();

        $atRiskCount = 0;

        foreach ($absences as $record) {
            $maxAbsences = $subjectsMaxAbsences->get($record->subject_id) ?? 4;

            if ($record->absent_count >= $maxAbsences) {
                $atRiskCount++;
            }
        }

        return $atRiskCount;
    }

    /**
     * Get subjects with highest absence rates.
     */
    private function getTopAbsentSubjects(int $limit = 5)
    {
        return Subject::select('subjects.*')
            ->selectRaw('(SELECT COUNT(*) FROM attendances WHERE attendances.subject_id = subjects.id AND attendances.status = "absent") as absent_count')
            ->selectRaw('(SELECT COUNT(*) FROM attendances WHERE attendances.subject_id = subjects.id) as total_count')
            ->having('total_count', '>', 0)
            ->orderByDesc('absent_count')
            ->take($limit)
            ->get()
            ->map(function ($subject) {
                $subject->absence_rate = $subject->total_count > 0
                    ? round(($subject->absent_count / $subject->total_count) * 100, 1)
                    : 0;
                return $subject;
            });
    }

    /**
     * عرض صفحة عن المطور.
     */
    public function about()
    {
        return view('admin.about');
    }
}
