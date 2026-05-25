<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Enums\UserRole;
use App\Models\Academic\Subject;
use App\Models\Grade;
use App\Models\StudentNote;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
                'grade_settings' => $subject->gradeSettingsPayload(),
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

        $hasCategories = $subject->gradeCategories->isNotEmpty();

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
            ->map(function ($student) use ($hasCategories) {
                $finalGrade = $student->grades->where('type', 'final')->first();
                $continuousGrades = $student->grades
                    ->where('type', 'continuous')
                    ->where('status', 'approved');
                if ($hasCategories) {
                    $continuousGrades = $continuousGrades->whereNotNull('category_id');
                }
                $continuous = $continuousGrades
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
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'grade_settings' => $subject->gradeSettingsPayload(),
            ],
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
                    ? round($totals->filter(fn ($value) => $value >= $subject->gradePassingScore())->count() / $totals->count() * 100)
                    : 0,
            ],
        ]);
    }

    /** PUT /api/doctor/grades/{subject}/settings */
    public function updateSettings(Request $request, $id)
    {
        $subject = Subject::where('doctor_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'total_max_score' => 'required|numeric|min:1|max:1000',
            'continuous_max_score' => 'required|numeric|min:0|max:1000',
            'final_max_score' => 'required|numeric|min:0|max:1000',
            'passing_score' => 'required|numeric|min:0|max:1000',
        ]);

        $total = (float) $validated['total_max_score'];
        $continuousMax = (float) $validated['continuous_max_score'];
        $finalMax = (float) $validated['final_max_score'];
        $passing = (float) $validated['passing_score'];

        if (round($continuousMax + $finalMax, 2) !== round($total, 2)) {
            return $this->error('مجموع أعمال السنة والاختبار النهائي يجب أن يساوي الدرجة النهائية.', 422);
        }

        if ($passing > $total) {
            return $this->error('درجة النجاح لا يمكن أن تتجاوز الدرجة النهائية.', 422);
        }

        $invalidContinuous = Grade::where('subject_id', $subject->id)
            ->where('type', 'continuous')
            ->whereNull('category_id')
            ->where('score', '>', $continuousMax)
            ->with('student:id,name,student_number')
            ->get()
            ->map(fn ($grade) => [
                'student_id' => $grade->student_id,
                'name' => $grade->student?->name,
                'student_number' => $grade->student?->student_number,
                'score' => $grade->score,
                'type' => 'continuous',
            ]);

        $invalidFinal = Grade::where('subject_id', $subject->id)
            ->where('type', 'final')
            ->where('score', '>', $finalMax)
            ->with('student:id,name,student_number')
            ->get()
            ->map(fn ($grade) => [
                'student_id' => $grade->student_id,
                'name' => $grade->student?->name,
                'student_number' => $grade->student?->student_number,
                'score' => $grade->score,
                'type' => 'final',
            ]);

        $categoriesTotal = $subject->gradeCategories()->sum('max_score');
        if ($categoriesTotal > ($continuousMax + 0.01)) {
            return $this->error('مجموع تصنيفات أعمال السنة الحالي يتجاوز الحد الجديد.', 422, [
                'categories_total' => round((float) $categoriesTotal, 2),
                'continuous_max_score' => $continuousMax,
            ]);
        }

        $invalidStudents = $invalidContinuous->concat($invalidFinal)->values();
        if ($invalidStudents->isNotEmpty()) {
            return $this->error('لا يمكن حفظ الإعدادات لأن هناك درجات طلاب تتجاوز الحدود الجديدة.', 422, [
                'invalid_students' => $invalidStudents,
            ]);
        }

        $subject->update([
            'grade_total_max_score' => $total,
            'grade_continuous_max_score' => $continuousMax,
            'grade_final_max_score' => $finalMax,
            'grade_passing_score' => $passing,
        ]);

        Grade::where('subject_id', $subject->id)
            ->where('type', 'continuous')
            ->whereNull('category_id')
            ->update(['max_score' => $continuousMax]);

        Grade::where('subject_id', $subject->id)
            ->where('type', 'final')
            ->update(['max_score' => $finalMax]);

        return $this->success([
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'grade_settings' => $subject->fresh()->gradeSettingsPayload(),
            ],
        ], 'تم تحديث إعدادات الدرجات بنجاح.');
    }

    /** POST /api/doctor/grades/{subject} */
    public function store(Request $request, $id)
    {
        $subject = Subject::where('doctor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.continuous' => 'nullable|numeric|min:0|max:' . $subject->gradeContinuousMaxScore(),
            'grades.*.final' => 'nullable|numeric|min:0|max:' . $subject->gradeFinalMaxScore(),
            'grades.*.categories' => 'nullable|array',
            'grades.*.categories.*' => 'nullable|numeric|min:0',
        ]);

        $hasCategories = $subject->gradeCategories()->exists();

        foreach ($request->grades as $gradeData) {
            $studentId = $gradeData['student_id'];

            if ($hasCategories) {
                Grade::where('subject_id', $subject->id)
                    ->where('student_id', $studentId)
                    ->where('type', 'continuous')
                    ->whereNull('category_id')
                    ->delete();
            } elseif (array_key_exists('continuous', $gradeData) && $gradeData['continuous'] !== null && $gradeData['continuous'] !== '') {
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
                        'max_score' => $subject->gradeContinuousMaxScore(),
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
                        'max_score' => $subject->gradeFinalMaxScore(),
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
        return $this->success($this->buildReportData($id));
    }

    /** GET /api/doctor/grades/{subject}/report/pdf */
    public function reportPdf($id)
    {
        $data = $this->buildReportData($id);
        $subject = $data['subject'];
        $stats = $data['stats'];
        $students = collect($data['students']);
        $generatedAt = now()->format('Y-m-d H:i');

        $rows = $students->map(function ($student) {
            $statusColor = $student['status'] === 'passed' ? '#047857' : '#b91c1c';
            $statusLabel = $student['status'] === 'passed' ? 'ناجح' : 'راسب';
            return '<tr>'
                . '<td>' . e($student['rank']) . '</td>'
                . '<td>' . e($student['name']) . '</td>'
                . '<td>' . e($student['student_number']) . '</td>'
                . '<td>' . e($student['continuous']) . '</td>'
                . '<td>' . e($student['final'] ?? '-') . '</td>'
                . '<td><strong>' . e($student['total']) . '</strong></td>'
                . '<td>' . e($student['grade_label']) . '</td>'
                . '<td style="color:' . $statusColor . ';font-weight:700">' . $statusLabel . '</td>'
                . '</tr>';
        })->implode('');

        $html = <<<HTML
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
body { direction: rtl; font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
.hero { background: #163f8f; color: #fff; padding: 18px 22px; border-radius: 16px; margin-bottom: 18px; }
h1 { margin: 0 0 6px; font-size: 22px; }
.muted { color: #64748b; }
.hero .muted { color: #dbeafe; }
.stats { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 16px; }
.stats td { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; }
.label { color: #64748b; font-size: 10px; }
.value { font-size: 16px; font-weight: 800; margin-top: 3px; }
table.data { width: 100%; border-collapse: collapse; }
table.data th { background: #eef2ff; color: #1e3a8a; padding: 9px 7px; text-align: right; }
table.data td { border-bottom: 1px solid #e5e7eb; padding: 8px 7px; }
table.data tr:nth-child(even) td { background: #f8fafc; }
</style>
</head>
<body>
  <div class="hero">
    <h1>تقرير درجات {$subject['name']}</h1>
    <div class="muted">{$subject['major']} - {$subject['level']} | تاريخ التصدير: {$generatedAt}</div>
  </div>
  <table class="stats">
    <tr>
      <td><div class="label">عدد الطلاب</div><div class="value">{$stats['students_count']}</div></td>
      <td><div class="label">المتوسط</div><div class="value">{$stats['average']}</div></td>
      <td><div class="label">نسبة النجاح</div><div class="value">{$stats['pass_rate']}%</div></td>
    </tr>
    <tr>
      <td><div class="label">الأعلى</div><div class="value">{$stats['highest']}</div></td>
      <td><div class="label">الأدنى</div><div class="value">{$stats['lowest']}</div></td>
      <td><div class="label">ناجح / راسب</div><div class="value">{$stats['passed']} / {$stats['failed']}</div></td>
    </tr>
  </table>
  <table class="data">
    <thead>
      <tr>
        <th>الترتيب</th>
        <th>الطالب</th>
        <th>رقم القيد</th>
        <th>أعمال السنة</th>
        <th>النهائي</th>
        <th>المجموع</th>
        <th>التقدير</th>
        <th>الحالة</th>
      </tr>
    </thead>
    <tbody>{$rows}</tbody>
  </table>
</body>
</html>
HTML;

        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        return $pdf->download('grade_report_' . $subject['id'] . '.pdf');
    }

    private function buildReportData($id): array
    {
        $subject = Subject::where('doctor_id', Auth::id())->with(['major', 'level', 'gradeCategories'])->findOrFail($id);
        $hasCategories = $subject->gradeCategories->isNotEmpty();
        $totalMaxScore = $subject->gradeTotalMaxScore();
        $passingScore = $subject->gradePassingScore();

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with(['grades' => fn ($query) => $query->where('subject_id', $subject->id)])
            ->orderBy('name')
            ->get();

        $studentsData = $students->map(function ($student) use ($hasCategories, $totalMaxScore, $passingScore) {
            $finalGrade = $student->grades->where('type', 'final')->where('status', 'approved')->first();
            $continuousGrades = $student->grades->where('type', 'continuous')->where('status', 'approved');
            if ($hasCategories) {
                $continuousGrades = $continuousGrades->whereNotNull('category_id');
            }
            $continuous = $continuousGrades->sum('score');
            $total = round((float) $continuous + (float) ($finalGrade?->score ?? 0), 2);

            return [
                'id' => $student->id,
                'name' => $student->name,
                'student_number' => $student->student_number,
                'continuous' => round((float) $continuous, 2),
                'final' => $finalGrade?->score,
                'total' => $total,
                'grade_label' => $this->gradeLabel($total, $totalMaxScore, $passingScore),
                'status' => $total >= $passingScore ? 'passed' : 'failed',
            ];
        })->sortByDesc('total')->values();

        $rankedStudents = $studentsData->map(function ($student, $index) {
            $student['rank'] = $index + 1;
            return $student;
        });

        $totals = $rankedStudents->pluck('total')->filter(fn ($value) => $value > 0);
        $passed = $totals->filter(fn ($value) => $value >= $passingScore)->count();
        $failed = $totals->count() - $passed;
        $percentage = fn ($value) => $totalMaxScore > 0 ? ((float) $value / $totalMaxScore) * 100 : 0;

        return [
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'major' => $subject->major?->name,
                'level' => $subject->level?->name,
                'grade_settings' => $subject->gradeSettingsPayload(),
            ],
            'stats' => [
                'total_students' => $students->count(),
                'students_count' => $students->count(),
                'graded' => $totals->count(),
                'average' => $totals->count() > 0 ? round($totals->avg(), 1) : 0,
                'highest' => $totals->max() ?? 0,
                'lowest' => $totals->min() ?? 0,
                'passed' => $passed,
                'failed' => $failed,
                'pass_rate' => $totals->count() > 0 ? round($passed / $totals->count() * 100) : 0,
                'distribution' => [
                    'excellent' => $totals->filter(fn ($value) => $percentage($value) >= 90)->count(),
                    'very_good' => $totals->filter(fn ($value) => $percentage($value) >= 80 && $percentage($value) < 90)->count(),
                    'good' => $totals->filter(fn ($value) => $percentage($value) >= 70 && $percentage($value) < 80)->count(),
                    'pass' => $totals->filter(fn ($value) => $value >= $passingScore && $percentage($value) < 70)->count(),
                    'fail' => $totals->filter(fn ($value) => $value < $passingScore)->count(),
                ],
            ],
            'students' => $rankedStudents,
        ];
    }

    private function gradeLabel(float|int $score, float|int $totalMaxScore = 100, float|int $passingScore = 50): string
    {
        $percentage = $totalMaxScore > 0 ? ((float) $score / (float) $totalMaxScore) * 100 : 0;

        if ($percentage >= 90) return 'ممتاز';
        if ($percentage >= 80) return 'جيد جدًا';
        if ($percentage >= 70) return 'جيد';
        if ($score >= $passingScore) return 'مقبول';
        return 'راسب';
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
