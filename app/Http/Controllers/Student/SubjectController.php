<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\Academic\Assignment;
use App\Models\Attendance;

class SubjectController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        $subjects = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->with('doctor')
            ->get();

        return view('student.subjects.index', compact('subjects'));
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
        $excusedCount = $attendanceRecords->where('status', 'excused')->count(); // Keeping for historical data even if removed from form

        $totalLectures = $attendanceRecords->count(); // Or fetch from schedule/lectures table if we had one
        $attendancePercentage = $totalLectures > 0 ? round(($presentCount / $totalLectures) * 100) : 0;

        return view('student.subjects.show', compact('subject', 'assignments', 'attendanceRecords', 'presentCount', 'absentCount', 'lateCount', 'attendancePercentage'));
    }
}
