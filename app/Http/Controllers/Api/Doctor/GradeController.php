<?php

namespace App\Http\Controllers\Api\Doctor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Grade;
use App\Models\StudentNote;
use App\Models\Academic\Subject;
use App\Models\User;
use App\Enums\UserRole;

class GradeController extends DoctorApiController
{
    /** GET /api/doctor/grades */
    public function index()
    {
        $subjects = Subject::where('doctor_id', Auth::id())->with(['major', 'level'])->get();
        $subjectIds = $subjects->pluck('id');

        $gradeStats = Grade::whereIn('subject_id', $subjectIds)
            ->select('subject_id', DB::raw('AVG(total) as avg_grade'), DB::raw('COUNT(DISTINCT student_id) as graded_students'))
            ->groupBy('subject_id')->get()->keyBy('subject_id');

        $studentsCountMap = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->whereIn('major_id', $subjects->pluck('major_id')->unique())
            ->whereIn('level_id', $subjects->pluck('level_id')->unique())
            ->select('major_id', 'level_id', DB::raw('count(*) as count'))
            ->groupBy('major_id', 'level_id')->get()
            ->keyBy(fn($i) => $i->major_id . '_' . $i->level_id);

        $data = $subjects->map(function ($s) use ($gradeStats, $studentsCountMap) {
            $stats = $gradeStats->get($s->id);
            $key = $s->major_id . '_' . $s->level_id;
            return [
                'id' => $s->id,
                'name' => $s->name,
                'major' => $s->major?->name,
                'level' => $s->level?->name,
                'students_count' => $studentsCountMap->has($key) ? $studentsCountMap->get($key)->count : 0,
                'graded_students' => $stats?->graded_students ?? 0,
                'average_grade' => $stats ? round($stats->avg_grade, 1) : null,
            ];
        });

        return $this->success($data);
    }

    /** GET /api/doctor/grades/{subject} */
    public function show(Request $request, $id)
    {
        $subject = Subject::where('doctor_id', Auth::id())->findOrFail($id);

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with(['grades' => fn($q) => $q->where('subject_id', $subject->id)])
            ->orderBy('name')->get()
            ->map(function ($s) use ($subject) {
                $grade = $s->grades->first();
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'student_number' => $s->student_number,
                    'midterm' => $grade?->midterm,
                    'practical' => $grade?->practical,
                    'homework' => $grade?->homework,
                    'final' => $grade?->final,
                    'total' => $grade?->total,
                ];
            });

        return $this->success([
            'subject' => ['id' => $subject->id, 'name' => $subject->name],
            'students' => $students,
        ]);
    }

    /** POST /api/doctor/grades/{subject} */
    public function store(Request $request, $id)
    {
        $subject = Subject::where('doctor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.midterm' => 'nullable|numeric|min:0',
            'grades.*.practical' => 'nullable|numeric|min:0',
            'grades.*.homework' => 'nullable|numeric|min:0',
            'grades.*.final' => 'nullable|numeric|min:0',
        ]);

        foreach ($request->grades as $gradeData) {
            $total = ($gradeData['midterm'] ?? 0) + ($gradeData['practical'] ?? 0) +
                ($gradeData['homework'] ?? 0) + ($gradeData['final'] ?? 0);

            Grade::updateOrCreate(
                ['subject_id' => $subject->id, 'student_id' => $gradeData['student_id']],
                [
                    'midterm' => $gradeData['midterm'] ?? 0,
                    'practical' => $gradeData['practical'] ?? 0,
                    'homework' => $gradeData['homework'] ?? 0,
                    'final' => $gradeData['final'] ?? 0,
                    'total' => $total,
                ]
            );
        }

        return $this->success(null, 'تم حفظ الدرجات بنجاح.');
    }

    /** GET /api/doctor/grades/{subject}/report */
    public function report($id)
    {
        $subject = Subject::where('doctor_id', Auth::id())->with(['major', 'level'])->findOrFail($id);

        $students = User::whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
            ->where('major_id', $subject->major_id)
            ->where('level_id', $subject->level_id)
            ->with(['grades' => fn($q) => $q->where('subject_id', $subject->id)])
            ->orderBy('name')->get();

        $grades = $students->map(fn($s) => $s->grades->first()?->total ?? 0)->filter(fn($v) => $v > 0);

        $stats = [
            'total_students' => $students->count(),
            'graded' => $grades->count(),
            'average' => $grades->count() > 0 ? round($grades->avg(), 1) : 0,
            'highest' => $grades->max() ?? 0,
            'lowest' => $grades->min() ?? 0,
            'pass_rate' => $grades->count() > 0 ? round($grades->filter(fn($v) => $v >= 50)->count() / $grades->count() * 100) : 0,
        ];

        $studentsData = $students->map(function ($s) {
            $grade = $s->grades->first();
            return [
                'id' => $s->id,
                'name' => $s->name,
                'student_number' => $s->student_number,
                'midterm' => $grade?->midterm,
                'practical' => $grade?->practical,
                'homework' => $grade?->homework,
                'final' => $grade?->final,
                'total' => $grade?->total,
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
