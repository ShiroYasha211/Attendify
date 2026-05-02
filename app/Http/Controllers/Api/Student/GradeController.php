<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\Academic\Subject;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends StudentApiController
{
    /**
     * Get student grades grouped by subject and doctor-defined categories.
     */
    public function index(Request $request)
    {
        $student = Auth::user();

        $subjects = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->get(['id', 'name']);

        $subjectIds = $subjects->pluck('id');

        $grades = Grade::where('student_id', $student->id)
            ->whereIn('subject_id', $subjectIds)
            ->with(['gradeCategory:id,name,max_score', 'subject:id,name'])
            ->get();

        $results = [];

        foreach ($subjects as $subject) {
            $subjectGrades = $grades->where('subject_id', $subject->id);
            $categories = [];
            $totalMark = 0;
            $maxPossibleMark = 0;

            foreach ($subjectGrades as $grade) {
                $maxScore = (float) ($grade->max_score ?? ($grade->gradeCategory->max_score ?? 0));

                $categories[] = [
                    'category_name' => $grade->gradeCategory->name
                        ?? $grade->category
                        ?? ($grade->type === 'final' ? 'الاختبار النهائي' : 'أعمال السنة'),
                    'score' => (float) $grade->score,
                    'max_score' => $maxScore,
                    'status' => $grade->status,
                    'date' => $grade->created_at->format('Y-m-d'),
                ];

                if ($grade->status === 'approved') {
                    $totalMark += (float) $grade->score;
                }

                $maxPossibleMark += $maxScore;
            }

            $results[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'total_score' => round($totalMark, 2),
                'max_possible' => round($maxPossibleMark, 2),
                'percentage' => $maxPossibleMark > 0 ? round(($totalMark / $maxPossibleMark) * 100, 1) : 0,
                'categories' => $categories,
            ];
        }

        return $this->success([
            'grades' => $results,
            'summary' => [
                'total_subjects' => $subjects->count(),
                'graded_subjects' => count(array_filter($results, fn ($r) => count($r['categories']) > 0)),
            ],
        ]);
    }
}
