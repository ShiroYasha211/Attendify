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
        $user = Auth::user();
        $hiddenIds = $user->hiddenChecklists()->pluck('evaluation_checklists.id')->toArray();

        $checklists = EvaluationChecklist::withCount('items')
            ->where(function ($q) use ($user, $hiddenIds) {
                $q->whereNull('doctor_id');
                if (!empty($hiddenIds)) {
                    $q->whereNotIn('id', $hiddenIds);
                }
            })
            ->orWhere('doctor_id', $user->id)
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
            'timer_type' => 'required|in:fixed,open',
            'time_limit_minutes' => 'required_if:timer_type,fixed|nullable|integer|min:1|max:120',
            'description' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.marks' => 'required|integer|min:1|max:100',
            'items.*.sub_items' => 'nullable|array',
            'items.*.sub_items.*.description' => 'required_with:items.*.sub_items|string|max:500',
            'items.*.sub_items.*.marks' => 'required_with:items.*.sub_items|integer|min:1|max:100',
        ]);

        // Validate that sub_items marks sum up to main item marks
        $totalChecklistMarks = 0;
        foreach ($request->items as $item) {
            $totalChecklistMarks += (int)$item['marks'];
            if (!empty($item['sub_items'])) {
                $subTotal = collect($item['sub_items'])->sum('marks');
                if ($subTotal !== (int)$item['marks']) {
                    return back()->withInput()->withErrors(['items' => "مجموع درجات العناصر الفرعية يجب أن يساوي درجة العنصر الرئيسي '{$item['description']}' ({$item['marks']})."]);
                }
            }
        }

        $checklist = EvaluationChecklist::create([
            'title' => $request->title,
            'description' => $request->description,
            'doctor_id' => Auth::id(),
            'skill_type' => $request->skill_type,
            'time_limit_minutes' => $request->timer_type === 'fixed' ? $request->time_limit_minutes : null,
            'is_practice_allowed' => $request->has('is_practice_allowed'),
            'total_marks' => $totalChecklistMarks,
        ]);

        foreach ($request->items as $i => $item) {
            $mainItem = ChecklistItem::create([
                'checklist_id' => $checklist->id,
                'description' => $item['description'],
                'marks' => $item['marks'],
                'sort_order' => $i,
            ]);

            if (!empty($item['sub_items'])) {
                foreach ($item['sub_items'] as $j => $subItem) {
                    ChecklistItem::create([
                        'checklist_id' => $checklist->id,
                        'parent_id' => $mainItem->id,
                        'description' => $subItem['description'],
                        'marks' => $subItem['marks'],
                        'sort_order' => $j,
                    ]);
                }
            }
        }

        return redirect()->route('doctor.clinical.evaluations.checklists')
            ->with('success', 'تم إنشاء قائمة التقييم بنجاح.');
    }

    public function editChecklist($id)
    {
        $user = Auth::user();
        $checklist = EvaluationChecklist::with('items')
            ->where(function ($q) use ($user) {
                $q->whereNull('doctor_id')->orWhere('doctor_id', $user->id);
            })
            ->findOrFail($id);

        return view('doctor.clinical.evaluations.edit_checklist', compact('checklist'));
    }

    public function updateChecklist(Request $request, $id)
    {
        $user = Auth::user();
        $checklist = EvaluationChecklist::where(function ($q) use ($user) {
            $q->whereNull('doctor_id')->orWhere('doctor_id', $user->id);
        })->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'skill_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'timer_type' => 'required|in:fixed,open',
            'time_limit_minutes' => 'required_if:timer_type,fixed|nullable|integer|min:1|max:120',
            'description' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.marks' => 'required|integer|min:1|max:100',
            'items.*.sub_items' => 'nullable|array',
            'items.*.sub_items.*.description' => 'required_with:items.*.sub_items|string|max:500',
            'items.*.sub_items.*.marks' => 'required_with:items.*.sub_items|integer|min:1|max:100',
        ]);

        // Validate that sub_items marks sum up to main item marks
        $totalChecklistMarks = 0;
        foreach ($request->items as $item) {
            $totalChecklistMarks += (int)$item['marks'];
            if (!empty($item['sub_items'])) {
                $subTotal = collect($item['sub_items'])->sum('marks');
                if ($subTotal !== (int)$item['marks']) {
                    return back()->withInput()->withErrors(['items' => "مجموع درجات العناصر الفرعية يجب أن يساوي درجة العنصر الرئيسي '{$item['description']}' ({$item['marks']})."]);
                }
            }
        }

        if (is_null($checklist->doctor_id)) {
            // Hide standard and create personal copy
            $user->hiddenChecklists()->syncWithoutDetaching([$checklist->id]);

            $newChecklist = EvaluationChecklist::create([
                'title' => $request->title,
                'description' => $request->description,
                'doctor_id' => $user->id,
                'skill_type' => $request->skill_type,
                'time_limit_minutes' => $request->timer_type === 'fixed' ? $request->time_limit_minutes : null,
                'is_practice_allowed' => $request->has('is_practice_allowed'),
                'total_marks' => $totalChecklistMarks,
            ]);

            foreach ($request->items as $i => $item) {
                $mainItem = ChecklistItem::create([
                    'checklist_id' => $newChecklist->id,
                    'description' => $item['description'],
                    'marks' => $item['marks'],
                    'sort_order' => $i,
                ]);

                if (!empty($item['sub_items'])) {
                    foreach ($item['sub_items'] as $j => $subItem) {
                        ChecklistItem::create([
                            'checklist_id' => $newChecklist->id,
                            'parent_id' => $mainItem->id,
                            'description' => $subItem['description'],
                            'marks' => $subItem['marks'],
                            'sort_order' => $j,
                        ]);
                    }
                }
            }

            return redirect()->route('doctor.clinical.evaluations.checklists')
                ->with('success', 'تم إنشاء نسخة مخصصة من القائمة الأساسية بنجاح.');

        } else {
            $checklist->update([
                'title' => $request->title,
                'description' => $request->description,
                'skill_type' => $request->skill_type,
                'is_practice_allowed' => $request->has('is_practice_allowed'),
                'time_limit_minutes' => $request->timer_type === 'fixed' ? $request->time_limit_minutes : null,
                'total_marks' => $totalChecklistMarks,
            ]);

            // Recreate items
            $checklist->items()->delete();
            foreach ($request->items as $i => $item) {
                $mainItem = ChecklistItem::create([
                    'checklist_id' => $checklist->id,
                    'description' => $item['description'],
                    'marks' => $item['marks'],
                    'sort_order' => $i,
                ]);

                if (!empty($item['sub_items'])) {
                    foreach ($item['sub_items'] as $j => $subItem) {
                        ChecklistItem::create([
                            'checklist_id' => $checklist->id,
                            'parent_id' => $mainItem->id,
                            'description' => $subItem['description'],
                            'marks' => $subItem['marks'],
                            'sort_order' => $j,
                        ]);
                    }
                }
            }

            return redirect()->route('doctor.clinical.evaluations.checklists')
                ->with('success', 'تم تحديث قائمة التقييم بنجاح.');
        }
    }

    public function destroyChecklist($id)
    {
        $user = Auth::user();
        $checklist = EvaluationChecklist::where(function ($q) use ($user) {
            $q->whereNull('doctor_id')->orWhere('doctor_id', $user->id);
        })->findOrFail($id);

        if (is_null($checklist->doctor_id)) {
            $user->hiddenChecklists()->syncWithoutDetaching([$checklist->id]);
            return redirect()->back()->with('success', 'تم إخفاء القائمة الأساسية من مساحتك.');
        } else {
            $checklist->delete();
            return redirect()->back()->with('success', 'تم حذف القائمة.');
        }
    }

    public function restoreDefaults()
    {
        Auth::user()->hiddenChecklists()->detach();
        return redirect()->route('doctor.clinical.evaluations.checklists')->with('success', 'تم استرداد قوائم التقييم الأساسية بنجاح.');
    }

    // ─────────────────── Live Evaluation ───────────────────

    public function startEvaluation()
    {
        $checklists = EvaluationChecklist::where('doctor_id', Auth::id())->where('is_active', true)->get();

        $doctorSubjects = \App\Models\Academic\Subject::where('doctor_id', Auth::id())
            ->select('major_id', 'level_id')
            ->distinct()
            ->get();

        $studentsQuery = User::whereIn('role', ['student', 'delegate']);
        if ($doctorSubjects->isNotEmpty()) {
            $studentsQuery->where(function ($query) use ($doctorSubjects) {
                foreach ($doctorSubjects as $subject) {
                    $query->orWhere(function ($q) use ($subject) {
                        $q->where('major_id', $subject->major_id)
                            ->where('level_id', $subject->level_id);
                    });
                }
            });
        } else {
            $studentsQuery->whereRaw('1 = 0');
        }
        $students = $studentsQuery->orderBy('name')->get();

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

        $checklist = EvaluationChecklist::with('items')
            ->where('doctor_id', Auth::id()) // Ownership check!
            ->findOrFail($request->checklist_id);

        // Calculate scores. We only calculate for items that the user actually evaluated (which are the leaves).
        // Since we render sub_items (or flat main items) on the frontend, those are the IDs in req->scores.
        $totalScore = 0;
        $maxScore = $checklist->total_marks;

        foreach ($checklist->items as $item) {
            // If this item has sub_items, it should NOT be manually evaluated, we skip it.
            // Its score is derived from its sub_items.
            if ($item->subItems()->count() > 0) {
                continue;
            }

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
            if ($item->subItems()->count() > 0) {
                continue; // Skip because it's a parent item and its score is just the sum of its children
            }

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

        $doctorSubjects = \App\Models\Academic\Subject::where('doctor_id', Auth::id())
            ->select('major_id', 'level_id')
            ->distinct()
            ->get();

        $studentsQuery = User::whereIn('role', ['student', 'delegate']);
        if ($doctorSubjects->isNotEmpty()) {
            $studentsQuery->where(function ($query) use ($doctorSubjects) {
                foreach ($doctorSubjects as $subject) {
                    $query->orWhere(function ($q) use ($subject) {
                        $q->where('major_id', $subject->major_id)
                            ->where('level_id', $subject->level_id);
                    });
                }
            });
        } else {
            $studentsQuery->whereRaw('1 = 0');
        }
        $students = $studentsQuery->orderBy('name')->get();

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
