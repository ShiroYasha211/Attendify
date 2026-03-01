<?php

namespace App\Http\Controllers\Api\Doctor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Assignment;
use App\Models\Academic\Subject;
use App\Models\AssignmentSubmission;

class AssignmentController extends DoctorApiController
{
    /** GET /api/doctor/assignments */
    public function index(Request $request)
    {
        $subjectIds = Subject::where('doctor_id', Auth::id())->pluck('id');

        $query = Assignment::with(['subject:id,name', 'submissions'])->whereIn('subject_id', $subjectIds);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->status === 'upcoming') {
            $query->where('due_date', '>=', now());
        } elseif ($request->status === 'overdue') {
            $query->where('due_date', '<', now());
        }

        $assignments = $query->latest()->paginate(15);

        $stats = [
            'total' => Assignment::whereIn('subject_id', $subjectIds)->count(),
            'upcoming' => Assignment::whereIn('subject_id', $subjectIds)->where('due_date', '>=', now())->count(),
            'overdue' => Assignment::whereIn('subject_id', $subjectIds)->where('due_date', '<', now())->count(),
        ];

        return $this->success([
            'stats' => $stats,
            'assignments' => $assignments->items(),
            'pagination' => [
                'current_page' => $assignments->currentPage(),
                'last_page' => $assignments->lastPage(),
                'per_page' => $assignments->perPage(),
                'total' => $assignments->total(),
            ],
        ]);
    }

    /** POST /api/doctor/assignments */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date|after_or_equal:today',
            'requires_submission' => 'nullable|boolean',
        ]);

        $subject = Subject::where('id', $request->subject_id)->where('doctor_id', Auth::id())->firstOrFail();

        $assignment = Assignment::create([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'requires_submission' => $request->boolean('requires_submission'),
            'created_by' => Auth::id(),
        ]);

        return $this->success($assignment, 'تم إضافة التكليف بنجاح.', 201);
    }

    /** PUT /api/doctor/assignments/{id} */
    public function update(Request $request, Assignment $assignment)
    {
        if ($assignment->subject->doctor_id !== Auth::id()) {
            return $this->error('غير مصرح لك.', 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'requires_submission' => 'nullable|boolean',
        ]);

        $assignment->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'requires_submission' => $request->boolean('requires_submission'),
        ]);

        return $this->success($assignment, 'تم تحديث التكليف بنجاح.');
    }

    /** DELETE /api/doctor/assignments/{id} */
    public function destroy(Assignment $assignment)
    {
        if ($assignment->subject->doctor_id !== Auth::id()) {
            return $this->error('غير مصرح لك.', 403);
        }

        $assignment->delete();
        return $this->success(null, 'تم حذف التكليف بنجاح.');
    }

    /** GET /api/doctor/assignments/{id}/submissions */
    public function submissions(Assignment $assignment)
    {
        if ($assignment->subject->doctor_id !== Auth::id()) {
            return $this->error('غير مصرح لك.', 403);
        }

        $submissions = $assignment->submissions()->with('student:id,name,student_number')->latest()->get();
        return $this->success($submissions);
    }

    /** POST /api/doctor/submissions/{id}/review */
    public function reviewSubmission(Request $request, AssignmentSubmission $submission)
    {
        if ($submission->assignment->subject->doctor_id !== Auth::id()) {
            return $this->error('غير مصرح لك.', 403);
        }

        $request->validate([
            'status' => 'required|in:accepted,rejected',
            'feedback' => 'nullable|string|max:1000',
            'grade' => 'nullable|integer|min:0|max:100',
        ]);

        $submission->update([
            'status' => $request->status,
            'feedback' => $request->feedback,
            'grade' => $request->grade,
        ]);

        return $this->success($submission, 'تم تقييم التسليم بنجاح.');
    }
}
