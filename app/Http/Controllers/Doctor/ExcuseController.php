<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Excuse;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcuseController extends Controller
{
    public function index()
    {
        // Get subjects for the authenticated doctor
        $subjectIds = Subject::where('doctor_id', Auth::id())->pluck('id');

        // Get pending excuses for these subjects
        $excuses = Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
            $q->whereIn('subject_id', $subjectIds);
        })
            ->where('status', 'pending')
            ->with(['student', 'attendance.subject'])
            ->latest()
            ->paginate(10);

        return view('doctor.excuses.index', compact('excuses'));
    }

    public function update(Request $request, Excuse $excuse)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
            'comment' => 'nullable|string|max:255',
        ]);

        // Ensure the doctor owns the subject related to this excuse
        if ($excuse->attendance->subject->doctor_id !== Auth::id()) {
            abort(403);
        }

        $excuse->update([
            'status' => $request->status,
            'doctor_comment' => $request->comment,
        ]);

        if ($request->status === 'accepted') {
            $excuse->attendance->update(['status' => 'excused']);
        }

        return back()->with('success', 'تم تحديث حالة العذر بنجاح.');
    }
}
