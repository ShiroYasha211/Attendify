<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Excuse;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcuseController extends Controller
{
    public function index(Request $request)
    {
        // Get subjects for the authenticated doctor
        $subjectIds = Subject::where('doctor_id', Auth::id())->pluck('id');

        // Get filter status
        $status = $request->get('status', 'pending');

        // Base query
        $query = Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
            $q->whereIn('subject_id', $subjectIds);
        })->with(['student', 'attendance.subject']);

        // Apply status filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $excuses = $query->latest()->paginate(15);

        // Stats
        $stats = [
            'all' => Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
                $q->whereIn('subject_id', $subjectIds);
            })->count(),
            'pending' => Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
                $q->whereIn('subject_id', $subjectIds);
            })->where('status', 'pending')->count(),
            'accepted' => Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
                $q->whereIn('subject_id', $subjectIds);
            })->where('status', 'accepted')->count(),
            'rejected' => Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
                $q->whereIn('subject_id', $subjectIds);
            })->where('status', 'rejected')->count(),
        ];

        return view('doctor.excuses.index', compact('excuses', 'stats', 'status'));
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
