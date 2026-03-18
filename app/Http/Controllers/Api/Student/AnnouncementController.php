<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends StudentApiController
{
    /**
     * Get Student Announcements
     */
    public function index(Request $request)
    {
        $student = $request->user();
        $category = $request->query('category');

        $baseQuery = Announcement::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'academic' => (clone $baseQuery)->where('category', 'academic')->count(),
            'general' => (clone $baseQuery)->where('category', 'general')->count(),
            'urgent' => (clone $baseQuery)->where('category', 'urgent')->count(),
        ];

        $pinnedAnnouncements = collect();
        if (\Illuminate\Support\Facades\Schema::hasColumn('announcements', 'is_pinned')) {
            $pinnedAnnouncements = (clone $baseQuery)
                ->where('is_pinned', true)
                ->latest()
                ->take(3)
                ->get();
        }

        $query = (clone $baseQuery)->latest();

        if ($category && in_array($category, ['academic', 'general', 'urgent'])) {
            $query->where('category', $category);
        }

        $announcements = $query->paginate(10);

        return $this->success([
            'stats' => $stats,
            'pinned' => $pinnedAnnouncements,
            'announcements' => $announcements,
        ]);
    }
}
