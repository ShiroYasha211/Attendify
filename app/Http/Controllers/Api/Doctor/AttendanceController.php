<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Enums\UserRole;
use App\Models\Academic\Lecture;
use App\Models\Academic\Subject;
use App\Models\Attendance;
use App\Models\QrAttendanceSession;
use App\Models\Student\StudentScheduleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Support\ExcuseWorkflow;

class AttendanceController extends DoctorApiController
{
    public function index(Request $request)
    {
        $subjectIds = Subject::where('doctor_id', $request->user()->id)->pluck('id');

        $sessions = Attendance::selectRaw('subject_id, date, lecture_id, recorded_by, attendance_method, count(*) as total_records')
            ->whereIn('subject_id', $subjectIds)
            ->with(['subject:id,name,code', 'recorder:id,name,role', 'lecture:id,title,lecture_number,start_time,end_time,lecture_type'])
            ->groupBy('subject_id', 'date', 'lecture_id', 'recorded_by', 'attendance_method')
            ->orderByDesc('date')
            ->get();

        return $this->success($sessions, 'طھظ… ط¬ظ„ط¨ ط¬ظ„ط³ط§طھ ط§ظ„ط­ط¶ظˆط± ط¨ظ†ط¬ط§ط­');
    }

    public function create(Request $request, Subject $subject)
    {
        if ($subject->doctor_id !== $request->user()->id) {
            return $this->error('ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ ط¨ط§ظ„ظˆطµظˆظ„ ظ„ظ‡ط°ط§ ط§ظ„ظ…ظ‚ط±ط±.', 403);
        }

        $date = $request->input('date') ?? now()->format('Y-m-d');
        $lectureId = $request->input('lecture_id');
        $qrSessionId = $request->input('qr_session_id');
        $genderFilter = in_array($request->input('gender_filter'), ['male', 'female'], true)
            ? $request->input('gender_filter')
            : 'all';

        $students = User::whereIn('role', QrAttendanceSession::PARTICIPANT_ROLES)
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->when($genderFilter !== 'all', fn ($query) => $query->where('gender', $genderFilter))
            ->orderBy('name')
            ->get(['id', 'name', 'student_number', 'gender']);

        $prefill = ['date' => $date];
        $verification = null;

        if ($qrSessionId) {
            $qrSession = QrAttendanceSession::where('id', $qrSessionId)
                ->where('delegate_id', $request->user()->id)
                ->firstOrFail();

            $date = $qrSession->date->format('Y-m-d');
            $prefill['date'] = $date;
            $prefill['title'] = $qrSession->title;
            $prefill['lecture_number'] = $qrSession->lecture_number;
            $prefill['from_qr'] = true;
            $verification = $qrSession->buildVerificationPayload();
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
            'verification' => $verification,
            'filters' => [
                'gender_filter' => $genderFilter,
                'available_gender_filters' => ['all', 'male', 'female'],
            ],
            'workflow' => [
                'merge_mode' => true,
                'description' => 'Re-saving the same lecture updates only submitted students and keeps previously recorded students unchanged.',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $subject = Subject::where('id', $request->subject_id)
            ->where('doctor_id', $request->user()->id)
            ->first();

        if (!$subject) {
            return $this->error('ط§ظ„ظ…ط§ط¯ط© ط؛ظٹط± ظ…ظˆط¬ظˆط¯ط© ط£ظˆ ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ.', 403);
        }

        return $this->persist($request, $subject);
    }

    public function storeForSubject(Request $request, Subject $subject)
    {
        if ($subject->doctor_id !== $request->user()->id) {
            return $this->error('ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ ط¨ط§ظ„ظˆطµظˆظ„ ظ„ظ‡ط°ط§ ط§ظ„ظ…ظ‚ط±ط±.', 403);
        }

        return $this->persist($request, $subject);
    }

    public function show(Request $request, $lectureId)
    {
        $lecture = Lecture::with('subject')->findOrFail($lectureId);
        $subject = Subject::where('id', $lecture->subject_id)
            ->where('doctor_id', $request->user()->id)
            ->first();

        if (!$subject) {
            return $this->error('ط§ظ„ظ…ط§ط¯ط© ط؛ظٹط± ظ…ظˆط¬ظˆط¯ط© ط£ظˆ ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ', 403);
        }

        $records = Attendance::where('lecture_id', $lectureId)
            ->with(['student:id,name,student_number,gender'])
            ->get();

        if ($records->isEmpty()) {
            return $this->error('ظ„ط§ طھظˆط¬ط¯ ط³ط¬ظ„ط§طھ ظ„ظ‡ط°ظ‡ ط§ظ„ط¬ظ„ط³ط©', 404);
        }

        return $this->success([
            'subject' => $subject->only(['id', 'name']),
            'lecture' => [
                'id' => $lecture->id,
                'title' => $lecture->title,
                'lecture_number' => $lecture->lecture_number,
                'date' => $lecture->date,
                'start_time' => $lecture->start_time,
                'end_time' => $lecture->end_time,
            ],
            'records' => $records,
        ], 'طھظ… ط¬ظ„ط¨ طھظپط§طµظٹظ„ ط¬ظ„ط³ط© ط§ظ„ط­ط¶ظˆط± ط¨ظ†ط¬ط§ط­');
    }

    public function toggleDelegate(Request $request, Subject $subject)
    {
        if ($subject->doctor_id !== $request->user()->id) {
            return $this->error('ط؛ظٹط± ظ…طµط±ط­ ظ„ظƒ ط¨ط§ظ„ظˆطµظˆظ„ ظ„ظ‡ط°ط§ ط§ظ„ظ…ظ‚ط±ط±.', 403);
        }

        $subject->allow_delegate_attendance = !$subject->allow_delegate_attendance;
        $subject->save();

        return $this->success([
            'subject_id' => $subject->id,
            'allow_delegate_attendance' => (bool) $subject->allow_delegate_attendance,
        ], $subject->allow_delegate_attendance ? 'طھظ… طھظپط¹ظٹظ„ طµظ„ط§ط­ظٹط© طھط­ط¶ظٹط± ط§ظ„ظ…ظ†ط¯ظˆط¨.' : 'طھظ… ط¥ظٹظ‚ط§ظپ طµظ„ط§ط­ظٹط© طھط­ط¶ظٹط± ط§ظ„ظ…ظ†ط¯ظˆط¨.');
    }

    public function report(Request $request, int $subjectId, string $date)
    {
        $subject = Subject::where('id', $subjectId)
            ->where('doctor_id', $request->user()->id)
            ->firstOrFail();

        $genderFilter = in_array($request->input('gender_filter'), ['male', 'female'], true)
            ? $request->input('gender_filter')
            : 'all';

        $students = User::whereIn('role', QrAttendanceSession::PARTICIPANT_ROLES)
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
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
        ]);
    }

    protected function persist(Request $request, Subject $subject)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'lecture_type' => 'nullable|in:official,special',
            'lecture_number' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
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

        $students = collect($request->input('students', []));
        if ($students->isEmpty()) {
            $students = collect($request->input('attendance', []))
                ->map(fn ($status, $studentId) => ['id' => (int) $studentId, 'status' => $status])
                ->values();
        }

        if ($students->isEmpty()) {
            return $this->error('ظٹط¬ط¨ ط¥ط±ط³ط§ظ„ ط¨ظٹط§ظ†ط§طھ ط§ظ„ط­ط¶ظˆط± ظ„ظ„ط·ظ„ط§ط¨.', 422);
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

        $genderFilter = in_array($request->input('gender_filter'), ['male', 'female'], true)
            ? $request->input('gender_filter')
            : 'all';

        $allowedStudentIds = User::whereIn('role', QrAttendanceSession::PARTICIPANT_ROLES)
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->when($genderFilter !== 'all', fn ($query) => $query->where('gender', $genderFilter))
            ->pluck('id');

        if ($students->pluck('id')->diff($allowedStudentIds)->isNotEmpty()) {
            return $this->error('One or more students are outside the subject scope or current gender filter.', 422);
        }

        DB::beginTransaction();

        try {
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
                    ],
                    [
                        'lecture_id' => $lecture->id,
                        'status' => $student['status'],
                        'recorded_by' => $request->user()->id,
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

            if ($request->filled('qr_session_id')) {
                $this->syncQrVerificationResults((int) $request->input('qr_session_id'), $students, $request->user()->id);
            }

            DB::commit();

            return $this->success([
                'lecture_id' => $lecture->id,
                'lecture_type' => $lecture->lecture_type,
                'merge_mode' => true,
                'gender_filter' => $genderFilter,
                'updated_students_count' => $students->count(),
            ], 'طھظ… ط­ظپط¸ ط³ط¬ظ„ ط§ظ„ط­ط¶ظˆط± ظˆط§ظ„ط؛ظٹط§ط¨ ط¨ظ†ط¬ط§ط­', 201);
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->error('ط­ط¯ط« ط®ط·ط£ ط£ط«ظ†ط§ط، ط±طµط¯ ط§ظ„ط­ط¶ظˆط±: ' . $exception->getMessage(), 500);
        }
    }

    protected function syncQrVerificationResults(int $qrSessionId, \Illuminate\Support\Collection $students, int $reviewerId): void
    {
        $session = QrAttendanceSession::with('verifications')->find($qrSessionId);

        if (!$session) {
            return;
        }

        $statusMap = $students->pluck('status', 'id');

        foreach ($session->verifications as $verification) {
            $finalStatus = $statusMap->get($verification->student_id);

            if (!$finalStatus) {
                continue;
            }

            $verification->update([
                'verification_status' => $finalStatus === Attendance::STATUS_ABSENT ? 'confirmed_absent' : 'confirmed_present',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);
        }
    }
}

