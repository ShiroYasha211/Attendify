<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Announcement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends DelegateApiController
{
    /**
     * Display a listing of announcements for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $announcements = Announcement::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->with('author:id,name')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($announcements, 'تم جلب الإعلانات بنجاح');
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:general,schedule,exam,activity,urgent',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'is_pinned' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $data = $request->except('attachment');
        $data['major_id'] = $delegate->major_id;
        $data['level_id'] = $delegate->level_id;
        $data['user_id'] = $delegate->id;
        $data['is_pinned'] = $request->has('is_pinned') && $request->is_pinned ? true : false;
        $data['status'] = 'published';

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('announcements', 'public');
        }

        $announcement = Announcement::create($data);

        return $this->success($announcement->load('author'), 'تم نشر الإعلان بنجاح', 201);
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, string $id)
    {
        $delegate = $request->user();

        $announcement = Announcement::find($id);

        if (!$announcement || $announcement->major_id !== $delegate->major_id || $announcement->level_id !== $delegate->level_id) {
            return $this->error('الإعلان غير موجود أو غير مصرح لك', 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:general,schedule,exam,activity,urgent',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
            'is_pinned' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $data = $request->except('attachment');
        $data['is_pinned'] = $request->has('is_pinned') && $request->is_pinned ? true : false;

        if ($request->hasFile('attachment')) {
            if ($announcement->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('announcements', 'public');
        }

        $announcement->update($data);

        return $this->success($announcement->load('author'), 'تم تحديث الإعلان بنجاح');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(Request $request, string $id)
    {
        $delegate = $request->user();

        $announcement = Announcement::find($id);

        if (!$announcement || $announcement->major_id !== $delegate->major_id || $announcement->level_id !== $delegate->level_id) {
            return $this->error('الإعلان غير موجود أو غير مصرح لك', 404);
        }

        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }

        $announcement->delete();

        return $this->success(null, 'تم حذف الإعلان بنجاح');
    }
}
