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

class AttendanceController extends DelegateApiController
{
    /**
     * Display a listing of attendance sessions for the delegate's batch.
     * Groups by lecture_id to support multiple lectures per day.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $subjectIds = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->pluck('id');

        // Group by lecture_id to properly separate multiple lectures on the same day
        $sessions = Attendance::selectRaw('subject_id, date, lecture_id, recorded_by, count(*) as total_records')
            ->whereIn('subject_id', $subjectIds)
            ->with(['subject:id,name', 'recorder:id,name', 'lecture:id,title,lecture_number,start_time,end_time'])
            ->groupBy('subject_id', 'date', 'lecture_id', 'recorded_by')
            ->orderBy('date', 'desc')
            ->get();

        return $this->success($sessions, 'تم جلب جلسات الحضور بنجاح');
    }

    /**
     * Store new attendance records.
     * Creates/updates a Lecture record and links attendance to it.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'date'           => 'required|date',
            'subject_id'     => 'required|exists:subjects,id',
            'title'          => 'required|string|max:255',
            'lecture_number' => 'nullable|string|max:50',
            'start_time'     => 'nullable',
            'end_time'       => 'nullable',
            'students'       => 'required|array|min:1',
            'students.*.id'     => 'required|exists:users,id',
            'students.*.status' => 'required|in:present,absent,late,excused',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $subject = Subject::where('id', $request->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('المادة غير موجودة أو غير مصرح لك', 403);
        }

        // Ensure delegate attendance is allowed by the doctor
        if (!$subject->allow_delegate_attendance) {
            return $this->error('التحضير مغلق من قبل الدكتور المشرف على المادة.', 403);
        }

        // Prevent duplicate: check by subject + date + title + lecture_number
        $lectureKey = [
            'subject_id' => $subject->id,
            'date'       => $request->date,
            'title'      => $request->title,
        ];
        if (!empty($request->lecture_number)) {
            $lectureKey['lecture_number'] = $request->lecture_number;
        }

        $existingLecture = Lecture::where($lectureKey)->first();
        if ($existingLecture) {
            $hasAttendance = Attendance::where('lecture_id', $existingLecture->id)->exists();
            if ($hasAttendance) {
                return $this->error('تم رصد الغياب لهذه المحاضرة مسبقاً. استخدم نفس العنوان مع رقم محاضرة مختلف لإضافة محاضرة جديدة.', 409);
            }
        }

        try {
            DB::beginTransaction();

            // Create or update Lecture record (same logic as web controller)
            $lecture = Lecture::updateOrCreate(
                $lectureKey,
                [
                    'title'          => $request->title,
                    'lecture_number' => $request->lecture_number,
                    'start_time'     => $request->start_time,
                    'end_time'       => $request->end_time,
                ]
            );

            // Determine attendance method
            $attendanceMethod = $request->has('qr_session_id') ? 'qr' : 'manual';

            foreach ($request->students as $student) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $student['id'],
                        'subject_id' => $subject->id,
                        'date'       => $request->date,
                        'lecture_id' => $lecture->id,
                    ],
                    [
                        'status'            => $student['status'],
                        'recorded_by'       => $delegate->id,
                        'attendance_method'  => $attendanceMethod,
                    ]
                );

                // Auto-add to Student Study Schedule
                \App\Models\Student\StudentScheduleItem::firstOrCreate(
                    [
                        'user_id'            => $student['id'],
                        'referenceable_type'  => Lecture::class,
                        'referenceable_id'    => $lecture->id,
                    ],
                    [
                        'title'          => $request->title,
                        'scheduled_date' => $request->date,
                        'item_type'      => 'study',
                        'priority'       => 'medium',
                        'status'         => 'pending',
                    ]
                );
            }

            // Notify the Doctor about the lecture report
            if ($subject->doctor_id) {
                $presentCount = collect($request->students)->where('status', 'present')->count();
                $absentCount  = collect($request->students)->where('status', 'absent')->count();
                $lateCount    = collect($request->students)->where('status', 'late')->count();
                $totalStudents = count($request->students);

                \App\Models\StudentNotification::create([
                    'user_id' => $subject->doctor_id,
                    'type'    => 'lecture_report',
                    'title'   => "📋 تقرير محاضرة: {$subject->name}",
                    'message' => "تم تسجيل الحضور لمحاضرة \"{$request->title}\" بتاريخ {$request->date}.\n"
                        . "إجمالي الطلاب: {$totalStudents} | حضور: {$presentCount} | غياب: {$absentCount} | تأخير: {$lateCount}",
                    'data'    => [
                        'subject_id'  => $subject->id,
                        'lecture_id'  => $lecture->id,
                        'date'        => $request->date,
                    ],
                ]);
            }

            DB::commit();
            return $this->success([
                'lecture_id' => $lecture->id,
            ], 'تم حفظ سجل الحضور والغياب بنجاح', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء رصد الغياب: ' . $e->getMessage(), 500);
        }
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
            return $this->error('المادة غير موجودة أو غير مصرح لك', 403);
        }

        $records = Attendance::where('lecture_id', $lectureId)
            ->with(['student:id,name,university_id'])
            ->get();

        if ($records->isEmpty()) {
            return $this->error('لا توجد سجلات لهذه الجلسة', 404);
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
        ], 'تم جلب تفاصيل جلسة الحضور بنجاح');
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
        ], 'تم جلب تنبيهات الغياب بنجاح');
    }
}
