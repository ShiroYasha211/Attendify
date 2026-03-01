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

        $inquiries = Inquiry::whereIn('subject_id', $subjectIds)
            ->with(['student:id,name', 'subject:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($inquiries, 'تم جلب الاستفسارات بنجاح');
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
            'answer' => $request->reply,
            'status' => 'answered',
            'answered_at' => now(),
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
