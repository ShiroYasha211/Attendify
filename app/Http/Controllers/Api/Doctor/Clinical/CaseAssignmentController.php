<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinical\CaseAssignment;
use App\Models\Clinical\ClinicalCase;
use App\Models\User;
use App\Enums\UserRole;

class CaseAssignmentController extends DoctorApiController
{
    /** GET /api/doctor/clinical/assignments */
    public function index(Request $request)
    {
        $query = CaseAssignment::with(['student:id,name,student_number', 'clinicalCase'])
            ->where('assigned_by', Auth::id());

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('clinical_case_id')) {
            $query->where('clinical_case_id', $request->clinical_case_id);
        }
        if ($request->filled('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        return $this->paginated($query->latest()->paginate(20));
    }

    /** POST /api/doctor/clinical/assignments */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'clinical_case_id' => 'required|exists:clinical_cases,id',
            'task_type' => 'required|in:history_taking,clinical_examination,follow_up',
            'instructions' => 'nullable|string',
        ]);
        $validated['assigned_by'] = Auth::id();

        $exists = CaseAssignment::where('student_id', $validated['student_id'])
            ->where('clinical_case_id', $validated['clinical_case_id'])
            ->where('task_type', $validated['task_type'])->exists();

        if ($exists) {
            return $this->error('هذا الطالب مكلف مسبقاً بنفس المهمة لهذه الحالة.', 422);
        }

        $assignment = CaseAssignment::create($validated);
        return $this->success($assignment->load(['student:id,name', 'clinicalCase']), 'تم تكليف الطالب بالحالة بنجاح.', 201);
    }
}
