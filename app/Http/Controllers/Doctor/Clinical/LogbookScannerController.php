<?php

namespace App\Http\Controllers\Doctor\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Clinical\StudentDailyLog;
use App\Models\User;

class LogbookScannerController extends Controller
{
    /**
     * Show QR scanner page.
     */
    public function scanner()
    {
        return view('doctor.clinical.scanner');
    }

    /**
     * Process scanned QR token (AJAX).
     */
    public function processQr(Request $request)
    {
        $request->validate(['qr_token' => 'required|string']);

        $log = StudentDailyLog::with([
            'student',
            'trainingCenter',
            'department',
            'doctor',
            'activities.bodySystem'
        ])->where('qr_token', $request->qr_token)->first();

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'رمز QR غير صالح.']);
        }

        if ($log->status === 'confirmed') {
            return response()->json(['success' => false, 'message' => 'هذا السجل مؤكد بالفعل.']);
        }

        if ($log->isExpired()) {
            return response()->json(['success' => false, 'message' => 'انتهت صلاحية رمز QR (30 دقيقة).']);
        }

        // Separate activities
        $histories = $log->activities->where('activity_type', 'history_taking');
        $exams = $log->activities->where('activity_type', 'clinical_examination');
        $rounds = $log->activities->where('activity_type', 'round');

        return response()->json([
            'success' => true,
            'log_id' => $log->id,
            'data' => [
                'student_name' => $log->student->name ?? '-',
                'student_number' => $log->student->student_number ?? '-',
                'training_center' => $log->trainingCenter->name ?? '-',
                'department' => $log->department->name ?? '-',
                'doctor_name' => $log->doctor->name ?? '-',
                'log_date' => $log->log_date->format('Y-m-d'),
                'log_time' => $log->log_time,
                'history_count' => $log->history_count,
                'exam_count' => $log->exam_count,
                'did_round' => $log->did_round,
                'round_notes' => $log->round_notes,
                'histories' => $histories->map(fn($h) => [
                    'body_system' => $h->bodySystem->name ?? '-',
                ])->values(),
                'exams' => $exams->map(fn($e) => [
                    'body_system' => $e->bodySystem->name ?? '-',
                ])->values(),
                'rounds' => $rounds->map(fn($r) => [
                    'case_name' => $r->case_name,
                ])->values(),
            ],
        ]);
    }

    /**
     * Confirm or reject a daily log (AJAX) — this is the "4 signatures" action.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'log_id' => 'required|exists:student_daily_logs,id',
            'action' => 'required|in:confirm,reject',
            'doctor_notes' => 'nullable|string|max:1000',
        ]);

        $log = StudentDailyLog::findOrFail($request->log_id);

        if ($log->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'هذا السجل تمت معالجته بالفعل.']);
        }

        if ($request->action === 'confirm') {
            $log->update([
                'status' => 'confirmed',
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
                'doctor_notes' => $request->doctor_notes,
            ]);
            return response()->json(['success' => true, 'message' => 'تم تأكيد السجل بنجاح ✅ (حضور + قصص + فحوصات + مرور)']);
        } else {
            $log->update([
                'status' => 'rejected',
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
                'doctor_notes' => $request->doctor_notes,
            ]);
            return response()->json(['success' => true, 'message' => 'تم رفض السجل ❌']);
        }
    }

    /**
     * Manual attendance — doctor marks student as present without QR.
     */
    public function manualAttendance()
    {
        $doctorSubjects = \App\Models\Academic\Subject::where('doctor_id', Auth::id())
            ->select('major_id', 'level_id')
            ->distinct()
            ->get();

        $studentsQuery = User::whereIn('role', ['student', 'delegate']);
        if ($doctorSubjects->isNotEmpty()) {
            $studentsQuery->where(function ($query) use ($doctorSubjects) {
                foreach ($doctorSubjects as $subject) {
                    $query->orWhere(function ($q) use ($subject) {
                        $q->where('major_id', $subject->major_id)
                            ->where('level_id', $subject->level_id);
                    });
                }
            });
        } else {
            $studentsQuery->whereRaw('1 = 0');
        }
        $students = $studentsQuery->orderBy('name')->get();

        $trainingCenters = \App\Models\Clinical\TrainingCenter::all();
        $departments = \App\Models\Clinical\ClinicalDepartment::all();

        return view('doctor.clinical.manual_attendance', compact('students', 'trainingCenters', 'departments'));
    }

    /**
     * Store manual attendance.
     */
    public function storeManualAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'training_center_id' => 'required|exists:training_centers,id',
            'department_id' => 'required|exists:clinical_departments,id',
            'doctor_notes' => 'nullable|string|max:1000',
        ]);

        $log = StudentDailyLog::create([
            'student_id' => $request->student_id,
            'training_center_id' => $request->training_center_id,
            'department_id' => $request->department_id,
            'doctor_id' => Auth::id(),
            'qr_token' => StudentDailyLog::generateToken(),
            'status' => 'confirmed',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
            'doctor_notes' => $request->doctor_notes ?? 'تحضير يدوي',
            'log_date' => now()->toDateString(),
            'log_time' => now()->toTimeString(),
        ]);

        return redirect()->back()->with('success', 'تم تسجيل حضور الطالب يدوياً ✅');
    }

    /**
     * View all daily log records.
     */
    public function records(Request $request)
    {
        $query = StudentDailyLog::with(['student', 'trainingCenter', 'department', 'confirmedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('log_date', $request->date);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('doctor.clinical.logbook_records', compact('logs'));
    }
}
