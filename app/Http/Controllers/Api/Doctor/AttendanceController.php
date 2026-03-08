<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Doctor\DoctorApiController;
use App\Models\Attendance;
use App\Models\Academic\Subject;
use App\Models\Academic\Lecture;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AttendanceController extends DoctorApiController
{
    /**
     * Display a listing of attendance sessions for the doctor's subjects.
     * Groups by lecture_id to support multiple lectures per day.
     */
    public function index(Request $request)
    {
        $doctor = $request->user();

        $subjectIds = Subject::where('doctor_id', $doctor->id)->pluck('id');

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
        $doctor = $request->user();

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
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$subject) {
            return $this->error('المادة غير موجودة أو غير مصرح لك', 403);
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
                return $this->error('تم رصد الحضور لهذه المحاضرة مسبقاً. استخدم نفس العنوان مع رقم محاضرة مختلف لإضافة محاضرة جديدة.', 409);
            }
        }

        try {
            DB::beginTransaction();

            // Create or update Lecture record
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
                        'recorded_by'       => $doctor->id,
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

            DB::commit();
            return $this->success([
                'lecture_id' => $lecture->id,
            ], 'تم حفظ سجل الحضور والغياب بنجاح', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء رصد الحضور: ' . $e->getMessage(), 500);
        }
    }

    /**
     * View details of a specific attendance session by lecture ID.
     */
    public function show(Request $request, $lectureId)
    {
        $doctor = $request->user();

        $lecture = Lecture::with('subject')->findOrFail($lectureId);

        $subject = Subject::where('id', $lecture->subject_id)
            ->where('doctor_id', $doctor->id)
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
}
