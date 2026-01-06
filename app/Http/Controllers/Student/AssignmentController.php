<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Academic\Assignment;
use App\Models\Academic\Subject;

class AssignmentController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Fetch subjects for the student
        $subjectIds = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->pluck('id');

        // Fetch assignments
        // Active: Due date >= Now
        // Past: Due date < Now
        $allAssignments = Assignment::whereIn('subject_id', $subjectIds)
            ->with('subject')
            ->orderBy('due_date')
            ->get();

        $activeAssignments = $allAssignments->filter(function ($assignment) {
            return \Carbon\Carbon::parse($assignment->due_date)->isFuture();
        });

        $pastAssignments = $allAssignments->filter(function ($assignment) {
            return \Carbon\Carbon::parse($assignment->due_date)->isPast();
        });

        return view('student.assignments.index', compact('activeAssignments', 'pastAssignments'));
    }
}
