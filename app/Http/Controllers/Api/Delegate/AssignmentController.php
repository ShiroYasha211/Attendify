<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Academic\Assignment;
use App\Models\Academic\Subject;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends DelegateApiController
{
    /**
     * Display a listing of assignments for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $assignments = Assignment::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with(['subject:id,name,code', 'creator:id,name'])
            ->orderBy('due_date', 'asc')
            ->get();

        return $this->success($assignments, 'تم جلب التكاليف بنجاح');
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'total_marks' => 'required|numeric|min:0',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'status' => 'required|in:active,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        // Validate subject scope
        $subject = Subject::where('id', $request->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('المادة غير موجودة أو غير مصرح لك', 403);
        }

        $data = $request->except('attachment');
        $data['major_id'] = $delegate->major_id;
        $data['level_id'] = $delegate->level_id;
        $data['created_by'] = $delegate->id;

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('assignments', 'public');
        }

        $assignment = Assignment::create($data);

        return $this->success($assignment->load('subject', 'creator'), 'تم إضافة التكليف بنجاح', 201);
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, string $id)
    {
        $delegate = $request->user();

        $assignment = Assignment::find($id);

        if (!$assignment || $assignment->major_id !== $delegate->major_id || $assignment->level_id !== $delegate->level_id) {
            return $this->error('التكليف غير موجود أو غير مصرح لك', 404);
        }

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'total_marks' => 'required|numeric|min:0',
            'attachment' => 'nullable|file|max:10240',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        if ($request->subject_id != $assignment->subject_id) {
            $subject = Subject::where('id', $request->subject_id)
                ->where('major_id', $delegate->major_id)
                ->where('level_id', $delegate->level_id)
                ->first();

            if (!$subject) {
                return $this->error('المادة الجديدة غير مصرح بها', 403);
            }
        }

        $data = $request->except('attachment');

        if ($request->hasFile('attachment')) {
            if ($assignment->attachment_path) {
                Storage::disk('public')->delete($assignment->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('assignments', 'public');
        }

        $assignment->update($data);

        return $this->success($assignment->load('subject', 'creator'), 'تم تحديث التكليف بنجاح');
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(Request $request, string $id)
    {
        $delegate = $request->user();

        $assignment = Assignment::find($id);

        if (!$assignment || $assignment->major_id !== $delegate->major_id || $assignment->level_id !== $delegate->level_id) {
            return $this->error('التكليف غير موجود أو غير مصرح لك', 404);
        }

        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }

        $assignment->delete();

        return $this->success(null, 'تم حذف التكليف بنجاح');
    }
}
