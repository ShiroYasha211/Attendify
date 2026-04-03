<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use App\Models\Clinical\CaseAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaseAssignmentController extends DoctorApiController
{
    public function index(Request $request)
    {
        $query = CaseAssignment::with(['student:id,name,student_number', 'clinicalCase', 'reviewer:id,name'])
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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $this->paginated($query->latest()->paginate(20));
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
            return $this->error('This student already has the same task for this case.', 422);
        }

        $assignment = CaseAssignment::create($validated);

        return $this->success(
            $assignment->load(['student:id,name,student_number', 'clinicalCase', 'reviewer:id,name']),
            'Clinical assignment created successfully.',
            201
        );
    }

    public function review(Request $request, CaseAssignment $assignment)
    {
        if ($assignment->assigned_by !== Auth::id()) {
            return $this->error('You are not allowed to review this assignment.', 403);
        }

        if ($assignment->status !== 'submitted_for_review') {
            return $this->error('This assignment is not waiting for review.', 422);
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

            return $this->success($assignment->fresh(['student:id,name', 'clinicalCase', 'reviewer:id,name']), 'Assignment approved successfully.');
        }

        $assignment->update([
            'status' => 'rejected',
            'reviewed_at' => $now,
            'reviewed_by' => Auth::id(),
            'review_notes' => trim($validated['review_notes']),
            'is_completed' => false,
            'completed_at' => null,
        ]);

        return $this->success($assignment->fresh(['student:id,name', 'clinicalCase', 'reviewer:id,name']), 'Assignment rejected successfully.');
    }
}
