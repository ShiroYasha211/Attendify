<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseResource;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::orderBy('name')->get();
        $years = CourseResource::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $semesters = CourseResource::select('semester_info')->distinct()->pluck('semester_info')->filter()->values();
        $lecturers = CourseResource::select('lecturer_name')->distinct()->pluck('lecturer_name')->filter()->values();

        $filters = $request->only([
            'search', 'subject_id', 'category', 'sub_category', 
            'year', 'semester_info', 'lecturer_name', 'file_type', 'uploader_role'
        ]);

        $query = CourseResource::with(['subject', 'uploader'])
            ->filter($filters);

        $totalCount = CourseResource::count();
        $resources = $query->latest()->paginate(20)->withQueryString();

        return view('admin.library.index', compact('resources', 'subjects', 'years', 'semesters', 'lecturers', 'totalCount'));
    }

    public function edit(CourseResource $library)
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.library.edit', [
            'resource' => $library,
            'subjects' => $subjects
        ]);
    }

    public function update(Request $request, CourseResource $library)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:lectures,summaries,quizzes,exams,references,other',
            'sub_category' => 'nullable|string|in:theoretical,practical,seminar,other',
            'semester_info' => 'nullable|string|max:255',
            'lecturer_name' => 'nullable|string|max:255',
            'unit_coordinator' => 'nullable|string|max:255',
            'clinical_unit' => 'nullable|string|max:255',
            'visibility' => 'required|in:batch,college,everyone',
            'description' => 'nullable|string',
        ]);

        $library->update($request->all());

        return redirect()->route('admin.library.index')->with('success', 'تم تحديث بيانات الملف بنجاح.');
    }

    public function download(Request $request, CourseResource $resource)
    {
        if (!$request->isMethod('HEAD')) {
            $resource->increment('downloads_count');
        }
        return Storage::disk('public')->download($resource->file_path, $resource->title . '.' . $resource->file_type);
    }

    public function destroy(CourseResource $library)
    {
        // Delete physical file
        if (Storage::disk('public')->exists($library->file_path)) {
            Storage::disk('public')->delete($library->file_path);
        }

        $library->delete();

        return redirect()->route('admin.library.index')->with('success', 'تم حذف الملف نهائياً من المكتبة.');
    }
}
