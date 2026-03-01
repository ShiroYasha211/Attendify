<?php

namespace App\Http\Controllers\Api\Doctor\Clinical;

use App\Http\Controllers\Api\Doctor\DoctorApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clinical\EvaluationChecklist;
use App\Models\Clinical\ChecklistItem;
use App\Models\Clinical\StudentEvaluation;
use App\Models\Clinical\EvaluationScore;
use App\Models\Clinical\BodySystem;
use App\Models\User;
use App\Enums\UserRole;

class EvaluationController extends DoctorApiController
{
    /** GET /api/doctor/clinical/evaluations/checklists */
    public function checklists(Request $request)
    {
        $query = EvaluationChecklist::where('doctor_id', Auth::id())->with('items');

        if ($request->filled('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        $checklists = $query->latest()->paginate(15);

        return $this->success([
            'checklists' => $checklists->items(),
            'pagination' => [
                'current_page' => $checklists->currentPage(),
                'last_page' => $checklists->lastPage(),
                'per_page' => $checklists->perPage(),
                'total' => $checklists->total(),
            ],
        ]);
    }

    /** POST /api/doctor/clinical/evaluations/checklists */
    public function storeChecklist(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'is_practice_allowed' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.marks' => 'required|integer|min:1|max:100',
        ]);

        $totalMarks = collect($request->items)->sum('marks');

        $checklist = EvaluationChecklist::create([
            'title' => $request->title,
            'description' => $request->description,
            'doctor_id' => Auth::id(),
            'skill_type' => $request->skill_type,
            'time_limit_minutes' => $request->time_limit_minutes,
            'is_practice_allowed' => $request->boolean('is_practice_allowed'),
            'total_marks' => $totalMarks,
        ]);

        foreach ($request->items as $i => $item) {
            ChecklistItem::create([
                'checklist_id' => $checklist->id,
                'description' => $item['description'],
                'marks' => $item['marks'],
                'sort_order' => $i + 1,
            ]);
        }

        return $this->success($checklist->load('items'), 'تم إنشاء قائمة التقييم بنجاح.', 201);
    }

    /** PUT /api/doctor/clinical/evaluations/checklists/{id} */
    public function updateChecklist(Request $request, $id)
    {
        $checklist = EvaluationChecklist::where('doctor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'is_practice_allowed' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.marks' => 'required|integer|min:1|max:100',
        ]);

        $totalMarks = collect($request->items)->sum('marks');

        $checklist->update([
            'title' => $request->title,
            'description' => $request->description,
            'skill_type' => $request->skill_type,
            'time_limit_minutes' => $request->time_limit_minutes,
            'is_practice_allowed' => $request->boolean('is_practice_allowed'),
            'total_marks' => $totalMarks,
        ]);

        $checklist->items()->delete();
        foreach ($request->items as $i => $item) {
            ChecklistItem::create([
                'checklist_id' => $checklist->id,
                'description' => $item['description'],
                'marks' => $item['marks'],
                'sort_order' => $i + 1,
            ]);
        }

        return $this->success($checklist->load('items'), 'تم تحديث قائمة التقييم بنجاح.');
    }

    /** DELETE /api/doctor/clinical/evaluations/checklists/{id} */
    public function destroyChecklist($id)
    {
        $checklist = EvaluationChecklist::where('doctor_id', Auth::id())->findOrFail($id);
        $checklist->items()->delete();
        $checklist->delete();
        return $this->success(null, 'تم حذف قائمة التقييم بنجاح.');
    }

    /** GET /api/doctor/clinical/evaluations/start-data */
    public function startData()
    {
        $doctorId = Auth::id();
        $checklists = EvaluationChecklist::where('doctor_id', $doctorId)->with('items')->get();

        $doctorSubjects = \App\Models\Academic\Subject::where('doctor_id', $doctorId)
            ->select('major_id', 'level_id')->distinct()->get();

        $students = User::where('role', UserRole::STUDENT)
            ->where(function ($q) use ($doctorSubjects) {
                foreach ($doctorSubjects as $ds) {
                    $q->orWhere(function ($sq) use ($ds) {
                        $sq->where('major_id', $ds->major_id)->where('level_id', $ds->level_id);
                    });
                }
            })->orderBy('name')->get(['id', 'name', 'student_number']);

        $bodySystems = BodySystem::all(['id', 'name']);

        return $this->success([
            'checklists' => $checklists,
            'students' => $students,
            'body_systems' => $bodySystems,
        ]);
    }

    /** POST /api/doctor/clinical/evaluations/submit */
    public function submit(Request $request)
    {
        $request->validate([
            'checklist_id' => 'required|exists:evaluation_checklists,id',
            'student_id' => 'required|exists:users,id',
            'body_system_id' => 'nullable|exists:body_systems,id',
            'procedure_type' => 'nullable|string',
            'clinical_case_id' => 'nullable|exists:clinical_cases,id',
            'timer_type' => 'nullable|in:fixed,open',
            'time_taken_seconds' => 'nullable|integer|min:0',
            'scores' => 'required|array',
            'scores.*.score' => 'required|in:done,partial,not_done',
            'doctor_feedback' => 'nullable|string',
        ]);

        $checklist = EvaluationChecklist::with('items')->findOrFail($request->checklist_id);

        // Calculate total score
        $totalObtained = 0;
        $totalMax = $checklist->total_marks;

        foreach ($checklist->items as $item) {
            $scoreValue = $request->scores[$item->id]['score'] ?? 'not_done';
            $marks = match ($scoreValue) {
                'done' => $item->marks,
                'partial' => round($item->marks / 2),
                default => 0,
            };
            $totalObtained += $marks;
        }

        $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100) : 0;
        $grade = match (true) {
            $percentage >= 90 => 'excellent',
            $percentage >= 75 => 'very_good',
            $percentage >= 60 => 'good',
            $percentage >= 50 => 'pass',
            default => 'fail',
        };

        $evaluation = StudentEvaluation::create([
            'checklist_id' => $checklist->id,
            'student_id' => $request->student_id,
            'doctor_id' => Auth::id(),
            'body_system_id' => $request->body_system_id,
            'procedure_type' => $request->procedure_type,
            'clinical_case_id' => $request->clinical_case_id,
            'timer_type' => $request->timer_type ?? 'fixed',
            'time_taken_seconds' => $request->time_taken_seconds ?? 0,
            'total_marks' => $totalMax,
            'obtained_marks' => $totalObtained,
            'percentage' => $percentage,
            'grade' => $grade,
            'doctor_feedback' => $request->doctor_feedback,
        ]);

        // Save individual scores
        foreach ($checklist->items as $item) {
            $scoreValue = $request->scores[$item->id]['score'] ?? 'not_done';
            $marks = match ($scoreValue) {
                'done' => $item->marks,
                'partial' => round($item->marks / 2),
                default => 0,
            };

            EvaluationScore::create([
                'evaluation_id' => $evaluation->id,
                'checklist_item_id' => $item->id,
                'score' => $scoreValue,
                'marks_obtained' => $marks,
            ]);
        }

        return $this->success([
            'evaluation_id' => $evaluation->id,
            'obtained_marks' => $totalObtained,
            'total_marks' => $totalMax,
            'percentage' => $percentage,
            'grade' => $grade,
        ], 'تم حفظ نتيجة التقييم بنجاح.');
    }

    /** GET /api/doctor/clinical/evaluations/results */
    public function results(Request $request)
    {
        $query = StudentEvaluation::with(['student:id,name,student_number', 'checklist:id,title,skill_type'])
            ->where('doctor_id', Auth::id());

        if ($request->filled('checklist_id')) {
            $query->where('checklist_id', $request->checklist_id);
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }

        return $this->paginated($query->latest()->paginate(20));
    }

    /** GET /api/doctor/clinical/evaluations/results/{id} */
    public function showResult($id)
    {
        $evaluation = StudentEvaluation::with([
            'student:id,name,student_number',
            'checklist:id,title,skill_type',
            'scores.checklistItem',
        ])->where('doctor_id', Auth::id())->findOrFail($id);

        return $this->success($evaluation);
    }
}
