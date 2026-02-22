<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\QrAttendanceSession;
use App\Models\Attendance;
use App\Models\Academic\Lecture;
use App\Models\User;
use App\Enums\UserRole;

class QrAttendanceController extends Controller
{
    /**
     * ──────────────────────────────────────────────────
     * 1. START SESSION (Delegate Only)
     * ──────────────────────────────────────────────────
     * Creates a new QR attendance session for a subject.
     * Returns session_id and first token for QR generation.
     *
     * POST /api/qr-attendance/start
     * Body: { subject_id, date, title, lecture_number? }
     */
    public function startSession(Request $request)
    {
        try {
            $user = $request->user();

            // Only delegates can start sessions
            // Ensure $user is not null
            if (!$user) {
                return response()->json(['message' => 'User not found. Please login.'], 401);
            }

            if (!$user->hasRole(UserRole::DELEGATE)) {
                return response()->json(['message' => 'غير مصرح لك بهذا الإجراء.'], 403);
            }

            $validated = $request->validate([
                'subject_id'     => 'required|exists:subjects,id',
                'date'           => 'required|date',
                'title'          => 'required|string|max:255',
                'lecture_number' => 'nullable|string|max:50',
            ]);

            // Check if session already exists for this subject+date
            $existing = QrAttendanceSession::where('subject_id', $validated['subject_id'])
                ->where('date', $validated['date'])
                ->first();

            if ($existing) {
                // If already finalized, can't restart
                if ($existing->status === 'finalized') {
                    return response()->json([
                        'message' => 'تم إنهاء جلسة الحضور لهذا اليوم بالفعل. لا يمكن إعادة البدء.',
                    ], 409);
                }

                // If active, return existing session
                $existing->rotateToken(); // Generate fresh token
                return response()->json([
                    'message'    => 'الجلسة موجودة مسبقاً، تم تجديد الكود.',
                    'session_id' => $existing->id,
                    'token'      => $existing->current_token,
                    'expires_at' => $existing->token_expires_at->toIso8601String(),
                ]);
            }

            // Generate first token
            $firstToken = Str::random(48) . bin2hex(random_bytes(8));

            $session = QrAttendanceSession::create([
                'subject_id'       => $validated['subject_id'],
                'delegate_id'      => $user->id,
                'date'             => $validated['date'],
                'title'            => $validated['title'],
                'lecture_number'   => $validated['lecture_number'] ?? null,
                'current_token'    => $firstToken,
                'token_expires_at' => Carbon::now()->addSeconds(30),
                'status'           => 'active',
            ]);

            return response()->json([
                'message'    => 'تم بدء جلسة الحضور بنجاح.',
                'session_id' => $session->id,
                'token'      => $session->current_token,
                'expires_at' => $session->token_expires_at->toIso8601String(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server Error: ' . $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * ──────────────────────────────────────────────────
     * 2. ROTATE TOKEN (Delegate Only)
     * ──────────────────────────────────────────────────
     * Generates a new token, invalidating the old one.
     * Called every 10 seconds by the delegate's app.
     *
     * GET /api/qr-attendance/{session}/token
     */
    public function rotateToken(Request $request, QrAttendanceSession $session)
    {
        $user = $request->user();

        // Verify ownership
        if ($session->delegate_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }

        if (!$session->isActive()) {
            return response()->json(['message' => 'الجلسة منتهية.'], 410);
        }

        $newToken = $session->rotateToken();

        return response()->json([
            'token'      => $newToken,
            'expires_at' => $session->token_expires_at->toIso8601String(),
        ]);
    }

    /**
     * ──────────────────────────────────────────────────
     * 3. GET STATUS (Delegate Only)
     * ──────────────────────────────────────────────────
     * Returns list of students who have scanned so far.
     *
     * GET /api/qr-attendance/{session}/status
     */
    public function getStatus(Request $request, QrAttendanceSession $session)
    {
        $user = $request->user();

        if ($session->delegate_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }

        // Get all students in the same scope
        $allStudents = User::where('role', UserRole::STUDENT)
            ->where('major_id', $user->major_id)
            ->where('level_id', $user->level_id)
            ->orderBy('name')
            ->get(['id', 'name', 'student_number']);

        // Get attendance records for this session
        $attendanceRecords = Attendance::where('subject_id', $session->subject_id)
            ->where('date', $session->date)
            ->get()
            ->keyBy('student_id');

        $students = $allStudents->map(function ($student) use ($attendanceRecords) {
            $record = $attendanceRecords->get($student->id);
            return [
                'id'             => $student->id,
                'name'           => $student->name,
                'student_number' => $student->student_number,
                'status'         => $record ? $record->status : 'pending', // pending = لم يمسح بعد
                'scanned_at'     => $record ? $record->updated_at->toIso8601String() : null,
            ];
        });

        $scannedCount = $attendanceRecords->where('status', 'present')->count();

        return response()->json([
            'session_status' => $session->status,
            'total_students' => $allStudents->count(),
            'scanned_count'  => $scannedCount,
            'students'       => $students,
        ]);
    }

    /**
     * ──────────────────────────────────────────────────
     * 4. SCAN QR CODE (Student Only)
     * ──────────────────────────────────────────────────
     * Student scans the QR code → gets marked as present.
     *
     * POST /api/qr-attendance/scan
     * Body: { token }
     */
    public function scan(Request $request)
    {
        $user = $request->user();

        // Only students can scan
        if (!$user->hasRole(UserRole::STUDENT)) {
            return response()->json(['message' => 'هذه الميزة متاحة للطلاب فقط.'], 403);
        }

        $validated = $request->validate([
            'token' => 'required|string|size:64',
        ]);

        // Find active session by token
        $session = QrAttendanceSession::where('current_token', $validated['token'])
            ->active()
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'الكود غير صالح أو منتهي الصلاحية. اطلب من المندوب تحديث الكود.',
            ], 404);
        }

        // Verify token hasn't expired
        if (!$session->isTokenValid($validated['token'])) {
            return response()->json([
                'message' => 'انتهت صلاحية الكود. انتظر الكود الجديد وأعد المسح.',
            ], 410);
        }

        // Verify student is in the same academic scope
        $delegate = $session->delegate;
        if ($user->major_id !== $delegate->major_id || $user->level_id !== $delegate->level_id) {
            return response()->json([
                'message' => 'أنت لست ضمن هذه الدفعة.',
            ], 403);
        }

        // Check if student already recorded for this session
        $existingAttendance = Attendance::where('student_id', $user->id)
            ->where('subject_id', $session->subject_id)
            ->where('date', $session->date)
            ->first();

        if ($existingAttendance && $existingAttendance->status === 'present') {
            return response()->json([
                'message' => 'تم تسجيل حضورك مسبقاً ✅',
                'already_scanned' => true,
            ]);
        }

        // Record attendance as 'present'
        Attendance::updateOrCreate(
            [
                'student_id' => $user->id,
                'subject_id' => $session->subject_id,
                'date'       => $session->date,
            ],
            [
                'status'      => 'present',
                'recorded_by' => $session->delegate_id,
            ]
        );

        return response()->json([
            'message' => 'تم تسجيل حضورك بنجاح! ✅',
            'subject' => $session->subject->name ?? '',
            'date'    => $session->date->format('Y-m-d'),
        ]);
    }

    /**
     * ──────────────────────────────────────────────────
     * 5. FINALIZE SESSION (Delegate Only)
     * ──────────────────────────────────────────────────
     * Ends the QR session. Marks all remaining students as absent.
     * Also creates/updates the Lecture record.
     *
     * POST /api/qr-attendance/{session}/finalize
     */
    public function finalize(Request $request, QrAttendanceSession $session)
    {
        $user = $request->user();

        if ($session->delegate_id !== $user->id) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }

        if ($session->status === 'finalized') {
            return response()->json(['message' => 'الجلسة منتهية بالفعل.'], 410);
        }

        // Lock the session to prevent further scans
        $session->update(['status' => 'finalized']);

        // We do NOT mark absentees here anymore.
        // We redirect to the manual attendance page for review and final saving.

        return response()->json([
            'message'      => 'تم إنهاء المسح. جاري الانتقال للمراجعة...',
            'redirect_url' => route('delegate.attendance.create', [
                'subject'       => $session->subject_id,
                'qr_session_id' => $session->id
            ]),
        ]);
    }
}
