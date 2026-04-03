<?php

namespace App\Http\Controllers\Doctor\Clinical;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Clinical\CaseAssignment;
use App\Models\Clinical\ClinicalCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $doctor = Auth::user();

        $cases = ClinicalCase::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->get();

        $doctorSubjects = \App\Models\Academic\Subject::where('doctor_id', $doctor->id)
            ->select('major_id', 'level_id')
            ->distinct()
            ->get();

        $studentsQuery = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE]);
        if ($doctorSubjects->isNotEmpty()) {
            $studentsQuery->where(function ($query) use ($doctorSubjects) {
                foreach ($doctorSubjects as $subject) {
                    $query->orWhere(function ($subQuery) use ($subject) {
                        $subQuery->where('major_id', $subject->major_id)
                            ->where('level_id', $subject->level_id);
                    });
                }
            });
        } else {
            $studentsQuery->whereRaw('1 = 0');
        }
        $students = $studentsQuery->orderBy('name')->get();

        $query = CaseAssignment::with(['student', 'clinicalCase.trainingCenter', 'reviewer'])
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
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
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
        $validated['status'] = 'assigned';

        $exists = CaseAssignment::where('student_id', $validated['student_id'])
            ->where('clinical_case_id', $validated['clinical_case_id'])
            ->where('task_type', $validated['task_type'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'هذا الطالب مكلف مسبقًا بنفس المهمة لهذه الحالة.');
        }

        CaseAssignment::create($validated);

        return redirect()->route('doctor.clinical.assignments.index')
            ->with('success', 'تم تكليف الطالب بالحالة بنجاح.');
    }

    public function review(Request $request, CaseAssignment $assignment)
    {
        abort_unless($assignment->assigned_by === Auth::id(), 403);

        if ($assignment->status !== 'submitted_for_review') {
            return back()->with('error', 'هذه المهمة ليست بانتظار المراجعة.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'review_notes' => 'required_if:action,reject|nullable|string|max:2000',
        ]);

        $now = now();
        if ($validated['action'] === 'approve') {
            $assignment->update([
                'status' => 'approved',
                'reviewed_at' => $now,
                'reviewed_by' => Auth::id(),
                'review_notes' => trim((string) ($validated['review_notes'] ?? '')) ?: null,
                'is_completed' => true,
                'completed_at' => $now,
            ]);

            return back()->with('success', 'تم اعتماد إنجاز المهمة.');
        }

        $assignment->update([
            'status' => 'rejected',
            'reviewed_at' => $now,
            'reviewed_by' => Auth::id(),
            'review_notes' => trim($validated['review_notes']),
            'is_completed' => false,
            'completed_at' => null,
        ]);

        return back()->with('success', 'تم رفض المهمة مع إرسال سبب الرفض للطالب.');
    }
}
