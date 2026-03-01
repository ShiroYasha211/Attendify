<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Reminder;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReminderController extends DelegateApiController
{
    /**
     * Display a listing of reminders for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $remindersQuery = Reminder::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with('author:id,name');

        if ($request->has('filter') && $request->filter === 'past') {
            $remindersQuery->where('reminder_time', '<', Carbon::now())
                ->orderBy('reminder_time', 'desc');
        } else {
            // Default to upcoming
            $remindersQuery->where('reminder_time', '>=', Carbon::now())
                ->orderBy('reminder_time', 'asc');
        }

        $reminders = $remindersQuery->get();

        return $this->success($reminders, 'تم جلب التذكيرات بنجاح');
    }

    /**
     * Store a newly created reminder.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:exam,assignment,lecture,event,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reminder_time' => 'required|date|after:now',
            'notify_before_minutes' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $reminder = Reminder::create([
            'major_id' => $delegate->major_id,
            'level_id' => $delegate->level_id,
            'user_id' => $delegate->id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'reminder_time' => $request->reminder_time,
            'notify_before_minutes' => $request->notify_before_minutes,
            'is_sent' => false,
        ]);

        return $this->success($reminder->load('author'), 'تمت إضافة التذكير بنجاح', 201);
    }

    /**
     * Update the specified reminder.
     */
    public function update(Request $request, string $id)
    {
        $delegate = $request->user();

        $reminder = Reminder::find($id);

        if (!$reminder || $reminder->major_id !== $delegate->major_id || $reminder->level_id !== $delegate->level_id) {
            return $this->error('التذكير غير موجود أو غير مصرح لك', 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:exam,assignment,lecture,event,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reminder_time' => 'required|date|after:now',
            'notify_before_minutes' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $reminder->update([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'reminder_time' => $request->reminder_time,
            'notify_before_minutes' => $request->notify_before_minutes,
        ]);

        return $this->success($reminder->load('author'), 'تم تحديث التذكير بنجاح');
    }

    /**
     * Remove the specified reminder.
     */
    public function destroy(Request $request, string $id)
    {
        $delegate = $request->user();

        $reminder = Reminder::find($id);

        if (!$reminder || $reminder->major_id !== $delegate->major_id || $reminder->level_id !== $delegate->level_id) {
            return $this->error('التذكير غير موجود أو غير مصرح لك', 404);
        }

        $reminder->delete();

        return $this->success(null, 'تم حذف التذكير بنجاح');
    }
}
