<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\StudentNote;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    /**
     * Display subjects for grade entry.
     */
    public function index()
    {
        $user = Auth::user();

        $subjects = Subject::where('doctor_id', $user->id)
            ->with(['major', 'level'])
            ->get();

        // Add student and grade counts
        $subjects->each(function ($subject) {
            $subject->students_count = User::where('role', UserRole::STUDENT)
                ->where('major_id', $subject->major_id)
                ->where('level_id', $subject->level_id)
                ->count();

            $subject->grades_count = Grade::where('subject_id', $subject->id)->count();

            // Calculate average score
            $allGrades = Grade::where('subject_id', $subject->id)->get();
            $studentGrades = [];
            foreach ($allGrades as $grade) {
                if (!isset($studentGrades[$grade->student_id])) {
                    $studentGrades[$grade->student_id] = 0;
                }
                $studentGrades[$grade->student_id] += $grade->score;
            }
            $subject->average_score = count($studentGrades) > 0
                ? round(array_sum($studentGrades) / count($studentGrades), 1)
                : 0;
        });

        return view('doctor.grades.index', compact('subjects'));
    }

    /**
     * Show grade entry form for a specific subject.
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();

        $subject = Subject::where('doctor_id', $user->id)
            ->with(['major', 'level'])
            ->findOrFail($id);

        // Base students query
        $studentsQuery = User::where('role', UserRole::STUDENT)
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id);

        // Apply search filter
        $search = $request->get('search');
        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery->orderBy('name')->get();

        // Get grades and notes for each student
        foreach ($students as $student) {
            $student->continuous_grade = Grade::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('type', 'continuous')
                ->first();

            $student->final_grade = Grade::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('type', 'final')
                ->first();

            $student->notes = StudentNote::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('doctor_id', $user->id)
                ->latest()
                ->take(3)
                ->get();
        }

        // Calculate statistics
        $stats = $this->calculateSubjectStats($subject, $students);

        return view('doctor.grades.show', compact('subject', 'students', 'stats', 'search'));
    }

    /**
     * Store grades for students.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.continuous' => 'nullable|numeric|min:0|max:40',
            'grades.*.final' => 'nullable|numeric|min:0|max:60',
        ]);

        $user = Auth::user();

        $subject = Subject::where('doctor_id', $user->id)->findOrFail($id);

        foreach ($request->grades as $gradeData) {
            $studentId = $gradeData['student_id'];

            // Save continuous grade (max 40)
            if (isset($gradeData['continuous']) && $gradeData['continuous'] !== null) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subject->id,
                        'type' => 'continuous',
                    ],
                    [
                        'score' => $gradeData['continuous'],
                        'max_score' => 40,
                        'created_by' => $user->id,
                    ]
                );
            }

            // Save final grade (max 60)
            if (isset($gradeData['final']) && $gradeData['final'] !== null) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subject->id,
                        'type' => 'final',
                    ],
                    [
                        'score' => $gradeData['final'],
                        'max_score' => 60,
                        'created_by' => $user->id,
                    ]
                );
            }
        }

        return redirect()->route('doctor.grades.show', $subject->id)
            ->with('success', 'تم حفظ الدرجات بنجاح');
    }

    /**
     * Store a note for a student.
     */
    public function storeNote(Request $request, $subjectId, $studentId)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // Verify subject ownership
        $subject = Subject::where('doctor_id', $user->id)->findOrFail($subjectId);

        StudentNote::create([
            'student_id' => $studentId,
            'doctor_id' => $user->id,
            'subject_id' => $subject->id,
            'note' => $request->note,
        ]);

        return back()->with('success', 'تم إرسال الملاحظة للطالب بنجاح');
    }

    /**
     * Show comprehensive grade report for a subject.
     */
    public function report($id)
    {
        $user = Auth::user();

        $subject = Subject::where('doctor_id', $user->id)
            ->with(['major', 'level'])
            ->findOrFail($id);

        // Get all students with grades
        $students = User::where('role', UserRole::STUDENT)
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->orderBy('name')
            ->get();

        foreach ($students as $student) {
            $student->continuous_grade = Grade::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('type', 'continuous')
                ->first();

            $student->final_grade = Grade::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('type', 'final')
                ->first();

            $student->total = ($student->continuous_grade->score ?? 0) + ($student->final_grade->score ?? 0);
        }

        // Sort by total descending
        $students = $students->sortByDesc('total')->values();

        // Calculate stats
        $stats = $this->calculateSubjectStats($subject, $students);

        return view('doctor.grades.report', compact('subject', 'students', 'stats'));
    }

    /**
     * Calculate statistics for a subject.
     */
    private function calculateSubjectStats($subject, $students)
    {
        $totals = [];
        $passed = 0;
        $failed = 0;

        foreach ($students as $student) {
            $continuous = $student->continuous_grade->score ?? 0;
            $final = $student->final_grade->score ?? 0;
            $total = $continuous + $final;
            $totals[] = $total;

            if ($total >= 60) {
                $passed++;
            } else {
                $failed++;
            }
        }

        return [
            'students_count' => count($students),
            'average' => count($totals) > 0 ? round(array_sum($totals) / count($totals), 1) : 0,
            'highest' => count($totals) > 0 ? max($totals) : 0,
            'lowest' => count($totals) > 0 ? min($totals) : 0,
            'passed' => $passed,
            'failed' => $failed,
            'pass_rate' => count($students) > 0 ? round(($passed / count($students)) * 100) : 0,
        ];
    }
}
