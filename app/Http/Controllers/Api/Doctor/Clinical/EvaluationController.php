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
use App\Models\Clinical\ClinicalCase;
use App\Models\Academic\Major;
use App\Models\Academic\Level;
use App\Models\User;

class EvaluationController extends DoctorApiController
{
    /** GET /api/doctor/clinical/evaluations/checklists */
    public function checklists(Request $request)
    {
        $user = Auth::user();
        $hiddenIds = $user->hiddenChecklists()->pluck('evaluation_checklists.id')->toArray();

        $query = EvaluationChecklist::with('items')
            ->where(function ($query) use ($user, $hiddenIds) {
                $query->where(function ($q) use ($hiddenIds) {
                    $q->whereNull('doctor_id');
                    if (!empty($hiddenIds)) {
                        $q->whereNotIn('id', $hiddenIds);
                    }
                })->orWhere('doctor_id', $user->id);
            });

        if ($request->filled('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        $checklists = $query->latest()->paginate(15);
        $checklists->getCollection()->transform(fn ($checklist) => $this->serializeChecklist($checklist));

        return $this->success([
            'checklists' => $checklists->items(),
            'hidden_count' => count($hiddenIds),
            'pagination' => [
                'current_page' => $checklists->currentPage(),
                'last_page' => $checklists->lastPage(),
                'per_page' => $checklists->perPage(),
                'total' => $checklists->total(),
            ],
        ]);
    }

    /** GET /api/doctor/clinical/evaluations/checklists/hidden */
    public function hiddenChecklists(Request $request)
    {
        $user = Auth::user();

        $query = $user->hiddenChecklists()
            ->with('items')
            ->whereNull('doctor_id')
            ->where('is_active', true)
            ->orderBy('doctor_hidden_checklists.created_at', 'desc');

        if ($request->filled('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $checklists = $query->paginate(15);
        $checklists->getCollection()->transform(fn ($checklist) => $this->serializeChecklist($checklist, true));

        return $this->success([
            'checklists' => $checklists->items(),
            'hidden_count' => $checklists->total(),
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
            'items.*.sub_items' => 'nullable|array',
            'items.*.sub_items.*.description' => 'required_with:items.*.sub_items|string|max:500',
            'items.*.sub_items.*.marks' => 'required_with:items.*.sub_items|integer|min:1|max:100',
        ]);

        $totalMarks = 0;
        foreach ($request->items as $item) {
            $totalMarks += (int)$item['marks'];
            if (!empty($item['sub_items'])) {
                $subTotal = collect($item['sub_items'])->sum('marks');
                if ($subTotal !== (int)$item['marks']) {
                    return $this->error("مجموع درجات العناصر الفرعية يجب أن يساوي درجة العنصر الرئيسي '{$item['description']}' ({$item['marks']}).", 422);
                }
            }
        }

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
            $mainItem = ChecklistItem::create([
                'checklist_id' => $checklist->id,
                'description' => $item['description'],
                'marks' => $item['marks'],
                'sort_order' => $i + 1,
            ]);

            if (!empty($item['sub_items'])) {
                foreach ($item['sub_items'] as $j => $subItem) {
                    ChecklistItem::create([
                        'checklist_id' => $checklist->id,
                        'parent_id' => $mainItem->id,
                        'description' => $subItem['description'],
                        'marks' => $subItem['marks'],
                        'sort_order' => $j + 1,
                    ]);
                }
            }
        }

        return $this->success($checklist->load('items'), 'تم إنشاء قائمة التقييم بنجاح.', 201);
    }

    /** PUT /api/doctor/clinical/evaluations/checklists/{id} */
    public function updateChecklist(Request $request, $id)
    {
        $user = Auth::user();
        $checklist = EvaluationChecklist::where(function ($q) use ($user) {
            $q->whereNull('doctor_id')->orWhere('doctor_id', $user->id);
        })->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'is_practice_allowed' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.marks' => 'required|integer|min:1|max:100',
            'items.*.sub_items' => 'nullable|array',
            'items.*.sub_items.*.description' => 'required_with:items.*.sub_items|string|max:500',
            'items.*.sub_items.*.marks' => 'required_with:items.*.sub_items|integer|min:1|max:100',
        ]);

        $totalMarks = 0;
        foreach ($request->items as $item) {
            $totalMarks += (int)$item['marks'];
            if (!empty($item['sub_items'])) {
                $subTotal = collect($item['sub_items'])->sum('marks');
                if ($subTotal !== (int)$item['marks']) {
                    return $this->error("مجموع درجات العناصر الفرعية يجب أن يساوي درجة العنصر الرئيسي '{$item['description']}' ({$item['marks']}).", 422);
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
                'time_limit_minutes' => $request->time_limit_minutes,
                'is_practice_allowed' => $request->boolean('is_practice_allowed'),
                'total_marks' => $totalMarks,
            ]);

            foreach ($request->items as $i => $item) {
                $mainItem = ChecklistItem::create([
                    'checklist_id' => $newChecklist->id,
                    'description' => $item['description'],
                    'marks' => $item['marks'],
                    'sort_order' => $i + 1,
                ]);

                if (!empty($item['sub_items'])) {
                    foreach ($item['sub_items'] as $j => $subItem) {
                        ChecklistItem::create([
                            'checklist_id' => $newChecklist->id,
                            'parent_id' => $mainItem->id,
                            'description' => $subItem['description'],
                            'marks' => $subItem['marks'],
                            'sort_order' => $j + 1,
                        ]);
                    }
                }
            }

            return $this->success($newChecklist->load('items'), 'تم إنشاء نسخة مخصصة من القائمة الأساسية بنجاح.', 201);
        } else {
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
            $mainItem = ChecklistItem::create([
                'checklist_id' => $checklist->id,
                'description' => $item['description'],
                'marks' => $item['marks'],
                'sort_order' => $i + 1,
            ]);

            if (!empty($item['sub_items'])) {
                foreach ($item['sub_items'] as $j => $subItem) {
                    ChecklistItem::create([
                        'checklist_id' => $checklist->id,
                        'parent_id' => $mainItem->id,
                        'description' => $subItem['description'],
                        'marks' => $subItem['marks'],
                        'sort_order' => $j + 1,
                    ]);
                }
            }
        }

        }
        return $this->success($checklist->load('items'), 'تم تحديث قائمة التقييم بنجاح.');
    }

    /** DELETE /api/doctor/clinical/evaluations/checklists/{id} */
    public function destroyChecklist($id)
    {
        $user = Auth::user();
        $checklist = EvaluationChecklist::where(function ($q) use ($user) {
            $q->whereNull('doctor_id')->orWhere('doctor_id', $user->id);
        })->findOrFail($id);

        if (is_null($checklist->doctor_id)) {
            $user->hiddenChecklists()->syncWithoutDetaching([$checklist->id]);
            return $this->success(null, 'تم إخفاء القائمة الأساسية من مساحتك.');
        } else {
            $checklist->items()->delete();
            $checklist->delete();
            return $this->success(null, 'تم حذف قائمة التقييم بنجاح.');
        }
    }

    public function restoreDefaults()
    {
        Auth::user()->hiddenChecklists()->detach();
        return $this->success(null, 'تم استرداد قوائم التقييم الأساسية بنجاح.');
    }

    /** POST /api/doctor/clinical/evaluations/checklists/{id}/restore */
    public function restoreChecklist($id)
    {
        $user = Auth::user();
        $checklist = $user->hiddenChecklists()
            ->with('items')
            ->where('evaluation_checklists.id', $id)
            ->whereNull('doctor_id')
            ->first();

        if (!$checklist) {
            return $this->error('قائمة التقييم غير مخفية أو لا يمكن استرجاعها.', 404);
        }

        $user->hiddenChecklists()->detach($checklist->id);

        return $this->success(
            $this->serializeChecklist($checklist),
            'تم استرجاع قائمة التقييم بنجاح.'
        );
    }

    /** GET /api/doctor/clinical/evaluations/start-data */
    public function startData()
    {
        $doctorId = Auth::id();
        $hiddenIds = Auth::user()->hiddenChecklists()->pluck('evaluation_checklists.id')->toArray();

        $checklists = EvaluationChecklist::with('items')
            ->where('is_active', true)
            ->where(function ($query) use ($doctorId, $hiddenIds) {
                $query->whereNull('doctor_id')
                    ->when(!empty($hiddenIds), function ($hiddenQuery) use ($hiddenIds) {
                        $hiddenQuery->whereNotIn('id', $hiddenIds);
                    })
                    ->orWhere('doctor_id', $doctorId);
            })
            ->latest()
            ->get();

        $students = User::with(['major:id,name', 'level:id,name'])
            ->inDoctorClinicalScope($doctorId)
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'student_number', 'major_id', 'level_id', 'role']);

        $bodySystems = BodySystem::all(['id', 'name']);
        $cases = ClinicalCase::where('doctor_id', $doctorId)
            ->where('status', 'active')
            ->latest()
            ->limit(50)
            ->get(['id', 'patient_name', 'diagnosis', 'body_system_id']);

        return $this->success([
            'checklists' => $checklists,
            'students' => $students,
            'majors' => Major::whereIn('id', $students->pluck('major_id')->filter()->unique())->orderBy('name')->get(['id', 'name']),
            'levels' => Level::whereIn('id', $students->pluck('level_id')->filter()->unique())->orderBy('name')->get(['id', 'name']),
            'body_systems' => $bodySystems,
            'cases' => $cases,
        ]);
    }

    /** GET /api/doctor/clinical/evaluations/students */
    public function students(Request $request)
    {
        $doctorId = Auth::id();
        $query = User::with(['major:id,name', 'level:id,name'])
            ->inDoctorClinicalScope($doctorId)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('major_id'), fn ($q) => $q->where('major_id', $request->major_id))
            ->when($request->filled('level_id'), fn ($q) => $q->where('level_id', $request->level_id))
            ->orderBy('name');

        $students = $query->paginate((int) $request->input('per_page', 25));

        return $this->success([
            'students' => $students->items(),
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    /** GET /api/doctor/clinical/evaluations/checklists/{id}/take */
    public function takeChecklist($id)
    {
        $checklist = $this->findAccessibleChecklist($id);

        return $this->success([
            'checklist' => $this->serializeChecklist($checklist->load('items.subItems')),
            'items_tree' => $this->checklistItemsTree($checklist),
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
            'scores.*.note' => 'nullable|string|max:1000',
            'doctor_feedback' => 'nullable|string',
        ]);

        $doctorId = Auth::id();
        $checklist = $this->findAccessibleChecklist($request->checklist_id)->load('items.subItems');

        $studentExists = User::inDoctorClinicalScope($doctorId)
            ->where('id', $request->student_id)
            ->exists();
        if (!$studentExists) {
            return $this->error('الطالب خارج نطاقك السريري ولا يمكن تقييمه.', 403);
        }

        if ($request->filled('clinical_case_id')) {
            $caseExists = ClinicalCase::where('doctor_id', $doctorId)
                ->where('status', 'active')
                ->where('id', $request->clinical_case_id)
                ->exists();
            if (!$caseExists) {
                return $this->error('الحالة السريرية غير متاحة لهذا التقييم.', 403);
            }
        }

        // Calculate total score
        $totalObtained = 0;
        $scoreableItems = $this->scoreableItems($checklist);
        $totalMax = $scoreableItems->sum('marks');

        foreach ($scoreableItems as $item) {
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
            'max_score' => $totalMax,
            'total_score' => $totalObtained,
            'percentage' => $percentage,
            'grade' => $grade,
            'doctor_feedback' => $request->doctor_feedback,
        ]);

        // Save individual scores
        foreach ($scoreableItems as $item) {
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
                'note' => $request->scores[$item->id]['note'] ?? null,
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

    private function findAccessibleChecklist($id): EvaluationChecklist
    {
        $user = Auth::user();
        $hiddenIds = $user->hiddenChecklists()->pluck('evaluation_checklists.id')->toArray();

        return EvaluationChecklist::with('items.subItems')
            ->where('id', $id)
            ->where('is_active', true)
            ->where(function ($query) use ($user, $hiddenIds) {
                $query->where(function ($standard) use ($hiddenIds) {
                    $standard->whereNull('doctor_id');
                    if (!empty($hiddenIds)) {
                        $standard->whereNotIn('id', $hiddenIds);
                    }
                })->orWhere('doctor_id', $user->id);
            })
            ->firstOrFail();
    }

    private function scoreableItems(EvaluationChecklist $checklist)
    {
        $items = $checklist->items;
        $leaves = $items->filter(fn ($item) => $item->subItems->isEmpty());

        return $leaves->isNotEmpty() ? $leaves : $items;
    }

    private function checklistItemsTree(EvaluationChecklist $checklist): array
    {
        return $checklist->items
            ->whereNull('parent_id')
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'marks' => $item->marks,
                    'sort_order' => $item->sort_order,
                    'sub_items' => $item->subItems->values()->map(fn ($subItem) => [
                        'id' => $subItem->id,
                        'parent_id' => $subItem->parent_id,
                        'description' => $subItem->description,
                        'marks' => $subItem->marks,
                        'sort_order' => $subItem->sort_order,
                    ])->all(),
                ];
            })
            ->all();
    }

    private function serializeChecklist(EvaluationChecklist $checklist, bool $isHidden = false): array
    {
        return [
            'id' => $checklist->id,
            'title' => $checklist->title,
            'description' => $checklist->description,
            'doctor_id' => $checklist->doctor_id,
            'skill_type' => $checklist->skill_type,
            'time_limit_minutes' => $checklist->time_limit_minutes,
            'is_practice_allowed' => (bool) $checklist->is_practice_allowed,
            'total_marks' => $checklist->total_marks,
            'is_active' => (bool) $checklist->is_active,
            'is_standard' => is_null($checklist->doctor_id),
            'is_hidden' => $isHidden,
            'items' => $checklist->relationLoaded('items') ? $checklist->items : [],
            'created_at' => $checklist->created_at,
            'updated_at' => $checklist->updated_at,
        ];
    }
}
