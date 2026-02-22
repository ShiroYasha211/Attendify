<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Academic\Lecture;
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

        // Fetch subjects for the "Start QR Attendance" modal
        $subjects = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->orderBy('name')
            ->get();

        return view('delegate.attendance.index', compact('sessions', 'subjects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $subjectId)
    {
        $delegate = Auth::user();
        $date = $request->input('date');
        $qrSessionId = $request->input('qr_session_id');

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

        $attendanceRecords = null;
        $prefill = [];

        // If coming from QR Session Review
        if ($qrSessionId) {
            $qrSession = \App\Models\QrAttendanceSession::where('id', $qrSessionId)
                ->where('delegate_id', $delegate->id)
                ->firstOrFail();

            $date = $qrSession->date->format('Y-m-d');
            $prefill['title'] = $qrSession->title;
            $prefill['lecture_number'] = $qrSession->lecture_number;
            $prefill['date'] = $date;
        }

        // If date is provided (either from QR or manually), fetch existing records
        if ($date) {
            $attendanceRecords = Attendance::where('subject_id', $subjectId)
                ->where('date', $date)
                ->get()
                ->keyBy('student_id');
        }

        return view('delegate.attendance.create', compact('subject', 'students', 'attendanceRecords', 'prefill'));
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
            'title' => 'required|string|max:255',
            'lecture_number' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late',
        ]);

        // Determine attendance method (QR session review = qr, otherwise manual)
        $attendanceMethod = $request->has('qr_session_id') ? 'qr' : 'manual';

        // Auto-create or update Lecture record
        $lecture = Lecture::updateOrCreate(
            [
                'subject_id' => $subject->id,
                'date' => $validated['date'],
            ],
            [
                'title' => $validated['title'],
                'lecture_number' => $validated['lecture_number'],
                'description' => $validated['description'] ?? null,
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
            ]
        );

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
                    'attendance_method' => $attendanceMethod,
                ]
            );

            // Auto-add to Student Study Schedule
            \App\Models\Student\StudentScheduleItem::firstOrCreate(
                [
                    'user_id' => $studentId,
                    'referenceable_type' => \App\Models\Academic\Lecture::class,
                    'referenceable_id' => $lecture->id,
                ],
                [
                    'title' => $validated['title'],
                    'scheduled_date' => $validated['date'],
                    'item_type' => 'study',
                    'priority' => 'medium',
                    'status' => 'pending',
                ]
            );
        }

        // Notify the Doctor about the lecture report
        if ($subject->doctor_id) {
            $presentCount = collect($validated['attendance'])->filter(fn($s) => $s === 'present')->count();
            $absentCount  = collect($validated['attendance'])->filter(fn($s) => $s === 'absent')->count();
            $lateCount    = collect($validated['attendance'])->filter(fn($s) => $s === 'late')->count();
            $totalStudents = count($validated['attendance']);

            \App\Models\StudentNotification::create([
                'user_id' => $subject->doctor_id,
                'type'    => 'lecture_report',
                'title'   => "📋 تقرير محاضرة: {$subject->name}",
                'message' => "تم تسجيل الحضور لمحاضرة \"{$validated['title']}\" بتاريخ {$validated['date']}.\n"
                    . "إجمالي الطلاب: {$totalStudents} | حضور: {$presentCount} | غياب: {$absentCount} | تأخير: {$lateCount}",
                'data'    => [
                    'subject_id'  => $subject->id,
                    'lecture_id'  => $lecture->id,
                    'date'        => $validated['date'],
                ],
            ]);
        }

        return redirect()->route('delegate.attendance.index')
            ->with('success', 'تم رصد الحضور وتم جدولة المحاضرة للطلاب بنجاح.');
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

    /**
     * Check if attendance exists for a specific date and subject.
     */
    public function check(Request $request, $subjectId)
    {
        $date = $request->input('date');
        // $subjectId is passed as the second argument from the route parameter specificed in controller method signature
        // The signature is check(Request $request, $subjectId)

        $exists = Attendance::where('subject_id', $subjectId)->where('date', $date)->exists();
        $lecture = Lecture::where('subject_id', $subjectId)->where('date', $date)->first();

        return response()->json([
            'exists' => $exists,
            'title' => $lecture ? $lecture->title : '',
            'lecture_number' => $lecture ? $lecture->lecture_number : '',
        ]);
    }
}
