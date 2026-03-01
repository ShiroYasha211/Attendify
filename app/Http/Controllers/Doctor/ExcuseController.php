<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Excuse;
use App\Models\Academic\Subject;
use App\Models\StudentNotification;
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

        // Apply subject filter
        $subjectId = $request->get('subject');
        if ($subjectId && $subjectId !== 'all') {
            $query->whereHas('attendance', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        // Apply search filter (by student name or number)
        $search = $request->get('search');
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        $excuses = $query->latest()->paginate(15)->withQueryString();

        // Optimized: single query with conditional counting (was 4 queries)
        $statsRaw = Excuse::whereHas('attendance', function ($q) use ($subjectIds) {
            $q->whereIn('subject_id', $subjectIds);
        })->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

        $stats = [
            'all' => $statsRaw->total ?? 0,
            'pending' => $statsRaw->pending ?? 0,
            'accepted' => $statsRaw->accepted ?? 0,
            'rejected' => $statsRaw->rejected ?? 0,
        ];

        $doctorSubjects = Subject::whereIn('id', $subjectIds)->get();

        return view('doctor.excuses.index', compact('excuses', 'stats', 'status', 'doctorSubjects', 'subjectId', 'search'));
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

        // Notify student about excuse decision
        $subjectName = $excuse->attendance->subject->name ?? 'غير محدد';
        $statusLabel = $request->status === 'accepted' ? 'قبول' : 'رفض';
        $statusIcon = $request->status === 'accepted' ? '✅' : '❌';

        StudentNotification::create([
            'user_id' => $excuse->student_id,
            'type'    => 'excuse',
            'title'   => "{$statusIcon} تم {$statusLabel} العذر",
            'message' => "تم {$statusLabel} عذرك في مادة {$subjectName} بتاريخ {$excuse->attendance->date->format('Y-m-d')}.",
            'data'    => [
                'excuse_id'  => $excuse->id,
                'status'     => $request->status,
                'action_url' => route('student.attendance.index'),
            ],
        ]);

        return back()->with('success', 'تم تحديث حالة العذر بنجاح.');
    }
}
