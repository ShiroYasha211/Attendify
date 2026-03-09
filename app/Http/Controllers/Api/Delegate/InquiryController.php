<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Inquiry;
use App\Models\Academic\Subject;
use Illuminate\Support\Facades\Validator;

class InquiryController extends DelegateApiController
{
    /**
     * Display a listing of inquiries for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $subjectIds = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->pluck('id');

        $query = Inquiry::whereIn('subject_id', $subjectIds)
            ->with(['student:id,name', 'subject:id,name'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $inquiries = $query->get();

        // Statistics
        $stats = [
            'total' => Inquiry::whereIn('subject_id', $subjectIds)->count(),
            'pending' => Inquiry::whereIn('subject_id', $subjectIds)->where('status', 'pending')->count(),
            'forwarded' => Inquiry::whereIn('subject_id', $subjectIds)->where('status', 'forwarded')->count(),
            'answered' => Inquiry::whereIn('subject_id', $subjectIds)->where('status', 'answered')->count(),
            'closed' => Inquiry::whereIn('subject_id', $subjectIds)->where('status', 'closed')->count(),
        ];

        return $this->success([
            'stats' => $stats,
            'inquiries' => $inquiries
        ], 'تم جلب الاستفسارات بنجاح');
    }

    /**
     * Display the specified inquiry with replies.
     */
    public function show(Request $request, string $id)
    {
        $delegate = $request->user();

        $inquiry = Inquiry::with(['student:id,name', 'subject:id,name'])->find($id);

        if (!$inquiry) {
            return $this->error('الاستفسار غير موجود', 404);
        }

        // Validate scope
        $subject = Subject::where('id', $inquiry->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('غير مصرح لك بمشاهدة هذا الاستفسار', 403);
        }

        return $this->success($inquiry, 'تم جلب بيانات الاستفسار بنجاح');
    }

    /**
     * Forward inquiry to doctor.
     */
    public function forward(Request $request, string $id)
    {
        $delegate = $request->user();

        $inquiry = Inquiry::find($id);

        if (!$inquiry) {
            return $this->error('الاستفسار غير موجود', 404);
        }

        $subject = Subject::where('id', $inquiry->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('غير مصرح لك بتحويل هذا الاستفسار', 403);
        }

        $inquiry->update([
            'status' => 'forwarded',
            'delegate_id' => $delegate->id,
        ]);

        // Notify student
        \App\Models\StudentNotification::create([
            'user_id' => $inquiry->student_id,
            'type'    => 'announcement', // Using announcement type or generic bell
            'title'   => 'تحديث على استفسارك',
            'message' => "تم تحويل استفسارك حول مادة {$subject->name} إلى الدكتور للمراجعة.",
            'data'    => ['inquiry_id' => $inquiry->id],
        ]);

        return $this->success($inquiry, 'تم تحويل الاستفسار للدكتور بنجاح');
    }

    /**
     * Store a reply to an inquiry.
     */
    public function storeReply(Request $request, string $id)
    {
        $delegate = $request->user();

        $inquiry = Inquiry::find($id);

        if (!$inquiry) {
            return $this->error('الاستفسار غير موجود', 404);
        }

        $subject = Subject::where('id', $inquiry->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('غير مصرح لك بالرد على هذا الاستفسار', 403);
        }

        $validator = Validator::make($request->all(), [
            'reply' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $inquiry->update([
            'reply' => $request->reply,
            'status' => 'answered',
            'delegate_id' => $delegate->id,
            'answered_at' => now(),
        ]);

        // Notify student
        \App\Models\StudentNotification::create([
            'user_id' => $inquiry->student_id,
            'type'    => 'announcement',
            'title'   => 'تم الرد على استفسارك',
            'message' => "تلقيت رداً جديداً على استفسارك حول مادة {$subject->name}.",
            'data'    => ['inquiry_id' => $inquiry->id],
        ]);

        return $this->success($inquiry, 'تم الرد بنجاح', 200);
    }

    /**
     * Update the status of an inquiry.
     */
    public function updateStatus(Request $request, string $id)
    {
        $delegate = $request->user();

        $inquiry = Inquiry::find($id);

        if (!$inquiry) {
            return $this->error('الاستفسار غير موجود', 404);
        }

        $subject = Subject::where('id', $inquiry->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('غير مصرح لك بتحديث هذا الاستفسار', 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,answered,closed',
            'is_public' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $inquiry->update([
            'status' => $request->status,
            'is_public' => $request->has('is_public') ? $request->is_public : $inquiry->is_public,
        ]);

        return $this->success($inquiry, 'تم تحديث حالة الاستفسار بنجاح');
    }
}
