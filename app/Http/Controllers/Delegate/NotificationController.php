<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Academic\Subject;
use App\Models\Attendance;
use App\Models\Notification; // Assuming simple notification model
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class NotificationController extends Controller
{
    public function index()
    {
        $delegate = Auth::user();

        // Get all students in scope
        $students = User::where('role', UserRole::STUDENT)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->get();

        // Calculate absences per student per subject
        // This is a bit heavy, in production optimize with raw queries
        $report = [];
        $subjects = Subject::where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)->get();

        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                $absenceCount = Attendance::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('status', 'absent')
                    ->count();

                if ($absenceCount > 0) {
                    $report[] = [
                        'student' => $student,
                        'subject' => $subject,
                        'absences' => $absenceCount,
                        'percentage' => ($absenceCount * 2), // Mock percentage, assuming 50 lectures = 100%
                    ];
                }
            }
        }

        // Sort by highest absences
        usort($report, function ($a, $b) {
            return $b['absences'] <=> $a['absences'];
        });

        return view('delegate.notifications.index', compact('report'));
    }

    public function create(Request $request)
    {
        // Show form to send specific warning
        // We can use a modal in index instead
    }


    public function store(Request $request)
    {
        // Send Notification Logic
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'message' => 'required|string',
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);

        // Insert using standard Laravel Notification Schema
        // We use UUID for ID
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\AbsenceWarning', // Custom type identifier
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $validated['student_id'],
            'data' => json_encode([
                'title' => 'تنبيه غياب: ' . $subject->name,
                'message' => $validated['message'],
                'subject_id' => $subject->id,
                'sender_id' => Auth::id(),
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'تم إرسال التنبيه للطالب بنجاح.');
    }
}
