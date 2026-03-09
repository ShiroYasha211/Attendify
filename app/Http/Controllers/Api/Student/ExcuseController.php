<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Excuse;
use App\Models\Attendance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExcuseController extends StudentApiController
{
    /**
     * Submit a new excuse via API
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendance_id' => 'required|exists:attendances,id',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // 2MB Max
        ], [
            'attendance_id.required' => 'معرف سجل الحضور مطلوب.',
            'attendance_id.exists' => 'سجل الحضور غير موجود.',
            'reason.required' => 'سبب العذر مطلوب.',
            'attachment.mimes' => 'يجب أن يكون المرفق ملف PDF أو صورة (JPG, PNG).',
            'attachment.max' => 'حجم المرفق لا يجب أن يتجاوز 2 ميجابايت.',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422, $validator->errors());
        }

        $student = Auth::user();
        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('student_id', $student->id)
            ->first();

        if (!$attendance) {
            return $this->error('سجل الحضور المختار غير تابع لك.', 403);
        }

        // 1. Check if eligible (Absent)
        if ($attendance->status !== 'absent') {
            return $this->error('لا يمكن تقديم عذر لمحاضرة لست غائباً فيها.', 400);
        }

        // 2. Check deadline (Dynamic from settings)
        $excuseDeadlineDays = (int) Setting::get('excuse_deadline_days', 3);
        $lectureDate = Carbon::parse($attendance->date);
        $deadline = $lectureDate->copy()->addDays($excuseDeadlineDays);

        if (now()->gt($deadline)) {
            return $this->error("عذراً، انتهت المهلة المحددة لتقديم العذر ({$excuseDeadlineDays} أيام من تاريخ الغياب).", 400);
        }

        // 3. Check if already submitted
        if ($attendance->excuse) {
            return $this->error('لقد قمت بتقديم عذر مسبقاً لهذا الغياب.', 400);
        }

        // Handle File Upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('excuses', 'public');
        }

        // Create Excuse
        $excuse = Excuse::create([
            'attendance_id' => $attendance->id,
            'student_id' => $student->id,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return $this->success([
            'excuse_id' => $excuse->id,
            'status' => $excuse->status,
            'attachment_url' => $attachmentPath ? asset('storage/' . $attachmentPath) : null
        ], 'تم تقديم العذر بنجاح، بانتظار موافقة الدكتور.', 201);
    }
}
