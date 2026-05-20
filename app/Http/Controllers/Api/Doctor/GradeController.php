<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Enums\UserRole;
use App\Models\Academic\Subject;
use App\Models\Grade;
use App\Models\StudentNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradeController extends DoctorApiController
{
    /** GET /api/doctor/grades */
    public function index()
    {
        $subjects = Subject::where('doctor_id', Auth::id())->with(['major', 'level'])->get();
        $subjectIds = $subjects->pluck('id');

        $gradeStats = Grade::whereIn('subject_id', $subjectIds)
            ->where('status', 'approved')
            ->select(
                'subject_id',
                DB::raw('SUM(score) as total_score'),
                DB::raw('COUNT(DISTINCT student_id) as graded_students')
            )
            ->groupBy('subject_id')
            ->get()
            ->keyBy('subject_id');

        $studentsCountMap = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])
            ->whereIn('major_id', $subjects->pluck('major_id')->unique())
            ->whereIn('level_id', $subjects->pluck('level_id')->unique())
            ->select('major_id', 'level_id', DB::raw('count(*) as count'))
            ->groupBy('major_id', 'level_id')
            ->get()
            ->keyBy(fn ($i) => $i->major_id . '_' . $i->level_id);

        $data = $subjects->map(function ($subject) use ($gradeStats, $studentsCountMap) {
            $stats = $gradeStats->get($subject->id);
            $key = $subject->major_id . '_' . $subject->level_id;

            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'major' => $subject->major?->name,
                'level' => $subject->level?->name,
                'students_count' => $studentsCountMap->has($key) ? $studentsCountMap->get($key)->count : 0,
                'graded_students' => $stats?->graded_students ?? 0,
                'average_grade' => $stats && $stats->graded_students > 0
                    ? round($stats->total_score / $stats->graded_students, 1)
                    : null,
            ];
        });

        return $this->success($data);
    }

    /** GET /api/doctor/grades/{subject} */
    public function show(Request $request, $id)
    {
        $subject = Subject::where('doctor_id', Auth::id())
            ->with('gradeCategories')
            ->findOrFail($id);

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with([
                'grades' => fn ($query) => $query->where('subject_id', $subject->id),
                'studentNotes' => fn ($query) => $query
                    ->where('subject_id', $subject->id)
                    ->where('doctor_id', Auth::id())
                    ->latest()
                    ->limit(3),
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($student) {
                $finalGrade = $student->grades->where('type', 'final')->first();
                $continuous = $student->grades
                    ->where('type', 'continuous')
                    ->where('status', 'approved')
                    ->sum('score');

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_number' => $student->student_number,
                    'continuous' => round((float) $continuous, 2),
                    'final' => $finalGrade?->score,
                    'total' => round((float) $continuous + (float) ($finalGrade?->score ?? 0), 2),
                    'category_grades' => $student->grades
                        ->where('type', 'continuous')
                        ->whereNotNull('category_id')
                        ->values()
                        ->map(fn ($grade) => [
                            'category_id' => $grade->category_id,
                            'score' => $grade->score,
                            'status' => $grade->status,
                        ]),
                    'notes' => $student->studentNotes
                        ->map(fn ($note) => [
                            'id' => $note->id,
                            'note' => $note->note,
                            'created_at' => $note->created_at,
                        ])
                        ->values(),
                ];
            });

        $totals = $students->pluck('total')->filter(fn ($value) => $value > 0);
        $pendingCount = Grade::where('subject_id', $subject->id)
            ->where('status', 'pending')
            ->count();

        return $this->success([
            'subject' => ['id' => $subject->id, 'name' => $subject->name],
            'categories' => $subject->gradeCategories,
            'students' => $students,
            'pending_count' => $pendingCount,
            'stats' => [
                'total_students' => $students->count(),
                'graded' => $totals->count(),
                'average' => $totals->count() > 0 ? round($totals->avg(), 1) : 0,
                'highest' => $totals->max() ?? 0,
                'lowest' => $totals->min() ?? 0,
                'pass_rate' => $totals->count() > 0
                    ? round($totals->filter(fn ($value) => $value >= 50)->count() / $totals->count() * 100)
                    : 0,
            ],
        ]);
    }

    /** POST /api/doctor/grades/{subject} */
    public function store(Request $request, $id)
    {
        $subject = Subject::where('doctor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.continuous' => 'nullable|numeric|min:0|max:40',
            'grades.*.final' => 'nullable|numeric|min:0|max:60',
            'grades.*.categories' => 'nullable|array',
            'grades.*.categories.*' => 'nullable|numeric|min:0',
        ]);

        foreach ($request->grades as $gradeData) {
            $studentId = $gradeData['student_id'];

            if (array_key_exists('continuous', $gradeData) && $gradeData['continuous'] !== null && $gradeData['continuous'] !== '') {
                Grade::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'student_id' => $studentId,
                        'type' => 'continuous',
                        'category_id' => null,
                    ],
                    [
                        'category' => 'أعمال السنة',
                        'score' => $gradeData['continuous'],
                        'max_score' => 40,
                        'created_by' => Auth::id(),
                        'status' => 'approved',
                    ]
                );
            }

            if (array_key_exists('final', $gradeData) && $gradeData['final'] !== null && $gradeData['final'] !== '') {
                Grade::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'student_id' => $studentId,
                        'type' => 'final',
                    ],
                    [
                        'category' => 'final',
                        'score' => $gradeData['final'],
                        'max_score' => 60,
                        'created_by' => Auth::id(),
                        'status' => 'approved',
                    ]
                );
            }

            foreach (($gradeData['categories'] ?? []) as $categoryId => $score) {
                if ($score === null || $score === '') {
                    continue;
                }

                $category = $subject->gradeCategories()->find($categoryId);
                if (! $category) {
                    continue;
                }

                Grade::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'student_id' => $studentId,
                        'type' => 'continuous',
                        'category_id' => $category->id,
                    ],
                    [
                        'category' => $category->name,
                        'score' => $score,
                        'max_score' => $category->max_score,
                        'created_by' => Auth::id(),
                        'status' => 'approved',
                    ]
                );
            }
        }

        return $this->success(null, 'تم حفظ الدرجات بنجاح.');
    }

    /** GET /api/doctor/grades/{subject}/report */
    public function report($id)
    {
        $subject = Subject::where('doctor_id', Auth::id())->with(['major', 'level'])->findOrFail($id);

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with(['grades' => fn ($query) => $query->where('subject_id', $subject->id)])
            ->orderBy('name')
            ->get();

        $totals = $students
            ->map(fn ($student) => $student->grades->where('status', 'approved')->sum('score'))
            ->filter(fn ($value) => $value > 0);

        $stats = [
            'total_students' => $students->count(),
            'graded' => $totals->count(),
            'average' => $totals->count() > 0 ? round($totals->avg(), 1) : 0,
            'highest' => $totals->max() ?? 0,
            'lowest' => $totals->min() ?? 0,
            'pass_rate' => $totals->count() > 0 ? round($totals->filter(fn ($value) => $value >= 50)->count() / $totals->count() * 100) : 0,
        ];

        $studentsData = $students->map(function ($student) {
            $finalGrade = $student->grades->where('type', 'final')->first();
            $continuous = $student->grades->where('type', 'continuous')->where('status', 'approved')->sum('score');

            return [
                'id' => $student->id,
                'name' => $student->name,
                'student_number' => $student->student_number,
                'continuous' => round((float) $continuous, 2),
                'final' => $finalGrade?->score,
                'total' => round((float) $continuous + (float) ($finalGrade?->score ?? 0), 2),
            ];
        });

        return $this->success([
            'subject' => ['id' => $subject->id, 'name' => $subject->name, 'major' => $subject->major?->name, 'level' => $subject->level?->name],
            'stats' => $stats,
            'students' => $studentsData,
        ]);
    }

    /** POST /api/doctor/grades/{subject}/note/{student} */
    public function storeNote(Request $request, $subjectId, $studentId)
    {
        $subject = Subject::where('doctor_id', Auth::id())->findOrFail($subjectId);

        $request->validate(['note' => 'required|string|max:500']);

        StudentNote::create([
            'subject_id' => $subject->id,
            'student_id' => $studentId,
            'doctor_id' => Auth::id(),
            'note' => $request->note,
        ]);

        return $this->success(null, 'تم إرسال الملاحظة بنجاح.', 201);
    }
}
