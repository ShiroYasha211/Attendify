<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request; // Add Request import

class DashboardController extends Controller
{
    public function index()
    {
        $doctor = Auth::user();

        // Fetch subjects assigned to this doctor
        $subjects = Subject::where('doctor_id', $doctor->id)
            ->with(['major', 'level', 'term', 'major.college.university'])
            ->get();

        // Append student count to each subject
        $subjects->each(function ($subject) {
            $subject->students_count = User::where('role', UserRole::STUDENT)
                ->where('major_id', $subject->major_id)
                ->where('level_id', $subject->level_id)
                ->count();
        });

        // Calculate total students across all doctor's subjects
        // Logic: Get all students who belong to the same (major, level, term) as the doctor's subjects
        $studentsCount = 0;
        foreach ($subjects as $subject) {
            $studentsCount += User::where('role', UserRole::STUDENT)
                ->where('major_id', $subject->major_id)
                ->where('level_id', $subject->level_id)
                // ->where('term_id', $subject->term_id) // Assuming students differ by term too, if applicable
                ->count();
        }

        // Calculate pending excuses
        $pendingExcusesCount = \App\Models\Excuse::whereHas('attendance', function ($q) use ($subjects) {
            $q->whereIn('subject_id', $subjects->pluck('id'));
        })->where('status', 'pending')->count();

        return view('doctor.dashboard', compact('doctor', 'subjects', 'studentsCount', 'pendingExcusesCount'));
    }

    public function showSubjectReport(Subject $subject)
    {
        // Ensure the subject belongs to the authenticated doctor
        if ($subject->doctor_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بالوصول لهذا المقرر.');
        }

        // Fetch students for this subject (same logic: match major/level)
        $students = User::where('role', UserRole::STUDENT)
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with(['attendances' => function ($query) use ($subject) {
                $query->where('subject_id', $subject->id);
            }])
            ->get();

        return view('doctor.reports.subject_report', compact('subject', 'students'));
    }
}
