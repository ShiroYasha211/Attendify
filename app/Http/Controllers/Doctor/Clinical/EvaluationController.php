<?php

namespace App\Http\Controllers\Doctor\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Clinical\EvaluationChecklist;
use App\Models\Clinical\ChecklistItem;
use App\Models\Clinical\StudentEvaluation;
use App\Models\Clinical\EvaluationScore;
use App\Models\Clinical\ClinicalCase;
use App\Models\User;

class EvaluationController extends Controller
{
    // ─────────────────── Checklists CRUD ───────────────────

    public function checklists()
    {
        $checklists = EvaluationChecklist::withCount('items')
            ->where('doctor_id', Auth::id())
            ->latest()
            ->get();

        return view('doctor.clinical.evaluations.checklists', compact('checklists'));
    }

    public function createChecklist()
    {
        return view('doctor.clinical.evaluations.create_checklist');
    }

    public function storeChecklist(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'skill_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'time_limit_minutes' => 'required|integer|min:1|max:120',
            'description' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.marks' => 'required|integer|min:1|max:100',
        ]);

        $checklist = EvaluationChecklist::create([
            'title' => $request->title,
            'description' => $request->description,
            'doctor_id' => Auth::id(),
            'skill_type' => $request->skill_type,
            'time_limit_minutes' => $request->time_limit_minutes,
            'total_marks' => collect($request->items)->sum('marks'),
        ]);

        foreach ($request->items as $i => $item) {
            ChecklistItem::create([
                'checklist_id' => $checklist->id,
                'description' => $item['description'],
                'marks' => $item['marks'],
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('doctor.clinical.evaluations.checklists')
            ->with('success', 'تم إنشاء قائمة التقييم بنجاح.');
    }

    public function editChecklist($id)
    {
        $checklist = EvaluationChecklist::with('items')
            ->where('doctor_id', Auth::id())
            ->findOrFail($id);

        return view('doctor.clinical.evaluations.edit_checklist', compact('checklist'));
    }

    public function updateChecklist(Request $request, $id)
    {
        $checklist = EvaluationChecklist::where('doctor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'skill_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'time_limit_minutes' => 'required|integer|min:1|max:120',
            'description' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.marks' => 'required|integer|min:1|max:100',
        ]);

        $checklist->update([
            'title' => $request->title,
            'description' => $request->description,
            'skill_type' => $request->skill_type,
            'time_limit_minutes' => $request->time_limit_minutes,
            'total_marks' => collect($request->items)->sum('marks'),
        ]);

        // Recreate items
        $checklist->items()->delete();
        foreach ($request->items as $i => $item) {
            ChecklistItem::create([
                'checklist_id' => $checklist->id,
                'description' => $item['description'],
                'marks' => $item['marks'],
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('doctor.clinical.evaluations.checklists')
            ->with('success', 'تم تحديث قائمة التقييم بنجاح.');
    }

    public function destroyChecklist($id)
    {
        $checklist = EvaluationChecklist::where('doctor_id', Auth::id())->findOrFail($id);
        $checklist->delete();
        return redirect()->back()->with('success', 'تم حذف القائمة.');
    }

    // ─────────────────── Live Evaluation ───────────────────

    public function startEvaluation()
    {
        $checklists = EvaluationChecklist::where('doctor_id', Auth::id())->where('is_active', true)->get();
        $students = User::where('role', 'student')->orderBy('name')->get();
        $cases = ClinicalCase::where('doctor_id', Auth::id())->where('status', 'active')->get();

        return view('doctor.clinical.evaluations.start', compact('checklists', 'students', 'cases'));
    }

    public function liveEvaluate(Request $request)
    {
        $request->validate([
            'checklist_id' => 'required|exists:evaluation_checklists,id',
            'student_id' => 'required|exists:users,id',
            'clinical_case_id' => 'nullable|exists:clinical_cases,id',
            'procedure_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'body_system_id' => 'required|exists:body_systems,id',
            'timer_type' => 'required|in:fixed,open',
        ]);

        $checklist = EvaluationChecklist::with('items')->findOrFail($request->checklist_id);
        $student = User::findOrFail($request->student_id);
        $bodySystem = \App\Models\Clinical\BodySystem::find($request->body_system_id);

        return view('doctor.clinical.evaluations.live', compact('checklist', 'student', 'bodySystem', 'request'));
    }

    public function submitEvaluation(Request $request)
    {
        $request->validate([
            'checklist_id' => 'required|exists:evaluation_checklists,id',
            'student_id' => 'required|exists:users,id',
            'clinical_case_id' => 'nullable',
            'time_taken_seconds' => 'required|integer|min:0',
            'doctor_feedback' => 'nullable|string|max:2000',
            'scores' => 'required|array',
            'scores.*.score' => 'required|in:done,partial,not_done',
        ]);

        $checklist = EvaluationChecklist::with('items')->findOrFail($request->checklist_id);

        // Calculate scores
        $totalScore = 0;
        $maxScore = $checklist->total_marks;

        foreach ($checklist->items as $item) {
            $s = $request->scores[$item->id]['score'] ?? 'not_done';
            if ($s === 'done') {
                $totalScore += $item->marks;
            } elseif ($s === 'partial') {
                $totalScore += intval($item->marks / 2);
            }
        }

        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;

        // Determine grade
        $grade = match (true) {
            $percentage >= 90 => 'excellent',
            $percentage >= 75 => 'good',
            $percentage >= 60 => 'acceptable',
            $percentage >= 50 => 'weak',
            default => 'fail',
        };

        $evaluation = StudentEvaluation::create([
            'student_id' => $request->student_id,
            'doctor_id' => Auth::id(),
            'checklist_id' => $request->checklist_id,
            'clinical_case_id' => $request->clinical_case_id ?: null,
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'grade' => $grade,
            'time_taken_seconds' => $request->time_taken_seconds,
            'doctor_feedback' => $request->doctor_feedback,
        ]);

        // Save individual scores
        foreach ($checklist->items as $item) {
            $s = $request->scores[$item->id]['score'] ?? 'not_done';
            $marksObtained = match ($s) {
                'done' => $item->marks,
                'partial' => intval($item->marks / 2),
                default => 0,
            };

            EvaluationScore::create([
                'evaluation_id' => $evaluation->id,
                'checklist_item_id' => $item->id,
                'score' => $s,
                'marks_obtained' => $marksObtained,
                'note' => $request->scores[$item->id]['note'] ?? null,
            ]);
        }

        return redirect()->route('doctor.clinical.evaluations.results')
            ->with('success', "تم حفظ التقييم بنجاح! النتيجة: {$percentage}% ({$evaluation->grade_label})");
    }

    // ─────────────────── Results ───────────────────

    public function results(Request $request)
    {
        $query = StudentEvaluation::with(['student', 'checklist'])
            ->where('doctor_id', Auth::id());

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $evaluations = $query->latest()->paginate(20)->withQueryString();
        $students = User::where('role', 'student')->orderBy('name')->get();

        return view('doctor.clinical.evaluations.results', compact('evaluations', 'students'));
    }

    public function showResult($id)
    {
        $evaluation = StudentEvaluation::with(['student', 'checklist', 'scores.checklistItem'])
            ->where('doctor_id', Auth::id())
            ->findOrFail($id);

        return view('doctor.clinical.evaluations.show_result', compact('evaluation'));
    }
}
