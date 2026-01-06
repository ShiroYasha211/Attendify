<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reminder;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    public function index()
    {
        $delegate = Auth::user();

        $reminders = Reminder::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->orderBy('event_date', 'asc') // Sort by nearest event
            ->paginate(10);

        return view('delegate.reminders.index', compact('reminders'));
    }

    public function store(Request $request)
    {
        $delegate = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'notify_at' => 'required|date',
        ]);

        Reminder::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'event_date' => $validated['event_date'],
            'notify_at' => $validated['notify_at'],
            'major_id' => $delegate->major_id,
            'level_id' => $delegate->level_id,
            'created_by' => $delegate->id,
        ]);

        return redirect()->back()->with('success', 'تم جدولة التذكير بنجاح.');
    }

    public function update(Request $request, Reminder $reminder)
    {
        $delegate = Auth::user();

        if ($reminder->created_by != $delegate->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'notify_at' => 'required|date',
        ]);

        $reminder->update($validated);

        return redirect()->back()->with('success', 'تم تحديث التذكير.');
    }

    public function destroy(Reminder $reminder)
    {
        $delegate = Auth::user();

        if ($reminder->created_by != $delegate->id) {
            abort(403);
        }

        $reminder->delete();

        return redirect()->back()->with('success', 'تم حذف التذكير.');
    }
}
