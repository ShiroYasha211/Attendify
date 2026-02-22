<?php

namespace App\Http\Controllers\Doctor\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Clinical\CaseAssignment;
use App\Models\Clinical\ClinicalCase;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function index()
    {
        $doctor = Auth::user();

        // Get cases that belong to this doctor
        $cases = ClinicalCase::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->get();

        // Get all students (ideally filtered by doctor's subjects, but for now we get all students or students in same college)
        $students = User::where('role', UserRole::STUDENT)->get();

        // Get existing assignments by this doctor
        $assignments = CaseAssignment::with(['student', 'clinicalCase'])
            ->where('assigned_by', $doctor->id)
            ->latest()
            ->paginate(20);

        return view('doctor.clinical.assignments.index', compact('cases', 'students', 'assignments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'clinical_case_id' => 'required|exists:clinical_cases,id',
            'task_type' => 'required|in:history_taking,clinical_examination,follow_up',
            'instructions' => 'nullable|string',
        ]);

        $validated['assigned_by'] = Auth::id();

        // Check if student is already assigned this case for this task
        $exists = CaseAssignment::where('student_id', $validated['student_id'])
            ->where('clinical_case_id', $validated['clinical_case_id'])
            ->where('task_type', $validated['task_type'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'هذا الطالب مكلف مسبقاً بنفس المهمة لهذه الحالة.');
        }

        CaseAssignment::create($validated);

        return redirect()->route('doctor.clinical.assignments.index')
            ->with('success', 'تم تكليف الطالب بالحالة بنجاح.');
    }
}
