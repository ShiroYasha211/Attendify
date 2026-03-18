<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use App\Models\Grade;
use App\Models\GradeCategory;
use App\Models\Academic\Subject;
use Illuminate\Support\Facades\Auth;

class GradeController extends StudentApiController
{
    /**
     * Get Student Grades grouped by subject and category
     */
    public function index(Request $request)
    {
        $student = Auth::user();

        // 1. Fetch all subjects for this student
        $subjects = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->get(['id', 'name']);

        $subjectIds = $subjects->pluck('id');

        // 2. Fetch all grades for this student
        $grades = Grade::where('student_id', $student->id)
            ->whereIn('subject_id', $subjectIds)
            ->with(['category:id,name,max_score', 'subject:id,name'])
            ->get();

        // 3. Group and format
        $results = [];

        foreach ($subjects as $subject) {
            $subjectGrades = $grades->where('subject_id', $subject->id);
            
            $categories = [];
            $totalMark = 0;
            $maxPossibleMark = 0;

            foreach ($subjectGrades as $grade) {
                $categories[] = [
                    'category_name' => $grade->category->name ?? 'أعمال فصلية',
                    'score' => $grade->score,
                    'max_score' => $grade->max_score ?? ($grade->category->max_score ?? 0),
                    'status' => $grade->status,
                    'notes' => $grade->notes,
                    'date' => $grade->created_at->format('Y-m-d'),
                ];
                $totalMark += $grade->score;
                $maxPossibleMark += $grade->max_score ?? ($grade->category->max_score ?? 0);
            }

            $results[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'total_score' => $totalMark,
                'max_possible' => $maxPossibleMark,
                'percentage' => $maxPossibleMark > 0 ? round(($totalMark / $maxPossibleMark) * 100, 1) : 0,
                'categories' => $categories,
            ];
        }

        return $this->success([
            'grades' => $results,
            'summary' => [
                'total_subjects' => $subjects->count(),
                'graded_subjects' => count(array_filter($results, fn($r) => count($r['categories']) > 0)),
            ]
        ]);
    }
}
