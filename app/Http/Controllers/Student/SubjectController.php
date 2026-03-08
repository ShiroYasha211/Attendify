<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\Academic\Assignment;
use App\Models\Academic\Level;
use App\Models\Attendance;
use App\Models\Setting;

class SubjectController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Load major to check for has_semesters
        $major = $student->major;

        // Load terms and their semesters for this specific student level
        $terms = \App\Models\Academic\Term::where('level_id', $student->level_id)
            ->with('semesters')
            ->get();

        $subjects = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->with(['doctor', 'term', 'semester'])
            ->get();

        return view('student.subjects.index', compact('subjects', 'terms', 'major'));
    }

    public function show($id)
    {
        $student = Auth::user();

        $subject = Subject::where('id', $id)
            ->where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->with('doctor')
            ->firstOrFail();

        // Fetch Assignments
        $assignments = Assignment::where('subject_id', $subject->id)
            ->latest()
            ->get();

        // Fetch Attendance Stats
        $attendanceRecords = Attendance::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->get();

        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $lateCount = $attendanceRecords->where('status', 'late')->count();
        $excusedCount = $attendanceRecords->where('status', 'excused')->count();

        $totalLectures = $attendanceRecords->count();
        $attendancePercentage = $totalLectures > 0 ? round(($presentCount / $totalLectures) * 100) : 0;

        // Fetch Grades for this student in this subject
        $grades = \App\Models\Grade::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->get();

        $continuousGrade = $grades->where('type', 'continuous')->first();
        $finalGrade = $grades->where('type', 'final')->first();

        // Calculate total percentage
        $totalGradePercentage = null;
        if ($continuousGrade || $finalGrade) {
            $cWeight = $continuousGrade ? ($continuousGrade->score / $continuousGrade->max_score) * 40 : 0;
            $fWeight = $finalGrade ? ($finalGrade->score / $finalGrade->max_score) * 60 : 0;
            $totalGradePercentage = round($cWeight + $fWeight, 1);
        }

        // ── Deprivation Warning Logic ──
        $maxAbsences = (int) Setting::get('default_max_absences', 3);
        $deprivationThreshold = (int) Setting::get('deprivation_threshold', 25);

        $absencePercent = $totalLectures > 0 ? round(($absentCount / $totalLectures) * 100) : 0;
        $warning = null;
        
        if ($absencePercent >= $deprivationThreshold) {
            $warning = 'danger'; // Deprivation zone
        } elseif ($absentCount >= $maxAbsences) {
            $warning = 'danger'; // Exceeded max allowed
        } elseif ($absentCount >= ($maxAbsences - 1)) {
            $warning = 'warning'; // One absence away from max
        }

        $subjectWarning = null;
        if ($warning) {
            $subjectWarning = [
                'absent_count' => $absentCount,
                'total_count' => $totalLectures,
                'absence_percent' => $absencePercent,
                'warning_level' => $warning,
                'max_absences' => $maxAbsences,
                'threshold' => $deprivationThreshold,
            ];
        }

        return view('student.subjects.show', compact(
            'subject',
            'assignments',
            'attendanceRecords',
            'presentCount',
            'absentCount',
            'lateCount',
            'attendancePercentage',
            'continuousGrade',
            'finalGrade',
            'totalGradePercentage',
            'subjectWarning'
        ));
    }
}
