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
        $user = $request->user();

        // 1. Stats Calculation
        $stats = [
            'total_files' => CourseResource::whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)->where('level_id', $user->level_id);
            })->count(),
            'my_uploads' => CourseResource::where('created_by', $user->id)->count(),
            'recent_week' => CourseResource::whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)->where('level_id', $user->level_id);
            })->where('created_at', '>=', now()->subWeek())->count(),
        ];

        // 2. Fetch Resources with Filters
        $query = CourseResource::with(['subject:id,name,code', 'uploader:id,name'])
            ->whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)
                    ->where('level_id', $user->level_id);
            });

        // Search Filter
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Subject Filter
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Category Filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $resources = $query->latest()->get();

        return $this->success([
            'stats' => $stats,
            'resources' => $resources
        ], 'تم جلب المصادر والمذكرات بنجاح');
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
            'category' => 'required|in:lectures,references,summaries,exams,other',
            'file' => 'required|file|max:20480', // 20MB max
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
        $extension = $file->getClientOriginalExtension();
        $path = $file->store('course_resources', 'public');

        $resource = CourseResource::create([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $path,
            'file_type' => $extension,
            'description' => $request->description,
            'created_by' => $delegate->id,
        ]);

        return $this->success($resource->load('subject', 'uploader'), 'تم رفع الملف بنجاح', 201);
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, string $id)
    {
        $delegate = $request->user();

        $resource = CourseResource::find($id);

        if (!$resource || $resource->created_by !== $delegate->id) {
            return $this->error('الملف غير موجود أو غير مصرح لك بتعديله', 403);
        }

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:lectures,references,summaries,exams,other',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        $resource->update([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
        ]);

        return $this->success($resource->load('subject'), 'تم تحديث بيانات الملف بنجاح');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Request $request, string $id)
    {
        $delegate = $request->user();

        $resource = CourseResource::find($id);

        if (!$resource || $resource->created_by !== $delegate->id) {
            return $this->error('الملف غير موجود أو غير مصرح لك بحذفه', 403);
        }

        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return $this->success(null, 'تم حذف الملف بنجاح');
    }

    /**
     * Search global library for resources.
     */
    public function searchLibrary(Request $request)
    {
        $query = $request->get('q');
        $user = $request->user();

        $resources = CourseResource::with(['subject:id,name,code', 'uploader:id,name'])
            ->whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id);
            })
            ->when($query, function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhereHas('subject', function ($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
            })
            ->latest()
            ->take(30)
            ->get();

        return $this->success($resources, 'تم البحث في المكتبة بنجاح');
    }

    /**
     * Import a resource from the library.
     */
    public function import(Request $request)
    {
        $request->validate([
            'resource_id' => 'required|exists:course_resources,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $original = CourseResource::findOrFail($request->resource_id);
        $delegate = $request->user();

        // Ensure subject belongs to delegate scope
        $subject = Subject::where('id', $request->subject_id)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$subject) {
            return $this->error('المادة المستهدفة غير صالحة', 403);
        }

        // Prevent duplicate import
        $exists = CourseResource::where('subject_id', $request->subject_id)
            ->where('file_path', $original->file_path)
            ->exists();

        if ($exists) {
            return $this->error('هذا الملف موجود بالفعل في هذا المقرر الدراسيك.', 422);
        }

        $resource = CourseResource::create([
            'subject_id' => $request->subject_id,
            'created_by' => $delegate->id,
            'title' => $original->title,
            'category' => $original->category,
            'file_path' => $original->file_path,
            'file_type' => $original->file_type,
            'description' => $original->description . " (تم استيراده من المكتبة)",
        ]);

        return $this->success($resource, 'تم استيراد الملف بنجاح', 201);
    }
}
