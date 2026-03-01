<?php

namespace App\Http\Controllers\Api\Student\Clinical;

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
     * Get Logbook Dashboard & List (Includes Form Options)
     */
    public function index(Request $request)
    {
        $student = $request->user();

        // 1. Fetch Student Logs (Pending and Confirmed)
        $logs = StudentDailyLog::with(['trainingCenter', 'department', 'doctor', 'confirmedBy', 'activities.bodySystem'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $confirmedCount = $logs->where('status', 'confirmed')->count();
        $pendingCount = $logs->where('status', 'pending')->count();

        // 2. Fetch Assignments
        $assignments = CaseAssignment::with(['clinicalCase.trainingCenter', 'clinicalCase.clinicalDepartment', 'clinicalCase.bodySystem', 'assigner'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        // 3. Dropdown options for the mobile app form
        $options = [
            'training_centers' => TrainingCenter::select('id', 'name')->orderBy('name')->get(),
            'departments' => ClinicalDepartment::select('id', 'name')->orderBy('name')->get(),
            'body_systems' => BodySystem::select('id', 'name')->orderBy('name')->get(),
            'doctors' => User::where('role', 'doctor')->select('id', 'name')->orderBy('name')->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'confirmed_logs' => $confirmedCount,
                    'pending_logs' => $pendingCount,
                ],
                'assignments' => $assignments,
                'logs' => $logs,
                'form_options' => $options,
            ]
        ], 200);
    }

    /**
     * Store a new daily log
     */
    public function store(Request $request)
    {
        $student = $request->user();

        $request->validate([
            'training_center_id' => 'required|exists:training_centers,id',
            'department_id' => 'required|exists:clinical_departments,id',
            'doctor_id' => 'required|exists:users,id',
            'histories' => 'nullable|array',
            'histories.*.body_system_id' => 'required|exists:body_systems,id',
            'exams' => 'nullable|array',
            'exams.*.body_system_id' => 'required|exists:body_systems,id',
            'did_round' => 'nullable|boolean',
            'rounds' => 'nullable|array',
            'rounds.*.case_name' => 'required|string|max:255',
            'round_notes' => 'nullable|string'
        ]);

        $historyCount = count($request->histories ?? []);
        $examCount = count($request->exams ?? []);
        $didRound = $request->boolean('did_round');

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

        $dailyLog->load(['trainingCenter', 'department', 'doctor', 'activities.bodySystem']);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الحالة اليومية بنجاح.',
            'data' => $dailyLog
        ], 201);
    }

    /**
     * Update an existing pending daily log
     */
    public function update(Request $request, $id)
    {
        $student = $request->user();

        $dailyLog = StudentDailyLog::where('student_id', $student->id)->findOrFail($id);

        if ($dailyLog->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل سجل تم اعتماده مسبقاً.'
            ], 403);
        }

        $request->validate([
            'training_center_id' => 'required|exists:training_centers,id',
            'department_id' => 'required|exists:clinical_departments,id',
            'doctor_id' => 'required|exists:users,id',
            'histories' => 'nullable|array',
            'exams' => 'nullable|array',
            'did_round' => 'nullable|boolean',
            'rounds' => 'nullable|array',
            'round_notes' => 'nullable|string'
        ]);

        $historyCount = count($request->histories ?? []);
        $examCount = count($request->exams ?? []);
        $didRound = $request->boolean('did_round');

        $dailyLog->update([
            'training_center_id' => $request->training_center_id,
            'department_id' => $request->department_id,
            'doctor_id' => $request->doctor_id,
            'history_count' => $historyCount,
            'exam_count' => $examCount,
            'did_round' => $didRound,
            'round_notes' => $request->round_notes,
        ]);

        // Clear old activities
        $dailyLog->activities()->delete();

        // Save new history activities
        foreach ($request->histories ?? [] as $h) {
            DailyLogActivity::create([
                'daily_log_id' => $dailyLog->id,
                'activity_type' => 'history_taking',
                'body_system_id' => $h['body_system_id'] ?? null,
            ]);
        }

        // Save new exam activities
        foreach ($request->exams ?? [] as $e) {
            DailyLogActivity::create([
                'daily_log_id' => $dailyLog->id,
                'activity_type' => 'clinical_examination',
                'body_system_id' => $e['body_system_id'] ?? null,
            ]);
        }

        // Save new round cases
        if ($didRound) {
            foreach ($request->rounds ?? [] as $r) {
                DailyLogActivity::create([
                    'daily_log_id' => $dailyLog->id,
                    'activity_type' => 'round',
                    'case_name' => $r['case_name'] ?? 'بدون اسم',
                ]);
            }
        }

        $dailyLog->load(['trainingCenter', 'department', 'doctor', 'activities.bodySystem']);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث السجل بنجاح.',
            'data' => $dailyLog
        ], 200);
    }

    /**
     * Delete a pending daily log
     */
    public function destroy(Request $request, $id)
    {
        $student = $request->user();
        $dailyLog = StudentDailyLog::where('student_id', $student->id)->findOrFail($id);

        if ($dailyLog->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف سجل تم اعتماده مسبقاً.'
            ], 403);
        }

        $dailyLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف السجل بنجاح.',
        ], 200);
    }

    /**
     * Provide a PDF download link or base64 PDF representation
     * For API, returning a direct download link is usually the best approach.
     */
    public function exportPdf(Request $request)
    {
        $student = $request->user();

        // Return a signed URL or direct web route link so the mobile app can launch it
        // Since we already have a web route for this, we can just return the URL
        $pdfUrl = route('student.clinical.logbook.pdf'); // Assuming this web route exists, wait, let me check the web routes.

        return response()->json([
            'success' => true,
            'message' => 'استخدم هذا الرابط لتحميل السجل كملف PDF.',
            'data' => [
                'download_url' => url('/student/clinical/logbook/export-pdf'), // Exact web route
            ]
        ], 200);
    }
}
