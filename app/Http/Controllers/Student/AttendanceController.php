<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\Attendance;
use App\Models\Setting;

class AttendanceController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Fetch all attendance records for this student
        $attendances = Attendance::where('student_id', $student->id)
            ->with(['subject', 'excuse'])
            ->orderBy('date', 'desc')
            ->get();

        // Calculate Stats
        $totalLectures = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $lateCount = $attendances->where('status', 'late')->count();

        // Presence Percentage (Present + Late usually counts)
        $presencePercentage = $totalLectures > 0
            ? round((($presentCount + $lateCount) / $totalLectures) * 100, 1)
            : 0;

        // Group by Subject for the accordion/list view
        $attendanceBySubject = $attendances->groupBy('subject_id');

        // ── Deprivation Warning Logic ──
        $maxAbsences = (int) Setting::get('default_max_absences', 3);
        $deprivationThreshold = (int) Setting::get('deprivation_threshold', 25);

        $subjectWarnings = [];
        foreach ($attendanceBySubject as $subjectId => $records) {
            $subjectAbsent = $records->where('status', 'absent')->count();
            $subjectTotal  = $records->count();
            $absencePercent = $subjectTotal > 0 ? round(($subjectAbsent / $subjectTotal) * 100) : 0;

            $warning = null;
            if ($absencePercent >= $deprivationThreshold) {
                $warning = 'danger'; // Deprivation zone
            } elseif ($subjectAbsent >= $maxAbsences) {
                $warning = 'danger'; // Exceeded max allowed
            } elseif ($subjectAbsent >= ($maxAbsences - 1)) {
                $warning = 'warning'; // One absence away from max
            }

            $subjectWarnings[$subjectId] = [
                'absent_count' => $subjectAbsent,
                'total_count'  => $subjectTotal,
                'absence_percent' => $absencePercent,
                'warning_level' => $warning,
                'max_absences' => $maxAbsences,
                'threshold' => $deprivationThreshold,
            ];
        }

        return view('student.attendance.index', compact(
            'attendances',
            'attendanceBySubject',
            'totalLectures',
            'presentCount',
            'absentCount',
            'lateCount',
            'presencePercentage',
            'subjectWarnings'
        ));
    }
}
