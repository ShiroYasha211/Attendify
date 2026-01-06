<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Academic\Assignment;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function index()
    {
        // Get subjects assigned to the doctor
        $doctorSubjects = Subject::where('doctor_id', Auth::id())->get();
        $subjectIds = $doctorSubjects->pluck('id');

        // Get assignments for these subjects
        $assignments = Assignment::with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->latest()
            ->get();

        return view('doctor.assignments.index', compact('assignments', 'doctorSubjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        // Ensure the doctor owns this subject
        $subject = Subject::where('id', $request->subject_id)
            ->where('doctor_id', Auth::id())
            ->firstOrFail();

        Assignment::create([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'تم إضافة التكليف بنجاح.');
    }

    public function update(Request $request, Assignment $assignment)
    {
        // Ensure the doctor owns the subject of this assignment
        if ($assignment->subject->doctor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
        ]);

        $assignment->update($request->only(['title', 'description', 'due_date']));

        return redirect()->back()->with('success', 'تم تحديث التكليف بنجاح.');
    }

    public function destroy(Assignment $assignment)
    {
        // Ensure the doctor owns the subject of this assignment
        if ($assignment->subject->doctor_id !== Auth::id()) {
            abort(403);
        }

        $assignment->delete();

        return redirect()->back()->with('success', 'تم حذف التكليف بنجاح.');
    }
}
