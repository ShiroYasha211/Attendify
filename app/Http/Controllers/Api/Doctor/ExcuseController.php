<?php

namespace App\Http\Controllers\Api\Doctor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Excuse;
use App\Models\Academic\Subject;
use App\Models\StudentNotification;

class ExcuseController extends DoctorApiController
{
    /** GET /api/doctor/excuses */
    public function index(Request $request)
    {
        $subjectIds = Subject::where('doctor_id', Auth::id())->pluck('id');

        $query = Excuse::whereHas('attendance', fn($q) => $q->whereIn('subject_id', $subjectIds))
            ->with(['student:id,name,student_number', 'attendance.subject:id,name']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('subject_id')) {
            $query->whereHas('attendance', fn($q) => $q->where('subject_id', $request->subject_id));
        }
        if ($request->filled('search')) {
            $query->whereHas('student', fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('student_number', 'like', "%{$request->search}%"));
        }

        $excuses = $query->latest()->paginate(15);

        // Stats (single query)
        $statsRaw = Excuse::whereHas('attendance', fn($q) => $q->whereIn('subject_id', $subjectIds))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            ")->first();

        return $this->success([
            'stats' => [
                'total' => $statsRaw->total ?? 0,
                'pending' => $statsRaw->pending ?? 0,
                'accepted' => $statsRaw->accepted ?? 0,
                'rejected' => $statsRaw->rejected ?? 0,
            ],
            'excuses' => $excuses->items(),
            'pagination' => [
                'current_page' => $excuses->currentPage(),
                'last_page' => $excuses->lastPage(),
                'per_page' => $excuses->perPage(),
                'total' => $excuses->total(),
            ],
        ]);
    }

    /** PUT /api/doctor/excuses/{excuse} */
    public function update(Request $request, Excuse $excuse)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
            'comment' => 'nullable|string|max:255',
        ]);

        if ($excuse->attendance->subject->doctor_id !== Auth::id()) {
            return $this->error('غير مصرح لك.', 403);
        }

        $excuse->update([
            'status' => $request->status,
            'doctor_comment' => $request->comment,
        ]);

        if ($request->status === 'accepted') {
            $excuse->attendance->update(['status' => 'excused']);
        }

        $subjectName = $excuse->attendance->subject->name ?? 'غير محدد';
        $statusLabel = $request->status === 'accepted' ? 'قبول' : 'رفض';
        $statusIcon = $request->status === 'accepted' ? '✅' : '❌';

        StudentNotification::create([
            'user_id' => $excuse->student_id,
            'type' => 'excuse',
            'title' => "{$statusIcon} تم {$statusLabel} العذر",
            'message' => "تم {$statusLabel} عذرك في مادة {$subjectName} بتاريخ {$excuse->attendance->date->format('Y-m-d')}.",
            'data' => ['excuse_id' => $excuse->id, 'status' => $request->status],
        ]);

        return $this->success(null, 'تم تحديث حالة العذر بنجاح.');
    }
}
