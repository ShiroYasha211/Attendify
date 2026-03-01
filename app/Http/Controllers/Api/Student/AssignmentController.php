<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Academic\Assignment;
use App\Models\Academic\Subject;
use App\Models\AssignmentSubmission;

class AssignmentController extends Controller
{
    /**
     * Get Student Assignments (Active and Past)
     */
    public function index(Request $request)
    {
        $student = $request->user();

        $subjectIds = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->pluck('id');

        $assignments = Assignment::whereIn('subject_id', $subjectIds)
            ->with(['subject:id,name', 'submissions' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->orderBy('due_date', 'desc')
            ->get()
            ->map(function ($assignment) {
                $submission = $assignment->submissions->first();
                $isLate = now()->greaterThan($assignment->due_date);

                $status = 'available';
                if ($submission) $status = 'submitted';
                elseif ($isLate) $status = 'missing';

                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'subject_name' => $assignment->subject->name ?? '',
                    'due_date' => $assignment->due_date,
                    'marks' => $assignment->marks,
                    'file_path' => $assignment->file_path ? asset('storage/' . $assignment->file_path) : null,
                    'status' => $status,
                    'submission' => $submission ? [
                        'file_url' => asset('storage/' . $submission->file_path),
                        'notes' => $submission->notes,
                        'submitted_at' => $submission->submitted_at,
                    ] : null,
                ];
            });

        $activeAssignments = $assignments->filter(function ($a) {
            return $a['status'] === 'available' || $a['status'] === 'submitted';
        })->values();

        $pastAssignments = $assignments->filter(function ($a) {
            return $a['status'] === 'missing';
        })->values();

        // Note: above filter is simplified, but visually separates the list well
        // Let's refine it realistically based on dates
        $activeAssignments = $assignments->filter(function ($a) {
            return \Carbon\Carbon::parse($a['due_date'])->isFuture() || $a['status'] === 'submitted';
        })->values();

        $pastAssignments = $assignments->filter(function ($a) {
            return \Carbon\Carbon::parse($a['due_date'])->isPast() && $a['status'] === 'missing';
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'active' => $activeAssignments,
                'past' => $pastAssignments,
            ]
        ], 200);
    }

    /**
     * Submit an assignment
     */
    public function submit(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,zip,rar,doc,docx,jpeg,png,jpg|max:10240', // Max 10MB
            'notes' => 'nullable|string|max:500',
        ]);

        $student = $request->user();

        $subjectIds = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->pluck('id');

        $assignment = Assignment::whereIn('subject_id', $subjectIds)->findOrFail($id);

        $existingSubmission = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            if (Storage::disk('public')->exists($existingSubmission->file_path)) {
                Storage::disk('public')->delete($existingSubmission->file_path);
            }
            $existingSubmission->delete();
        }

        $file = $request->file('file');
        $fileName = $student->student_number . '_' . $student->name . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('submissions/' . $assignment->id, $fileName, 'public');

        $submission = AssignmentSubmission::create([
            'assignment_id' => $id,
            'student_id' => $student->id,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تسليم التكليف بنجاح.',
            'data' => [
                'file_url' => asset('storage/' . $submission->file_path),
                'notes' => $submission->notes,
                'submitted_at' => $submission->submitted_at,
            ]
        ], 201);
    }
}
