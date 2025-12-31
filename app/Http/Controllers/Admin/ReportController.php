<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Academic\Subject;
use App\Models\Attendance;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * لوحة تحكم التقارير (Reports Hub).
     */
    public function index()
    {
        // نحتاج بيانات للقوائم المنسدلة في فلاتر التقارير
        $universities = \App\Models\Academic\University::with('colleges.majors.levels')->get();
        // نحتاج قائمة المواد لتقرير المادة
        $subjects = Subject::with(['level', 'major'])->get();

        return view('admin.reports.index', compact('universities', 'subjects'));
    }

    /**
     * تقرير الحضور التفصيلي لمادة (Subject Attendance Report).
     */
    public function subjectReport(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::with(['major', 'level', 'doctor'])->findOrFail($request->subject_id);

        // جلب جميع الطلاب المسجلين
        $students = User::where('role', UserRole::STUDENT)
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->orderBy('name')
            ->get();

        // إحصائيات الحضور
        $reportData = $students->map(function ($student) use ($subject) {
            $records = Attendance::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->get();

            $totalSessions = Attendance::where('subject_id', $subject->id)
                ->distinct('date')
                ->count();

            $present = $records->where('status', 'present')->count();
            $late = $records->where('status', 'late')->count();
            $excused = $records->where('status', 'excused')->count();
            $absent = $records->where('status', 'absent')->count();

            $absencePercentage = 0;
            if ($totalSessions > 0) {
                $absencePercentage = round(($absent / $totalSessions) * 100, 1);
            }

            return [
                'student' => $student,
                'present' => $present,
                'late' => $late,
                'excused' => $excused,
                'absent' => $absent,
                'total_sessions' => $totalSessions,
                'absence_percentage' => $absencePercentage,
            ];
        });

        return view('admin.reports.subject_report', compact('subject', 'reportData'));
    }

    /**
     * تقرير الحرمان والإنذارات (Threshold Report).
     */
    public function thresholdReport(Request $request)
    {
        $request->validate([
            'level_id' => 'required|exists:levels,id',
            'threshold' => 'required|numeric|min:0|max:100', // نسبة الغياب المسموحة قبل التنبيه
        ]);

        $level = \App\Models\Academic\Level::with('major.college')->findOrFail($request->level_id);
        $threshold = $request->threshold;

        // 1. جلب مواد هذا المستوى
        $subjects = Subject::whereHas('term', function ($q) use ($level) {
            $q->where('level_id', $level->id);
        })->get();

        // 2. جلب طلاب هذا المستوى
        $students = User::where('role', UserRole::STUDENT)
            ->where('level_id', $level->id)
            ->get();

        $alertData = [];

        // 3. فحص كل طالب في كل مادة
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                $totalSessions = Attendance::where('subject_id', $subject->id)
                    ->distinct('date')
                    ->count();

                if ($totalSessions == 0) continue;

                $absentCount = Attendance::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('status', 'absent')
                    ->count();

                $percentage = ($absentCount / $totalSessions) * 100;

                if ($percentage >= $threshold) {
                    $alertData[] = [
                        'student' => $student,
                        'subject' => $subject,
                        'absence_percentage' => round($percentage, 1),
                        'absent_count' => $absentCount,
                        'total_sessions' => $totalSessions,
                    ];
                }
            }
        }

        return view('admin.reports.threshold_report', compact('level', 'threshold', 'alertData'));
    }
}
