<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Attendance;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AttendanceController extends DelegateApiController
{
    /**
     * Display a listing of attendance sessions for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $subjectIds = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->pluck('id');

        $sessions = Attendance::select('date', 'subject_id', 'recorded_by')
            ->whereIn('subject_id', $subjectIds)
            ->with(['subject:id,name', 'recorder:id,name'])
            ->groupBy('date', 'subject_id', 'recorded_by')
            ->orderBy('date', 'desc')
            ->get();

        return $this->success($sessions, 'تم جلب جلسات الحضور بنجاح');
    }

    /**
     * Store new attendance records.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'subject_id' => 'required|exists:subjects,id',
            'students' => 'required|array|min:1',
            'students.*.id' => 'required|exists:users,id',
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

        // Prevent duplicate session creation
        $existingSession = Attendance::where('subject_id', $request->subject_id)
            ->whereDate('date', $request->date)
            ->exists();

        if ($existingSession) {
            return $this->error('تم رصد الغياب لهذه المادة في هذا التاريخ مسبقاً', 409);
        }

        try {
            DB::beginTransaction();

            $records = [];
            foreach ($request->students as $student) {
                // Should technically validate that student is in the delegate's batch
                $records[] = [
                    'student_id' => $student['id'],
                    'subject_id' => $request->subject_id,
                    'date' => $request->date,
                    'status' => $student['status'],
                    'recorded_by' => $delegate->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Attendance::insert($records);

            DB::commit();
            return $this->success(null, 'تم حفظ سجل الحضور والغياب بنجاح', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء رصد الغياب', 500);
        }
    }

    /**
     * View details of a specific attendance session.
     */
    public function show(Request $request, string $subjectId, string $date)
    {
        $delegate = $request->user();

        $subject = Subject::where('id', $subjectId)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('المادة غير موجودة أو غير مصرح لك', 403);
        }

        $records = Attendance::where('subject_id', $subjectId)
            ->whereDate('date', $date)
            ->with(['student:id,name,university_id'])
            ->get();

        if ($records->isEmpty()) {
            return $this->error('لا توجد سجلات لهذه الجلسة', 404);
        }

        return $this->success([
            'subject' => $subject->only('id', 'name'),
            'date' => $date,
            'records' => $records
        ], 'تم جلب تفاصيل جلسة الحضور بنجاح');
    }
}
