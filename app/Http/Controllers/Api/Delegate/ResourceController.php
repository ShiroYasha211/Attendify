<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\CourseResource;
use App\Models\Academic\Subject;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ResourceController extends DelegateApiController
{
    /**
     * Display a listing of resources for the delegate's batch.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $subjectIds = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->pluck('id');

        $resources = CourseResource::whereIn('subject_id', $subjectIds)
            ->with(['subject:id,name,code', 'uploader:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($resources, 'تم جلب المصادر والمذكرات بنجاح');
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:lecture,summary,book,other',
            'file' => 'required|file|max:51200', // 50MB max
            'description' => 'nullable|string',
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

        $file = $request->file('file');
        $path = $file->store('course_resources', 'public');

        $resource = CourseResource::create([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'uploaded_by' => $delegate->id,
            'is_approved' => true,
        ]);

        return $this->success($resource->load('subject', 'uploader'), 'تم رفع الملف بنجاح', 201);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Request $request, string $id)
    {
        $delegate = $request->user();

        $resource = CourseResource::with('subject')->find($id);

        if (!$resource || $resource->subject->major_id !== $delegate->major_id || $resource->subject->level_id !== $delegate->level_id) {
            return $this->error('الملف غير موجود أو غير مصرح لك', 404);
        }

        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return $this->success(null, 'تم حذف الملف بنجاح');
    }
}
