<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\ExamSchedule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExamScheduleController extends DelegateApiController
{
    /**
     * Display a listing of exam schedules for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $schedules = ExamSchedule::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with(['items.subject:id,name,code', 'creator:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($schedules, 'تم جلب جداول الاختبارات بنجاح');
    }

    /**
     * Store a newly created exam schedule with its items.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'terms_id' => 'required|exists:terms,id', // Matches form input names (terms_id)
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.subject_id' => 'required|exists:subjects,id',
            'items.*.exam_date' => 'required|date',
            'items.*.start_time' => 'required|date_format:H:i',
            'items.*.end_time' => 'required|date_format:H:i|after:items.*.start_time',
            'items.*.hall_name' => 'required|string|max:255',
            'items.*.exam_type' => 'required|in:midterm,final,practical,quiz,other',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        try {
            DB::beginTransaction();

            $schedule = ExamSchedule::create([
                'major_id' => $delegate->major_id,
                'level_id' => $delegate->level_id,
                'term_id' => $request->terms_id,
                'title' => $request->title,
                'description' => $request->description,
                'is_published' => true,
                'created_by' => $delegate->id,
            ]);

            foreach ($request->items as $item) {
                // Ideally, validate if subject_id belongs to the delegate's scope here
                $schedule->items()->create($item);
            }

            DB::commit();

            return $this->success($schedule->load('items.subject'), 'تم حفظ جدول الاختبارات بنجاح', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء حفظ الجدول: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified exam schedule.
     */
    public function update(Request $request, string $id)
    {
        $delegate = $request->user();

        $schedule = ExamSchedule::find($id);

        if (!$schedule || $schedule->major_id !== $delegate->major_id || $schedule->level_id !== $delegate->level_id) {
            return $this->error('الجدول غير موجود أو غير مصرح لك', 404);
        }

        $validator = Validator::make($request->all(), [
            'terms_id' => 'required|exists:terms,id', // Matches form input names (terms_id)
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.subject_id' => 'required|exists:subjects,id',
            'items.*.exam_date' => 'required|date',
            'items.*.start_time' => 'required|date_format:H:i',
            'items.*.end_time' => 'required|date_format:H:i|after:items.*.start_time',
            'items.*.hall_name' => 'required|string|max:255',
            'items.*.exam_type' => 'required|in:midterm,final,practical,quiz,other',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        try {
            DB::beginTransaction();

            $schedule->update([
                'term_id' => $request->terms_id,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            // Sync items (Delete existing and recreate for simplicity)
            $schedule->items()->delete();

            foreach ($request->items as $item) {
                $schedule->items()->create($item);
            }

            DB::commit();

            return $this->success($schedule->load('items.subject'), 'تم تحديث جدول الاختبارات بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء تحديث الجدول: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified exam schedule.
     */
    public function destroy(Request $request, string $id)
    {
        $delegate = $request->user();

        $schedule = ExamSchedule::find($id);

        if (!$schedule || $schedule->major_id !== $delegate->major_id || $schedule->level_id !== $delegate->level_id) {
            return $this->error('الجدول غير موجود أو غير مصرح لك', 404);
        }

        $schedule->delete();

        return $this->success(null, 'تم حذف جدول الاختبارات بنجاح');
    }
}
