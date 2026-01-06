<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Excuse;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ExcuseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // 2MB Max
        ]);

        $student = Auth::user();
        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // 1. Check if eligible (Absent)
        if ($attendance->status !== 'absent') {
            return back()->with('error', 'لا يمكن تقديم عذر لمحاضرة لست غائباً فيها.');
        }

        // 2. Check deadline (7 days)
        $lectureDate = Carbon::parse($attendance->date);
        $deadline = $lectureDate->copy()->addDays(7);
        if (now()->gt($deadline)) {
            return back()->with('error', 'عذراً، انتهت المهلة المحددة لتقديم العذر (أسبوع من تاريخ الغياب).');
        }

        // 3. Check if already submitted
        if ($attendance->excuse) {
            return back()->with('error', 'لقد قمت بتقديم عذر مسبقاً لهذا الغياب.');
        }

        // Handle File Upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('excuses', 'public');
        }

        // Create Excuse
        Excuse::create([
            'attendance_id' => $attendance->id,
            'student_id' => $student->id,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return back()->with('success', 'تم تقديم العذر بنجاح، بانتظار موافقة الدكتور.');
    }
}
