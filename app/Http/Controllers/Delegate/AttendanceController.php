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
     * Display subjects list + attendance reports (two-tab view).
     */
    public function index()
    {
        $delegate = Auth::user();

        // Fetch subjects for the delegate's scope
        $subjects = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with('doctor')
            ->orderBy('name')
            ->get();

        $subjectIds = $subjects->pluck('id');

        // Get unique attendance sessions (grouped by subject, date, and lecture) for the Reports tab
        $sessions = Attendance::selectRaw('subject_id, date, lecture_id, count(*) as total_records')
            ->whereIn('subject_id', $subjectIds)
            ->groupBy('subject_id', 'date', 'lecture_id')
            ->with(['subject'])
            ->latest('date')
            ->paginate(10);

        return view('delegate.attendance.index', compact('sessions', 'subjects'));
    }

    /**
     * Show the form for creating/editing attendance.
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

        // Ensure delegate attendance is allowed by the doctor
        if (!$subject->allow_delegate_attendance) {
            abort(403, 'التحضير مغلق من قبل الدكتور المشرف على المادة.');
        }

        // Fetch Students in the same scope
        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
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
            $prefill['from_qr'] = true;
        }

        // If date is provided (either from QR or manually), fetch existing records
        if ($date) {
            // Try to find the specific lecture (by lecture_id from request, or by date)
            $lectureId = $request->input('lecture_id');
            $lecture = null;

            if ($lectureId) {
                $lecture = Lecture::where('id', $lectureId)->where('subject_id', $subjectId)->first();
            }
            if (!$lecture) {
                $lecture = Lecture::where('subject_id', $subjectId)->where('date', $date)->latest()->first();
            }

            // Fetch attendance records for this specific lecture or all on this date
            $attendanceQuery = Attendance::where('subject_id', $subjectId)->where('date', $date);
            if ($lecture) {
                $attendanceQuery->where('lecture_id', $lecture->id);
            }
            $attendanceRecords = $attendanceQuery->get()->keyBy('student_id');

            // Pre-fill lecture data
            if ($lecture && empty($prefill['from_qr'])) {
                $prefill['title'] = $prefill['title'] ?? $lecture->title;
                $prefill['lecture_number'] = $prefill['lecture_number'] ?? $lecture->lecture_number;
                $prefill['description'] = $lecture->description;
                $prefill['start_time'] = $lecture->start_time ? \Carbon\Carbon::parse($lecture->start_time)->format('H:i') : null;
                $prefill['end_time'] = $lecture->end_time ? \Carbon\Carbon::parse($lecture->end_time)->format('H:i') : null;
                $prefill['date'] = $date;
            }
        }

        return view('delegate.attendance.create', compact('subject', 'students', 'attendanceRecords', 'prefill'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $subjectId)
    {
        $delegate = Auth::user();

        $subject = Subject::findOrFail($subjectId);
        if ($subject->major_id != $delegate->major_id) abort(403);

        // Ensure delegate attendance is allowed by the doctor
        if (!$subject->allow_delegate_attendance) {
            abort(403, 'التحضير مغلق من قبل الدكتور المشرف على المادة.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'lecture_number' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late',
        ]);

        // Determine attendance method (QR session review = qr, otherwise manual)
        $attendanceMethod = $request->has('qr_session_id') ? 'qr' : 'manual';

        // Auto-create or update Lecture record
        // Unique by subject + date + title + lecture_number (allows multiple lectures per day)
        $lectureKey = [
            'subject_id' => $subject->id,
            'date' => $validated['date'],
            'title' => $validated['title'],
        ];
        if (!empty($validated['lecture_number'])) {
            $lectureKey['lecture_number'] = $validated['lecture_number'];
        }

        $lecture = Lecture::updateOrCreate(
            $lectureKey,
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
                    'lecture_id' => $lecture->id,
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

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
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
     * Check if attendance exists for a specific date, subject, title, and lecture number.
     */
    public function check(Request $request, $subjectId)
    {
        $date = $request->input('date');
        $title = $request->input('title');
        $lectureNumber = $request->input('lecture_number');

        // Check for existing lecture with same title and number on same date
        $lecture = Lecture::where('subject_id', $subjectId)
            ->where('date', $date)
            ->when($title, fn($q) => $q->where('title', $title))
            ->when($lectureNumber, fn($q) => $q->where('lecture_number', $lectureNumber))
            ->first();

        $exists = $lecture ? Attendance::where('subject_id', $subjectId)
            ->where('date', $date)
            ->where('lecture_id', $lecture->id)
            ->exists() : false;

        return response()->json([
            'exists' => $exists,
            'title' => $lecture ? $lecture->title : '',
            'lecture_number' => $lecture ? $lecture->lecture_number : '',
        ]);
    }
}
