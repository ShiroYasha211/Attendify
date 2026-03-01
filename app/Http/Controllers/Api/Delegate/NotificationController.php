<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Attendance;
use App\Models\StudentNotification;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NotificationController extends DelegateApiController
{
    /**
     * Display a listing of absence statistics and sent notifications.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        // 1. Get Absence Statistics (Optimized query as in web version)
        $absenceStats = DB::table('attendances as a')
            ->join('users as u', 'a.student_id', '=', 'u.id')
            ->join('subjects as s', 'a.subject_id', '=', 's.id')
            ->where('s.major_id', $delegate->major_id)
            ->where('s.level_id', $delegate->level_id)
            ->where('a.status', 'absent')
            ->select(
                'u.id as student_id',
                'u.name as student_name',
                'u.university_id as student_university_id',
                's.id as subject_id',
                's.name as subject_name',
                DB::raw('COUNT(a.id) as absence_count')
            )
            ->groupBy('u.id', 'u.name', 'u.university_id', 's.id', 's.name')
            ->orderByDesc('absence_count')
            ->get();

        // 2. Get history of sent warnings
        $sentWarnings = StudentNotification::where('sender_id', $delegate->id)
            ->where('type', 'absence_warning')
            ->with(['student:id,name,university_id'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'absence_stats' => $absenceStats,
            'sent_warnings' => $sentWarnings,
        ], 'تم جلب إحصائيات الغياب المحدثة بنجاح');
    }

    /**
     * Send an absence warning notification to a student.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'absence_count' => 'required|integer|min:1',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        // Validate student belongs to batch
        $student = User::where('id', $request->student_id)
            ->where('role', UserRole::STUDENT)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$student) {
            return $this->error('الطالب غير موجود في دفعتك', 403);
        }

        $notification = StudentNotification::create([
            'student_id' => $request->student_id,
            'sender_id' => $delegate->id,
            'title' => 'إنذار غياب',
            'message' => $request->message,
            'type' => 'absence_warning',
            'subject_id' => $request->subject_id,
        ]);

        return $this->success($notification, 'تم إرسال إنذار الغياب بنجاح', 201);
    }
}
