<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Academic\Schedule;
use App\Models\Academic\Subject;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends DelegateApiController
{
    /**
     * Display a listing of schedules for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        // Get subjects for this delegate's scope
        $subjectIds = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->pluck('id');

        $schedules = Schedule::whereIn('subject_id', $subjectIds)
            ->with(['subject:id,name,code,doctor_id', 'subject.doctor:id,name'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->success($schedules, 'تم جلب جدول المحاضرات بنجاح');
    }

    /**
     * Store a newly created schedule.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'hall_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        // Validate subject belongs to delegate scope
        $subject = Subject::where('id', $request->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('المادة غير موجودة أو غير مصرح لك', 403);
        }

        $schedule = Schedule::create($request->all());

        return $this->success($schedule, 'تمت إضافة المحاضرة للجدول بنجاح', 201);
    }

    /**
     * Update the specified schedule.
     */
    public function update(Request $request, string $id)
    {
        $delegate = $request->user();

        $schedule = Schedule::with('subject')->find($id);

        if (!$schedule || $schedule->subject->major_id !== $delegate->major_id || $schedule->subject->level_id !== $delegate->level_id) {
            return $this->error('سجل الجدول غير موجود أو غير مصرح لك', 404);
        }

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i|string',
            'end_time' => 'required|date_format:H:i|string|after:start_time',
            'hall_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        // Validate new subject belongs to delegate scope (if changed)
        if ($request->subject_id != $schedule->subject_id) {
            $newSubject = Subject::where('id', $request->subject_id)
                ->where('major_id', $delegate->major_id)
                ->where('level_id', $delegate->level_id)
                ->first();

            if (!$newSubject) {
                return $this->error('المادة الجديدة غير مصرح بها', 403);
            }
        }

        $schedule->update($request->all());

        return $this->success($schedule, 'تم تحديث المحاضرة بنجاح');
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy(Request $request, string $id)
    {
        $delegate = $request->user();

        $schedule = Schedule::with('subject')->find($id);

        if (!$schedule || $schedule->subject->major_id !== $delegate->major_id || $schedule->subject->level_id !== $delegate->level_id) {
            return $this->error('سجل الجدول غير موجود أو غير مصرح لك', 404);
        }

        $schedule->delete();

        return $this->success(null, 'تم حذف المحاضرة من الجدول بنجاح');
    }
}
