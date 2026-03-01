<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinical\StudentDailyLog;
use App\Models\User;

class LogbookController extends DoctorApiController
{
    /** POST /api/doctor/clinical/logbook/scan */
    public function processQr(Request $request)
    {
        $request->validate(['qr_token' => 'required|string']);

        $log = StudentDailyLog::with(['student', 'trainingCenter', 'department', 'doctor', 'activities.bodySystem'])
            ->where('qr_token', $request->qr_token)->first();

        if (!$log) {
            return $this->error('رمز QR غير صالح.', 404);
        }
        if ($log->status === 'confirmed') {
            return $this->error('هذا السجل مؤكد بالفعل.', 422);
        }
        if ($log->isExpired()) {
            return $this->error('انتهت صلاحية رمز QR (30 دقيقة).', 422);
        }

        $histories = $log->activities->where('activity_type', 'history_taking');
        $exams = $log->activities->where('activity_type', 'clinical_examination');

        return $this->success([
            'log_id' => $log->id,
            'student_name' => $log->student?->name,
            'student_number' => $log->student?->student_number,
            'training_center' => $log->trainingCenter?->name,
            'department' => $log->department?->name,
            'doctor_name' => $log->doctor?->name,
            'log_date' => $log->log_date->format('Y-m-d'),
            'log_time' => $log->log_time,
            'history_count' => $log->history_count,
            'exam_count' => $log->exam_count,
            'did_round' => $log->did_round,
            'round_notes' => $log->round_notes,
            'histories' => $histories->map(fn($h) => ['body_system' => $h->bodySystem?->name])->values(),
            'exams' => $exams->map(fn($e) => ['body_system' => $e->bodySystem?->name])->values(),
        ]);
    }

    /** POST /api/doctor/clinical/logbook/confirm */
    public function confirm(Request $request)
    {
        $request->validate([
            'log_id' => 'required|exists:student_daily_logs,id',
            'action' => 'required|in:confirm,reject',
            'doctor_notes' => 'nullable|string|max:1000',
        ]);

        $log = StudentDailyLog::findOrFail($request->log_id);

        if ($log->status !== 'pending') {
            return $this->error('هذا السجل تمت معالجته بالفعل.', 422);
        }

        $log->update([
            'status' => $request->action === 'confirm' ? 'confirmed' : 'rejected',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
            'doctor_notes' => $request->doctor_notes,
        ]);

        $msg = $request->action === 'confirm' ? 'تم تأكيد السجل بنجاح ✅' : 'تم رفض السجل ❌';
        return $this->success(null, $msg);
    }

    /** GET /api/doctor/clinical/logbook/records */
    public function records(Request $request)
    {
        $query = StudentDailyLog::with(['student:id,name,student_number', 'trainingCenter', 'department', 'confirmedBy:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('log_date', $request->date);
        }

        return $this->paginated($query->latest()->paginate(20));
    }

    /** POST /api/doctor/clinical/logbook/manual */
    public function manualAttendance(Request $request)
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

        return $this->success($log, 'تم تسجيل حضور الطالب يدوياً ✅', 201);
    }
}
