<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use App\Models\CourseResource;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

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
        $query = CourseResource::with(['subject', 'uploader'])
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

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'subject':
                $query->join('subjects', 'course_resources.subject_id', '=', 'subjects.id')
                    ->orderBy('subjects.name')
                    ->select('course_resources.*');
                break;
            default: // newest
                $query->latest();
        }

        $resources = $query->paginate(10);

        // 3. Dropdown Data
        $subjects = Subject::where('major_id', $user->major_id)
            ->where('level_id', $user->level_id)
            ->get();

        // 4. Grouped Resources by Subject (for grouped view)
        $groupedResources = CourseResource::with(['subject', 'uploader'])
            ->whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)
                    ->where('level_id', $user->level_id);
            })
            ->latest()
            ->get()
            ->groupBy('subject_id');

        $viewMode = $request->get('view', 'table'); // table or grouped

        return view('delegate.resources.index', compact('resources', 'subjects', 'stats', 'groupedResources', 'viewMode', 'sort'));
    }

    public function create()
    {
        $user = Auth::user();

        // Fetch subjects for the list
        $subjects = Subject::where('major_id', $user->major_id)
            ->where('level_id', $user->level_id)
            ->get();

        return view('delegate.resources.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:lectures,references,summaries,exams,other',
            'file' => 'required|file|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar|max:10240', // 10MB max
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        // Determine file type category roughly for icon usage
        $fileType = $this->getFileType($extension);

        $path = $file->store('course_resources', 'public');

        CourseResource::create([
            'subject_id' => $request->subject_id,
            'created_by' => Auth::id(),
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $path,
            'file_type' => $extension, // Store exact extension
            'description' => $request->description,
        ]);

        return redirect()->route('delegate.resources.index')->with('success', 'تم رفع الملف بنجاح.');
    }

    public function destroy(CourseResource $resource)
    {
        if ($resource->created_by !== Auth::id()) {
            abort(403);
        }

        Storage::disk('public')->delete($resource->file_path);
        $resource->delete();

        return redirect()->back()->with('success', 'تم حذف الملف بنجاح.');
    }

    private function getFileType($extension)
    {
        $images = ['jpg', 'jpeg', 'png', 'gif'];
        $docs = ['pdf', 'doc', 'docx', 'txt'];
        $slides = ['ppt', 'pptx'];
        $archives = ['zip', 'rar'];

        if (in_array(strtolower($extension), $images)) return 'image';
        if (in_array(strtolower($extension), $docs)) return 'document';
        if (in_array(strtolower($extension), $slides)) return 'presentation';
        if (in_array(strtolower($extension), $archives)) return 'archive';

        return 'other';
    }
}
