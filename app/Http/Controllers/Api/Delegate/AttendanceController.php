<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Attendance;
use App\Models\Academic\Subject;
use App\Models\Academic\Lecture;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Student\StudentScheduleItem;
use App\Support\ExcuseWorkflow;

class AttendanceController extends DelegateApiController
{
    /**
     * Display a listing of attendance sessions for the delegate's batch.
     * Groups by lecture_id to support multiple lectures per day.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        // 1. Get Subjects in Batch (to know which subjects are open for attendance)
        $subjects = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with('doctor:id,name')
            ->orderBy('name')
            ->get();

        $subjectIds = $subjects->pluck('id');

        // 2. Get Grouped Attendance Sessions (History)
        $sessions = Attendance::selectRaw('subject_id, date, lecture_id, recorded_by, attendance_method, count(*) as total_records')
            ->whereIn('subject_id', $subjectIds)
            ->with(['subject:id,name,code', 'recorder:id,name,role', 'lecture:id,title,lecture_number,start_time,end_time,lecture_type'])
            ->groupBy('subject_id', 'date', 'lecture_id', 'recorded_by', 'attendance_method')
            ->orderBy('date', 'desc')
            ->get();

        return $this->success([
            'subjects' => $subjects,
            'sessions' => $sessions
        ], 'طھظ… ط¬ظ„ط¨ ط¨ظٹط§ظ†ط§طھ ط§ظ„ط­ط¶ظˆط± ط¨ظ†ط¬ط§ط­');
    }

    /**
     * Store new attendance records.
     * Creates/updates a Lecture record and links attendance to it.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();
        $request->merge([
            'date' => $request->input('date', $request->input('lecture_date')),
        ]);

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'lecture_type' => 'nullable|in:official,special',
            'lecture_number' => 'nullable|string|max:50',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'gender_filter' => 'nullable|in:all,male,female',
            'students' => 'nullable|array|min:1',
            'students.*.id' => 'required_with:students|exists:users,id',
            'students.*.status' => 'required_with:students|in:' . implode(',', ExcuseWorkflow::editableAttendanceStatuses()),
            'attendance' => 'nullable|array|min:1',
            'attendance.*' => 'required_with:attendance|in:' . implode(',', ExcuseWorkflow::editableAttendanceStatuses()),
        ]);

        if ($validator->fails()) {
            return $this->error('ط¨ظٹط§ظ†ط§طھ ط؛ظٹط± طµط§ظ„ط­ط©', 422, $validator->errors());
        }

        $subject = Subject::where('id', $request->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('ط§ظ„ظ…ط§ط¯ط© ط؛ظٹط± ظ…ظˆط¬ظˆط¯ط© ط£ظˆ ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ', 403);
        }

        if (!$subject->allow_delegate_attendance) {
            return $this->error('ط§ظ„طھط­ط¶ظٹط± ظ…ط؛ظ„ظ‚ ظ…ظ† ظ‚ط¨ظ„ ط§ظ„ط¯ظƒطھظˆط± ط§ظ„ظ…ط´ط±ظپ ط¹ظ„ظ‰ ط§ظ„ظ…ط§ط¯ط©.', 403);
        }

        $students = collect($request->input('students', []));
        if ($students->isEmpty()) {
            $students = collect($request->input('attendance', []))
                ->map(fn ($status, $studentId) => ['id' => (int) $studentId, 'status' => $status])
                ->values();
        }

        if ($students->isEmpty()) {
            return $this->error('Attendance payload is required.', 422);
        }

        $genderFilter = in_array($request->input('gender_filter'), ['male', 'female'], true)
            ? $request->input('gender_filter')
            : 'all';

        $allowedStudentIds = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->when($genderFilter !== 'all', fn ($query) => $query->where('gender', $genderFilter))
            ->pluck('id');

        if ($students->pluck('id')->diff($allowedStudentIds)->isNotEmpty()) {
            return $this->error('One or more students are outside the delegate scope or current gender filter.', 422);
        }

        $lectureKey = [
            'subject_id' => $subject->id,
            'date' => $request->date,
            'title' => $request->title,
            'lecture_type' => $request->input('lecture_type', 'official'),
        ];
        if (!empty($request->lecture_number)) {
            $lectureKey['lecture_number'] = $request->lecture_number;
        }

        try {
            DB::beginTransaction();

            $lecture = Lecture::updateOrCreate(
                $lectureKey,
                [
                    'title' => $request->title,
                    'lecture_type' => $request->input('lecture_type', 'official'),
                    'lecture_number' => $request->lecture_number,
                    'description' => $request->description,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                ]
            );

            $attendanceMethod = $request->has('qr_session_id') ? 'qr' : 'manual';

            foreach ($students as $student) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $student['id'],
                        'subject_id' => $subject->id,
                        'date' => $request->date,
                        'lecture_id' => $lecture->id,
                    ],
                    [
                        'status' => $student['status'],
                        'recorded_by' => $delegate->id,
                        'attendance_method' => $attendanceMethod,
                    ]
                );

                StudentScheduleItem::firstOrCreate(
                    [
                        'user_id' => $student['id'],
                        'referenceable_type' => Lecture::class,
                        'referenceable_id' => $lecture->id,
                    ],
                    [
                        'title' => $request->title,
                        'scheduled_date' => $request->date,
                        'item_type' => 'study',
                        'priority' => 'medium',
                        'status' => 'pending',
                    ]
                );
            }

            if ($subject->doctor_id) {
                $presentCount = $students->where('status', 'present')->count();
                $absentCount = $students->where('status', 'absent')->count();
                $lateCount = $students->where('status', 'late')->count();
                $totalStudents = $students->count();

                \App\Models\StudentNotification::create([
                    'user_id' => $subject->doctor_id,
                    'type' => 'lecture_report',
                    'title' => "ًں“‹ طھظ‚ط±ظٹط± ظ…ط­ط§ط¶ط±ط©: {$subject->name}",
                    'message' => "طھظ… طھط³ط¬ظٹظ„ ط§ظ„ط­ط¶ظˆط± ظ„ظ…ط­ط§ط¶ط±ط© \"{$request->title}\" ط¨طھط§ط±ظٹط® {$request->date}.\n"
                        . "ط¥ط¬ظ…ط§ظ„ظٹ ط§ظ„ط·ظ„ط§ط¨: {$totalStudents} | ط­ط¶ظˆط±: {$presentCount} | ط؛ظٹط§ط¨: {$absentCount} | طھط£ط®ظٹط±: {$lateCount}",
                    'data' => [
                        'subject_id' => $subject->id,
                        'lecture_id' => $lecture->id,
                        'date' => $request->date,
                    ],
                ]);
            }

            DB::commit();
            return $this->success([
                'lecture_id' => $lecture->id,
                'lecture_type' => $lecture->lecture_type,
                'merge_mode' => true,
                'gender_filter' => $genderFilter,
                'updated_students_count' => $students->count(),
            ], 'طھظ… ط­ظپط¸ ط³ط¬ظ„ ط§ظ„ط­ط¶ظˆط± ظˆط§ظ„ط؛ظٹط§ط¨ ط¨ظ†ط¬ط§ط­', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('ط­ط¯ط« ط®ط·ط£ ط£ط«ظ†ط§ط، ط±طµط¯ ط§ظ„ط؛ظٹط§ط¨: ' . $e->getMessage(), 500);
        }
    }

    public function create(Request $request, Subject $subject)
    {
        $delegate = $request->user();

        $subject = Subject::where('id', $subject->id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with('doctor:id,name')
            ->first();

        if (!$subject) {
            return $this->error('ط§ظ„ظ…ط§ط¯ط© ط؛ظٹط± ظ…ظˆط¬ظˆط¯ط© ط£ظˆ ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ', 403);
        }

        if (!$subject->allow_delegate_attendance) {
            return $this->error('ط§ظ„طھط­ط¶ظٹط± ظ…ط؛ظ„ظ‚ ظ…ظ† ظ‚ط¨ظ„ ط§ظ„ط¯ظƒطھظˆط± ط§ظ„ظ…ط´ط±ظپ ط¹ظ„ظ‰ ط§ظ„ظ…ط§ط¯ط©.', 403);
        }

        $date = $request->input('date') ?? now()->format('Y-m-d');
        $lectureId = $request->input('lecture_id');
        $qrSessionId = $request->input('qr_session_id');
        $genderFilter = in_array($request->input('gender_filter'), ['male', 'female'], true)
            ? $request->input('gender_filter')
            : 'all';

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->when($genderFilter !== 'all', fn ($query) => $query->where('gender', $genderFilter))
            ->orderBy('name')
            ->get(['id', 'name', 'student_number', 'gender']);

        $prefill = ['date' => $date];

        if ($qrSessionId) {
            $qrSession = \App\Models\QrAttendanceSession::where('id', $qrSessionId)
                ->where('delegate_id', $delegate->id)
                ->firstOrFail();

            $date = $qrSession->date->format('Y-m-d');
            $prefill['date'] = $date;
            $prefill['title'] = $qrSession->title;
            $prefill['lecture_number'] = $qrSession->lecture_number;
            $prefill['from_qr'] = true;
        }

        $lecture = null;
        if ($lectureId) {
            $lecture = Lecture::where('id', $lectureId)->where('subject_id', $subject->id)->first();
        }
        if (!$lecture) {
            $lecture = Lecture::where('subject_id', $subject->id)->where('date', $date)->latest()->first();
        }

        $attendanceQuery = Attendance::where('subject_id', $subject->id)->where('date', $date);
        if ($lecture) {
            $attendanceQuery->where('lecture_id', $lecture->id);
            $prefill['title'] = $prefill['title'] ?? $lecture->title;
            $prefill['lecture_number'] = $prefill['lecture_number'] ?? $lecture->lecture_number;
            $prefill['description'] = $lecture->description;
            $prefill['lecture_type'] = $lecture->lecture_type;
            $prefill['start_time'] = $lecture->start_time;
            $prefill['end_time'] = $lecture->end_time;
        }

        return $this->success([
            'subject' => $subject->only(['id', 'name', 'allow_delegate_attendance']),
            'students' => $students,
            'attendance_records' => $attendanceQuery->get()->keyBy('student_id'),
            'prefill' => $prefill,
            'filters' => [
                'gender_filter' => $genderFilter,
                'available_gender_filters' => ['all', 'male', 'female'],
            ],
            'workflow' => [
                'merge_mode' => true,
                'description' => 'Re-saving the same lecture updates only submitted students and keeps previously recorded students unchanged.',
            ],
        ], 'طھظ… ط¬ظ„ط¨ ظ†ظ…ظˆط°ط¬ ط§ظ„ط­ط¶ظˆط± ط¨ظ†ط¬ط§ط­');
    }
    /**
     * View details of a specific attendance session by lecture ID.
     */
    public function show(Request $request, $lectureId)
    {
        $delegate = $request->user();

        $lecture = Lecture::with('subject')->findOrFail($lectureId);

        $subject = Subject::where('id', $lecture->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('ط§ظ„ظ…ط§ط¯ط© ط؛ظٹط± ظ…ظˆط¬ظˆط¯ط© ط£ظˆ ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ', 403);
        }

        $records = Attendance::where('lecture_id', $lectureId)
            ->with(['student:id,name,university_id,student_number,gender'])
            ->get();

        if ($records->isEmpty()) {
            return $this->error('ظ„ط§ طھظˆط¬ط¯ ط³ط¬ظ„ط§طھ ظ„ظ‡ط°ظ‡ ط§ظ„ط¬ظ„ط³ط©', 404);
        }

        return $this->success([
            'subject' => $subject->only('id', 'name'),
            'lecture' => [
                'id'             => $lecture->id,
                'title'          => $lecture->title,
                'lecture_number' => $lecture->lecture_number,
                'date'           => $lecture->date,
                'start_time'     => $lecture->start_time,
                'end_time'       => $lecture->end_time,
            ],
            'records' => $records
        ], 'طھظ… ط¬ظ„ط¨ طھظپط§طµظٹظ„ ط¬ظ„ط³ط© ط§ظ„ط­ط¶ظˆط± ط¨ظ†ط¬ط§ط­');
    }


    public function report(Request $request, int $subjectId, string $date)
    {
        $delegate = $request->user();

        $subject = Subject::where('id', $subjectId)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with('doctor:id,name')
            ->firstOrFail();

        $genderFilter = in_array($request->input('gender_filter'), ['male', 'female'], true)
            ? $request->input('gender_filter')
            : 'all';

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->when($genderFilter !== 'all', fn ($query) => $query->where('gender', $genderFilter))
            ->orderBy('name')
            ->get(['id', 'name', 'student_number', 'gender']);

        $lecture = null;
        if ($request->filled('lecture_id')) {
            $lecture = Lecture::where('id', $request->input('lecture_id'))
                ->where('subject_id', $subject->id)
                ->first();
        }
        if (!$lecture) {
            $lecture = Lecture::where('subject_id', $subject->id)
                ->where('date', $date)
                ->latest()
                ->first();
        }

        $attendanceQuery = Attendance::where('subject_id', $subject->id)
            ->where('date', $date)
            ->with('recorder');
        if ($lecture) {
            $attendanceQuery->where('lecture_id', $lecture->id);
        }

        $attendanceRecords = $attendanceQuery->get()->keyBy('student_id');

        return $this->success([
            'subject' => $subject->only(['id', 'name']),
            'date' => $date,
            'lecture' => $lecture ? [
                'id' => $lecture->id,
                'title' => $lecture->title,
                'lecture_number' => $lecture->lecture_number,
                'lecture_type' => $lecture->lecture_type,
                'start_time' => $lecture->start_time,
                'end_time' => $lecture->end_time,
            ] : null,
            'students' => $students->map(function ($student) use ($attendanceRecords) {
                $record = $attendanceRecords->get($student->id);

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_number' => $student->student_number,
                    'gender' => $student->gender,
                    'status' => $record?->status,
                    'record' => $record ? [
                        'id' => $record->id,
                        'status' => $record->status,
                        'attendance_method' => $record->attendance_method,
                        'recorded_by' => $record->recorded_by,
                        'recorded_by_name' => $record->recorder?->name,
                        'recorded_by_role' => $record->recorder?->role,
                    ] : null,
                ];
            }),
            'filters' => [
                'gender_filter' => $genderFilter,
                'available_gender_filters' => ['all', 'male', 'female'],
            ],
        ], 'طھظ… ط¬ظ„ط¨ طھظ‚ط±ظٹط± ط§ظ„ط­ط¶ظˆط± ط¨ظ†ط¬ط§ط­');
    }
    /**
     * Get Absence Alerts (Students absent today + At-risk students).
     */
    public function alerts(Request $request)
    {
        $delegate = $request->user();

        // 1. Students absent today
        $absentToday = Attendance::where('date', date('Y-m-d'))
            ->where('status', 'absent')
            ->whereHas('student', function ($q) use ($delegate) {
                $q->where('major_id', $delegate->major_id)
                  ->where('level_id', $delegate->level_id);
            })
            ->with(['student:id,name,avatar,student_number', 'subject:id,name', 'lecture:id,title'])
            ->latest()
            ->get();

        // 2. At-Risk Students (> 20% absence rate in any subject)
        $atRiskStudents = collect();
        
        $subjects = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->get();

        $allStudents = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->get(['id', 'name', 'avatar', 'student_number']);

        // Grouped stats calculation
        $totalSessionsQuery = Attendance::whereIn('student_id', $allStudents->pluck('id'))
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->select('student_id', 'subject_id', DB::raw('count(*) as total'))
            ->groupBy('student_id', 'subject_id')
            ->get();

        $absencesQuery = Attendance::whereIn('student_id', $allStudents->pluck('id'))
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->where('status', 'absent')
            ->select('student_id', 'subject_id', DB::raw('count(*) as absences'))
            ->groupBy('student_id', 'subject_id')
            ->get()
            ->keyBy(function ($item) {
                return $item->student_id . '_' . $item->subject_id;
            });

        foreach ($totalSessionsQuery as $sessionStat) {
            $studentId = $sessionStat->student_id;
            $subjectId = $sessionStat->subject_id;
            $totalSessions = $sessionStat->total;

            if ($totalSessions >= 3) { // Only calculate for students with at least 3 sessions
                $absenceKey = $studentId . '_' . $subjectId;
                $absences = $absencesQuery->has($absenceKey) ? $absencesQuery->get($absenceKey)->absences : 0;
                $absenceRate = ($absences / $totalSessions) * 100;

                if ($absenceRate >= 20) {
                    $student = $allStudents->firstWhere('id', $studentId);
                    $subject = $subjects->firstWhere('id', $subjectId);

                    if ($student && $subject) {
                        $atRiskStudents->push([
                            'student' => $student,
                            'subject' => $subject->only('id', 'name'),
                            'absence_rate' => round($absenceRate, 1),
                            'absences' => $absences,
                            'total_sessions' => $totalSessions
                        ]);
                    }
                }
            }
        }

        return $this->success([
            'absent_today' => $absentToday,
            'at_risk_students' => $atRiskStudents->sortByDesc('absence_rate')->values()
        ], 'طھظ… ط¬ظ„ط¨ طھظ†ط¨ظٹظ‡ط§طھ ط§ظ„ط؛ظٹط§ط¨ ط¨ظ†ط¬ط§ط­');
    }
}
