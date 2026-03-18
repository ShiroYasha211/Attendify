<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use App\Models\Academic\Subject;
use App\Models\CourseResource;

class ResourceController extends StudentApiController
{
    /**
     * Get Student Resources
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_files' => CourseResource::whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)->where('level_id', $user->level_id);
            })->count(),
            'recent_week' => CourseResource::whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)->where('level_id', $user->level_id);
            })->where('created_at', '>=', now()->subWeek())->count(),
        ];

        $query = CourseResource::with(['subject:id,name', 'uploader:id,name'])
            ->whereHas('subject', function ($q) use ($user) {
                $q->where('major_id', $user->major_id)
                    ->where('level_id', $user->level_id);
            });

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('scheduled') && $request->scheduled == '1') {
            $query->whereHas('scheduleItems', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

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
            default:
                $query->latest();
        }

        $resources = $query->paginate(15);

        // Fetch subjects for filters
        $subjects = Subject::where('major_id', $user->major_id)
            ->where('level_id', $user->level_id)
            ->select('id', 'name')
            ->get();

        return $this->success([
            'stats' => $stats,
            'subjects' => $subjects,
            'resources' => $resources,
        ]);
    }
}
