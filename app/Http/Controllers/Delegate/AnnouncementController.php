<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $delegate = Auth::user();

        $announcements = Announcement::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->latest()
            ->paginate(10);

        return view('delegate.announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $delegate = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:academic,general,urgent',
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'major_id' => $delegate->major_id,
            'level_id' => $delegate->level_id,
            'created_by' => $delegate->id,
        ]);

        return redirect()->back()->with('success', 'تم نشر الخبر بنجاح.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $delegate = Auth::user();

        if ($announcement->created_by != $delegate->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:academic,general,urgent',
        ]);

        $announcement->update($validated);

        return redirect()->back()->with('success', 'تم تحديث الخبر بنجاح.');
    }

    public function destroy(Announcement $announcement)
    {
        $delegate = Auth::user();

        if ($announcement->created_by != $delegate->id) {
            abort(403);
        }

        $announcement->delete();

        return redirect()->back()->with('success', 'تم حذف الخبر.');
    }
}
