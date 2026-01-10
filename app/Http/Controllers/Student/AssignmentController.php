<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Academic\Assignment;
use App\Models\Academic\Subject;
use App\Models\AssignmentSubmission;

class AssignmentController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Fetch subjects for the student
        $subjectIds = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->pluck('id');

        // Fetch assignments with submissions
        $allAssignments = Assignment::whereIn('subject_id', $subjectIds)
            ->with(['subject', 'submissions' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->orderBy('due_date', 'desc')
            ->get();

        // Add submission status to each assignment
        foreach ($allAssignments as $assignment) {
            $assignment->my_submission = $assignment->submissions->first();
        }

        $activeAssignments = $allAssignments->filter(function ($assignment) {
            return \Carbon\Carbon::parse($assignment->due_date)->isFuture();
        });

        $pastAssignments = $allAssignments->filter(function ($assignment) {
            return \Carbon\Carbon::parse($assignment->due_date)->isPast();
        });

        return view('student.assignments.index', compact('activeAssignments', 'pastAssignments'));
    }

    /**
     * Show assignment details and submission form.
     */
    public function show($id)
    {
        $student = Auth::user();

        // Get subject IDs for this student
        $subjectIds = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->pluck('id');

        $assignment = Assignment::whereIn('subject_id', $subjectIds)
            ->with('subject')
            ->findOrFail($id);

        $submission = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $student->id)
            ->first();

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    /**
     * Submit assignment.
     */
    public function submit(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,zip,rar|max:10240', // Max 10MB
            'notes' => 'nullable|string|max:500',
        ]);

        $student = Auth::user();

        // Verify student has access to this assignment
        $subjectIds = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->pluck('id');

        $assignment = Assignment::whereIn('subject_id', $subjectIds)->findOrFail($id);

        // Check if already submitted
        $existingSubmission = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            // Delete old file
            if (Storage::disk('public')->exists($existingSubmission->file_path)) {
                Storage::disk('public')->delete($existingSubmission->file_path);
            }
            $existingSubmission->delete();
        }

        // Store the file
        $file = $request->file('file');
        $fileName = $student->student_number . '_' . $student->name . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('submissions/' . $assignment->id, $fileName, 'public');

        // Create submission record
        AssignmentSubmission::create([
            'assignment_id' => $id,
            'student_id' => $student->id,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'submitted_at' => now(),
        ]);

        return redirect()->route('student.assignments.show', $id)
            ->with('success', 'تم تسليم التكليف بنجاح!');
    }
}
