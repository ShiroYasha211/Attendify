<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\Attendance; // Assuming this is where Attendance model is

class AttendanceController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Fetch all attendance records for this student
        // We need to bring subject details and excuse status
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

        return view('student.attendance.index', compact(
            'attendances',
            'attendanceBySubject',
            'totalLectures',
            'presentCount',
            'absentCount',
            'lateCount',
            'presencePercentage'
        ));
    }
}
