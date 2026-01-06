<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user();
        $category = $request->query('category');

        $query = Announcement::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->latest();

        if ($category && in_array($category, ['academic', 'general', 'urgent'])) {
            $query->where('category', $category);
        }

        $announcements = $query->paginate(10);

        return view('student.announcements.index', compact('announcements', 'category'));
    }
}
