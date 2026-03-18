<?php

namespace App\Http\Controllers\Administrative;

use App\Http\Controllers\Controller;
use App\Models\Excuse;
use App\Models\StudentNotification;
use App\Models\Academic\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcuseController extends Controller
{
    /**
     * Display a listing of excuses for the college.
     */
    public function index(Request $request)
    {
        $college = auth()->user()->college;

        // Base query: get excuses for students in this college
        $query = Excuse::whereHas('student', function ($q) use ($college) {
            $q->where('college_id', $college->id);
        })->with(['student', 'attendance.subject']);

        // Apply status filter
        $status = $request->get('status', 'pending');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Apply search filter
        $search = $request->get('search');
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        $excuses = $query->latest()->paginate(15)->withQueryString();

        // Stats
        $statsRaw = Excuse::whereHas('student', function ($q) use ($college) {
            $q->where('college_id', $college->id);
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

        return view('administrative.excuses.index', compact('excuses', 'stats', 'status', 'search', 'college'));
    }

    /**
     * Update the specified excuse status.
     */
    public function update(Request $request, Excuse $excuse)
    {
        $college = auth()->user()->college;
        
        // Ensure the excuse belongs to a student in this college
        if ($excuse->student->college_id !== $college->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:accepted,rejected',
            'comment' => 'nullable|string|max:255',
        ]);

        $excuse->update([
            'status' => $request->input('status'),
            'doctor_comment' => $request->input('comment'), // Reusing this field for admin comments
        ]);

        if ($request->status === 'accepted') {
            $excuse->attendance->update(['status' => 'excused']);
        }

        // Notify student
        $subjectName = $excuse->attendance->subject->name ?? 'غير محدد';
        $statusLabel = $request->status === 'accepted' ? 'قبول' : 'رفض';
        $statusIcon = $request->status === 'accepted' ? '✅' : '❌';

        $message = "تم {$statusLabel} عذرك المُقدم لمادة {$subjectName} (غياب {$excuse->attendance->date->format('Y-m-d')}) من قِبل إدارة الكلية.";
        if ($request->input('comment')) {
            $message .= "\nملاحظة الإدارة: " . $request->input('comment');
        }

        StudentNotification::create([
            'user_id' => $excuse->student_id,
            'type'    => 'excuse',
            'title'   => "{$statusIcon} حالة العذر لمنصة الكلية",
            'message' => $message,
            'data'    => [
                'excuse_id'  => $excuse->id,
                'status'     => $request->status,
                'action_url' => route('student.subjects.show', $excuse->attendance->subject_id),
            ],
        ]);

        return back()->with('success', 'تم الاستجابة للعذر بنجاح.');
    }
}
