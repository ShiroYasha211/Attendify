<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Models\Attendance;
use App\Enums\UserRole;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $delegate = Auth::user();

        // Get unique attendance sessions (grouped by subject and date)
        // Since we store individual records, we can group by date & subject
        $sessions = Attendance::selectRaw('subject_id, date, count(*) as total_records')
            ->where('recorded_by', $delegate->id)
            ->groupBy('subject_id', 'date')
            ->with(['subject'])
            ->latest('date')
            ->paginate(10);

        return view('delegate.attendance.index', compact('sessions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($subjectId)
    {
        $delegate = Auth::user();

        // Fetch Subject and ensure it belongs to delegate's scope
        $subject = Subject::where('id', $subjectId)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->firstOrFail();

        // Fetch Students in the same scope
        $students = User::where('role', UserRole::STUDENT)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->orderBy('name')
            ->get();

        return view('delegate.attendance.create', compact('subject', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $subjectId)
    {
        $delegate = Auth::user();

        $subject = Subject::findOrFail($subjectId); // Scope check already done in create or middleware usually, but good to re-verify if strict.
        if ($subject->major_id != $delegate->major_id) abort(403);

        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late',
        ]);

        foreach ($validated['attendance'] as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $subject->id,
                    'date' => $validated['date'],
                ],
                [
                    'status' => $status,
                    'recorded_by' => $delegate->id,
                ]
            );
        }

        return redirect()->route('delegate.attendance.index')
            ->with('success', 'تم رصد الحضور لهذا اليوم بنجاح.');
    }

    public function showReport($subjectId, $date)
    {
        $delegate = Auth::user();

        $subject = Subject::where('id', $subjectId)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with('doctor')
            ->firstOrFail();

        $students = User::where('role', UserRole::STUDENT)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->orderBy('name')
            ->get();

        // Get attendance records for this specific date and subject
        $attendanceRecords = Attendance::where('subject_id', $subject->id)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('delegate.attendance.report', compact('subject', 'students', 'attendanceRecords', 'date'));
    }
}
