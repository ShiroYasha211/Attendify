<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseResource;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Fetch Subjects for Filter
        $subjects = Subject::where('major_id', $user->major_id)
            ->orderBy('level_id')
            ->orderBy('name')
            ->get();

        // 2. Fetch Years (Distinct Creation Years)
        $years = CourseResource::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // 3. Apply Filters
        $filters = $request->only(['search', 'level_id', 'subject_id', 'category', 'year']);
        $filters['major_id'] = $user->major_id;

        $query = CourseResource::with(['subject', 'uploader'])
            ->filter($filters);

        // Filter by My Uploads
        if ($request->has('my_uploads')) {
            $query->where('created_by', $user->id);
        }

        // 4. Sorting
        $sort = $request->get('sort', 'newest');
        $query->latest();

        // 5. View Mode
        $viewMode = $request->get('view', 'by_subject'); // table | by_subject | by_year

        $resources = null;
        $groupedResources = null;
        $totalCount = 0;

        if ($viewMode === 'table') {
            $resources = $query->paginate(15)->withQueryString();
            $totalCount = $resources->total();
        } else {
            $allResources = $query->get();
            $totalCount = $allResources->count();

            if ($viewMode === 'by_year') {
                $groupedResources = $allResources->groupBy(function ($item) {
                    return $item->created_at->format('Y');
                })->sortKeysDesc();
            } else {
                // by_subject (default)
                $groupedResources = $allResources->groupBy(function ($item) {
                    return $item->subject->name ?? 'أخرى';
                })->sortKeys();
            }
        }

        return view('student.library.index', compact(
            'resources',
            'groupedResources',
            'subjects',
            'years',
            'viewMode',
            'totalCount'
        ));
    }

    public function incrementDownload(CourseResource $resource)
    {
        // $resource->increment('downloads_count'); // Disabled until migration is run
        return response()->json(['success' => true]);
    }
}
