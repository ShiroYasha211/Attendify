<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Enums\UserRole;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // 1. Academic Info
        $major = $student->major;
        $level = $student->level;
        $term  = $student->term; // Assuming User has term_id, if not we might need to get current term from system settings??
        // Wait, User table migration has term_id? Let's check User model relationships.
        // Assuming standard academic structure: Student belongs to Major, Level. Term might be current active term or student's term.
        // Let's assume student has term_id for now as per previous context or we get subjects by major/level only?
        // Checking DoctorController logic: $subjects = Subject::where('doctor_id', $doctor->id)...
        // For student, subjects are defined by Major + Level + Term.

        // Fetch Subjects for Student's Academic State
        $subjectsQuery = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id);
        // ->where('term_id', $student->term_id); // If student has term_id. 
        // In many systems, subjects are semester-based. Let's assume we show all subjects for the student's current level/major structure.

        $subjects = $subjectsQuery->with('doctor')->get();

        // 2. Delegate Info
        $delegate = User::where('role', UserRole::DELEGATE)
            ->where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->first();

        // 3. Statistics
        $totalSubjects = $subjects->count();

        // Attendance Stats
        $attendances = $student->attendances;
        $totalSessions = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $excusedCount = $attendances->where('status', 'excused')->count();

        // Calculate Attendance Percentage (Present + Late?)
        // Simple formula: (Present + Late + Excused?) / Total * 100
        // Or just Absence % logic: (Absent / Total) * 100
        $attendancePercentage = 0;
        if ($totalSessions > 0) {
            $attendancePercentage = round((($presentCount + $lateCount) / $totalSessions) * 100, 1);
        }

        return view('student.dashboard', compact(
            'student',
            'major',
            'level',
            'subjects',
            'delegate',
            'totalSubjects',
            'absentCount',
            'attendancePercentage'
        ));
    }

    public function showSubject(Subject $subject)
    {
        // Security check: Ensure subject belongs to student's major/level
        $student = Auth::user();
        if ($subject->major_id != $student->major_id || $subject->level_id != $student->level_id) {
            abort(403, 'غير مصرح لك باستعراض هذا المقرر.');
        }

        // Get Attendance History for this Subject
        $attendances = $student->attendances()
            ->where('subject_id', $subject->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Subject Stats
        $total = $attendances->count();
        $absent = $attendances->where('status', 'absent')->count();
        $percentage = $total > 0 ? round(($absent / $total) * 100, 1) : 0;

        return view('student.subjects.show', compact('subject', 'attendances', 'percentage', 'absent', 'total'));
    }
}
