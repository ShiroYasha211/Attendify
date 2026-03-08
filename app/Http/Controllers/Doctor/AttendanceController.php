<?php

namespace App\Http\Controllers\Doctor;

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
        $doctor = Auth::user();

        // Fetch subjects assigned to this doctor
        $subjects = Subject::where('doctor_id', $doctor->id)
            ->with(['major', 'level'])
            ->get();

        $subjectIds = $subjects->pluck('id');

        // Get attendance sessions for the Reports tab (grouped by lecture)
        $sessions = Attendance::selectRaw('subject_id, date, lecture_id, count(*) as total_records')
            ->whereIn('subject_id', $subjectIds)
            ->groupBy('subject_id', 'date', 'lecture_id')
            ->with(['subject'])
            ->latest('date')
            ->paginate(10);

        return view('doctor.attendance.index', compact('sessions', 'subjects'));
    }

    /**
     * Show the form for creating/editing attendance.
     */
    public function create(Request $request, Subject $subject)
    {
        $doctor = Auth::user();

        // Ensure subject belongs to doctor
        if ($subject->doctor_id !== $doctor->id) {
            abort(403, 'غير مصرح لك بالوصول لهذا المقرر.');
        }

        $date = $request->input('date') ?? date('Y-m-d');
        $qrSessionId = $request->input('qr_session_id');

        // Fetch Students in the same scope
        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->orderBy('name')
            ->get();

        $attendanceRecords = null;
        $prefill = [];

        // If coming from QR Session Review
        if ($qrSessionId) {
            $qrSession = \App\Models\QrAttendanceSession::where('id', $qrSessionId)
                ->where('delegate_id', $doctor->id)
                ->firstOrFail();

            $date = $qrSession->date->format('Y-m-d');
            $prefill['title'] = $qrSession->title;
            $prefill['lecture_number'] = $qrSession->lecture_number;
            $prefill['date'] = $date;
            $prefill['from_qr'] = true;
        }

        // If date is provided, fetch existing records
        if ($date) {
            // Try to find the specific lecture (by lecture_id from request, or by date)
            $lectureId = $request->input('lecture_id');
            $lecture = null;

            if ($lectureId) {
                $lecture = Lecture::where('id', $lectureId)->where('subject_id', $subject->id)->first();
            }
            if (!$lecture) {
                $lecture = Lecture::where('subject_id', $subject->id)->where('date', $date)->latest()->first();
            }

            // Fetch attendance records for this specific lecture or all on this date
            $attendanceQuery = Attendance::where('subject_id', $subject->id)->where('date', $date);
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

        return view('doctor.attendance.create', compact('subject', 'students', 'attendanceRecords', 'prefill'));
    }

    /**
     * Store attendance records.
     * Creates/updates a Lecture record and links attendance to it.
     */
    public function store(Request $request, Subject $subject)
    {
        $doctor = Auth::user();

        // Ensure subject belongs to doctor
        if ($subject->doctor_id !== $doctor->id) {
            abort(403, 'غير مصرح لك بالوصول لهذا المقرر.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'lecture_number' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,excused',
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
                    'recorded_by' => $doctor->id,
                    'attendance_method' => $attendanceMethod,
                ]
            );

            // Auto-add to Student Study Schedule
            \App\Models\Student\StudentScheduleItem::firstOrCreate(
                [
                    'user_id' => $studentId,
                    'referenceable_type' => Lecture::class,
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

        return redirect()->route('doctor.attendance.index')
            ->with('success', 'تم رصد الحضور وتم جدولة المحاضرة للطلاب بنجاح.');
    }

    /**
     * Show attendance report for a specific subject and date.
     */
    public function showReport($subjectId, $date)
    {
        $doctor = Auth::user();

        $subject = Subject::where('id', $subjectId)
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->orderBy('name')
            ->get();

        $attendanceRecords = Attendance::where('subject_id', $subject->id)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('doctor.attendance.report', compact('subject', 'students', 'attendanceRecords', 'date'));
    }

    /**
     * Toggle delegate attendance permission.
     */
    public function toggleDelegateAttendance(Request $request, Subject $subject)
    {
        $doctor = Auth::user();

        if ($subject->doctor_id !== $doctor->id) {
            abort(403, 'غير مصرح لك بالوصول لهذا المقرر.');
        }

        $subject->allow_delegate_attendance = !$subject->allow_delegate_attendance;
        $subject->save();

        $status = $subject->allow_delegate_attendance ? 'تم تفعيل' : 'تم إيقاف';
        return redirect()->back()->with('success', "{$status} صلاحية تحضير المندوب للمقرر بنجاح.");
    }
}
