<?php

namespace App\Http\Controllers\Student\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Clinical\StudentDailyLog;
use App\Models\Clinical\DailyLogActivity;
use App\Models\Clinical\CaseAssignment;
use App\Models\Clinical\TrainingCenter;
use App\Models\Clinical\ClinicalDepartment;
use App\Models\Clinical\BodySystem;
use App\Models\User;

class LogbookController extends Controller
{
    /**
     * Student Clinical Dashboard — show daily logs and assignments.
     */
    public function index()
    {
        $student = Auth::user();

        $assignments = CaseAssignment::with(['clinicalCase.trainingCenter', 'clinicalCase.clinicalDepartment', 'clinicalCase.bodySystem', 'assigner'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $confirmedCount = StudentDailyLog::where('student_id', $student->id)->confirmed()->count();
        $pendingCount   = StudentDailyLog::where('student_id', $student->id)->pending()->count();

        $pendingLogs = StudentDailyLog::with(['trainingCenter', 'department', 'doctor'])
            ->where('student_id', $student->id)
            ->pending()
            ->latest()
            ->get();

        return view('student.clinical.index', compact('assignments', 'confirmedCount', 'pendingCount', 'pendingLogs'));
    }

    /**
     * Show the daily log form — student fills in all activities before generating QR.
     */
    public function createDailyLog()
    {
        $student = Auth::user();
        $centers = TrainingCenter::orderBy('name')->get();
        $departments = ClinicalDepartment::orderBy('name')->get();
        $bodySystems = BodySystem::orderBy('name')->get();
        $doctors = User::where('role', 'doctor')->orderBy('name')->get();

        return view('student.clinical.create_daily_log', compact('centers', 'departments', 'bodySystems', 'doctors'));
    }

    /**
     * Store the daily log and generate QR code.
     */
    public function storeDailyLog(Request $request)
    {
        $student = Auth::user();

        $request->validate([
            'training_center_id' => 'required|exists:training_centers,id',
            'department_id' => 'required|exists:clinical_departments,id',
            'doctor_id' => 'required|exists:users,id',
            'histories' => 'nullable|array',
            'histories.*.body_system_id' => 'required|exists:body_systems,id',
            'exams' => 'nullable|array',
            'exams.*.body_system_id' => 'required|exists:body_systems,id',
            'did_round' => 'nullable',
            'rounds' => 'nullable|array',
            'rounds.*.case_name' => 'required|string|max:255',
        ]);

        $historyCount = count($request->histories ?? []);
        $examCount = count($request->exams ?? []);
        $didRound = $request->has('did_round');

        $dailyLog = StudentDailyLog::create([
            'student_id' => $student->id,
            'training_center_id' => $request->training_center_id,
            'department_id' => $request->department_id,
            'doctor_id' => $request->doctor_id,
            'history_count' => $historyCount,
            'exam_count' => $examCount,
            'did_round' => $didRound,
            'round_notes' => $request->round_notes,
            'qr_token' => StudentDailyLog::generateToken(),
            'status' => 'pending',
            'log_date' => now()->toDateString(),
            'log_time' => now()->toTimeString(),
        ]);

        // Save individual history activities
        foreach ($request->histories ?? [] as $h) {
            DailyLogActivity::create([
                'daily_log_id' => $dailyLog->id,
                'activity_type' => 'history_taking',
                'body_system_id' => $h['body_system_id'],
            ]);
        }

        // Save exam activities
        foreach ($request->exams ?? [] as $e) {
            DailyLogActivity::create([
                'daily_log_id' => $dailyLog->id,
                'activity_type' => 'clinical_examination',
                'body_system_id' => $e['body_system_id'],
            ]);
        }

        // Save round cases
        if ($didRound) {
            foreach ($request->rounds ?? [] as $r) {
                DailyLogActivity::create([
                    'daily_log_id' => $dailyLog->id,
                    'activity_type' => 'round',
                    'case_name' => $r['case_name'],
                ]);
            }
        }

        return redirect()->route('student.clinical.show-qr', $dailyLog->id);
    }

    /**
     * Show the generated QR code for a daily log.
     */
    public function showQr($id)
    {
        $student = Auth::user();
        $dailyLog = StudentDailyLog::with(['trainingCenter', 'department', 'doctor', 'activities.bodySystem'])
            ->where('student_id', $student->id)
            ->findOrFail($id);

        return view('student.clinical.show_qr', compact('dailyLog'));
    }

    /**
     * View the student's logbook (confirmed daily logs).
     */
    public function myLogbook()
    {
        $student = Auth::user();

        $entries = StudentDailyLog::with(['trainingCenter', 'department', 'doctor', 'confirmedBy', 'activities.bodySystem'])
            ->where('student_id', $student->id)
            ->latest()
            ->paginate(20);

        return view('student.clinical.logbook', compact('entries'));
    }

    /**
     * Regenerate QR token for an expired daily log.
     */
    public function regenerateQr($id)
    {
        $student = Auth::user();
        $dailyLog = StudentDailyLog::where('student_id', $student->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $dailyLog->update([
            'qr_token' => StudentDailyLog::generateToken(),
            'created_at' => now(),
        ]);

        return redirect()->route('student.clinical.show-qr', $dailyLog->id)
            ->with('success', 'تم تجديد الباركود بنجاح ✅');
    }

    /**
     * Cancel and delete an expired/pending daily log.
     */
    public function cancelDailyLog($id)
    {
        $student = Auth::user();
        $dailyLog = StudentDailyLog::where('student_id', $student->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        // Delete activities first
        $dailyLog->activities()->delete();
        $dailyLog->delete();

        return redirect()->route('student.clinical.index')
            ->with('success', 'تم حذف السجل بنجاح ✅');
    }

    /**
     * Export the student's confirmed logbook as a PDF document.
     */
    public function exportPdf()
    {
        $student = Auth::user();

        $entries = StudentDailyLog::with(['trainingCenter', 'department', 'doctor', 'confirmedBy', 'activities.bodySystem'])
            ->where('student_id', $student->id)
            ->where('status', 'confirmed')
            ->orderBy('log_date', 'asc')
            ->get();

        if ($entries->isEmpty()) {
            return redirect()->back()->with('error', 'لا يوجد أي سجلات معتمدة لتصديرها.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.clinical.pdf.student_logbook', compact('student', 'entries'));

        // Optional: configure PDF for Arabic (RTL) support if needed, DomPDF needs specific font setup for Arabic which should be configured in laravel-dompdf config.

        return $pdf->download('Clinical_Logbook_' . $student->university_id . '.pdf');
    }
}
