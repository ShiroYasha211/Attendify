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
    public function index(Request $request)
    {
        $doctor = Auth::user();

        // Get cases that belong to this doctor
        $cases = ClinicalCase::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->get();

        // Get students filtered by doctor's subjects
        $doctorSubjects = \App\Models\Academic\Subject::where('doctor_id', $doctor->id)
            ->select('major_id', 'level_id')
            ->distinct()
            ->get();

        $studentsQuery = User::where('role', UserRole::STUDENT);
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
            $studentsQuery->whereRaw('1 = 0'); // No subjects = no students
        }
        $students = $studentsQuery->orderBy('name')->get();

        // Get existing assignments by this doctor
        $query = CaseAssignment::with(['student', 'clinicalCase'])
            ->where('assigned_by', $doctor->id);

        if ($request->filled('filter_student_id')) {
            $query->where('student_id', $request->filter_student_id);
        }
        if ($request->filled('filter_case_id')) {
            $query->where('clinical_case_id', $request->filter_case_id);
        }
        if ($request->filled('filter_task_type')) {
            $query->where('task_type', $request->filter_task_type);
        }

        $assignments = $query->latest()->paginate(20)->withQueryString();

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
