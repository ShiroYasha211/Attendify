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

use App\Http\Controllers\Api\Delegate\DelegateApiController;

class QrAttendanceController extends DelegateApiController
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

            // Only delegates or practical delegates can start sessions
            // Ensure $user is not null
            if (!$user) {
                return response()->json(['message' => 'User not found. Please login.'], 401);
            }

            if (!in_array($user->role, [UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE, UserRole::DOCTOR])) {
                return response()->json(['message' => 'غير مصرح لك بهذا الإجراء.'], 403);
            }

            $validated = $request->validate([
                'subject_id'     => 'required|exists:subjects,id',
                'date'           => 'required|date',
                'title'          => 'required|string|max:255',
                'lecture_number' => 'nullable|string|max:50',
            ]);

            $subject = \App\Models\Academic\Subject::findOrFail($validated['subject_id']);

            // Verify if the doctor has allowed the delegate to take attendance
            // Skip this check if the user IS the doctor (doctors can always take their own attendance)
            if (!in_array($user->role, [UserRole::DOCTOR]) && !$subject->allow_delegate_attendance) {
                return response()->json(['message' => 'التحضير مغلق من قبل الدكتور المشرف على المادة.'], 403);
            }

            // Check if session already exists for this subject+date+title
            $existing = QrAttendanceSession::where('subject_id', $validated['subject_id'])
                ->where('date', $validated['date'])
                ->where('title', $validated['title'])
                ->first();

            if ($existing) {
                // If it was previously finalized, reactivate it
                if ($existing->status === 'finalized') {
                    $existing->status = 'active';
                    $existing->save();
                }

                // If active, return existing session
                $existing->rotateToken(); // Generate fresh token
                return response()->json([
                    'message'    => 'الجلسة موجودة مسبقاً، تم إعادة تفعيل الكود.',
                    'session_id' => $existing->id,
                    'token'      => $existing->current_token,
                    'expires_at' => $existing->token_expires_at->toIso8601String(),
                ]);
            }

            // Generate first token
            $firstToken = Str::random(48) . bin2hex(random_bytes(8));

            try {
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
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle duplicate key — find existing session and reuse it
                $session = QrAttendanceSession::where('subject_id', $validated['subject_id'])
                    ->where('date', $validated['date'])
                    ->first();

                if ($session) {
                    // Reactivate and update
                    $session->update([
                        'title'            => $validated['title'],
                        'lecture_number'   => $validated['lecture_number'] ?? null,
                        'current_token'    => $firstToken,
                        'token_expires_at' => Carbon::now()->addSeconds(30),
                        'status'           => 'active',
                    ]);
                } else {
                    throw $e; // Re-throw if it's a different error
                }
            }

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

        // Verify ownership (delegate_id stores the creator, could be doctor or delegate)
        if ((int) $session->delegate_id !== (int) $user->id) {
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

        if ((int) $session->delegate_id !== (int) $user->id) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }

        // Get the subject to determine the academic scope (works for both doctor and delegate)
        $subject = $session->subject;

        // Get all students in the same scope using the SUBJECT's major/level
        $allStudents = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
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
        if (!in_array($user->role->value, [UserRole::STUDENT->value, UserRole::DELEGATE->value])) {
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

        // Verify student is in the same academic scope (using subject, not delegate)
        $subject = $session->subject;
        if ($user->major_id !== $subject->major_id || $user->level_id !== $subject->level_id) {
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

        if ((int) $session->delegate_id !== (int) $user->id) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }

        if ($session->status === 'finalized') {
            return response()->json(['message' => 'الجلسة منتهية بالفعل.'], 410);
        }

        // Lock the session to prevent further scans
        $session->update(['status' => 'finalized']);

        // We do NOT mark absentees here.
        // The delegate will review and finalize on the manual attendance form.

        // Build redirect URL back to the manual attendance form with QR session context
        $redirectUrl = route('delegate.attendance.create', $session->subject_id)
            . '?qr_session_id=' . $session->id;

        return $this->success(
            [
                'session_id'   => $session->id,
                'subject_id'   => $session->subject_id,
                'redirect_url' => $redirectUrl,
            ],
            'تم إنهاء المسح بنجاح. يمكنك الآن مراجعة قائمة الحضور.'
        );
    }
}
