<?php

namespace App\Http\Controllers\Api\Admin\Clinical;

use App\Http\Controllers\Api\Admin\AdminApiController;
use App\Models\Clinical\EvaluationChecklist;
use App\Models\Clinical\ChecklistItem;
use Illuminate\Http\Request;

class EvaluationChecklistController extends AdminApiController
{
    public function index(Request $request)
    {
        $query = EvaluationChecklist::whereNull('doctor_id')->with('items');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        return $this->paginated($query->latest()->paginate($request->per_page ?? 15));
    }

    public function show($id)
    {
        $checklist = EvaluationChecklist::whereNull('doctor_id')->with('items.subItems')->findOrFail($id);
        return $this->success($checklist);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'is_practice_allowed' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.marks' => 'required|integer|min:1',
            'items.*.sub_items' => 'nullable|array',
        ]);

        $totalMarks = collect($request->items)->sum('marks');

        $checklist = EvaluationChecklist::create([
            'title' => $request->title,
            'description' => $request->description,
            'doctor_id' => null,
            'skill_type' => $request->skill_type,
            'time_limit_minutes' => $request->time_limit_minutes,
            'is_practice_allowed' => $request->input('is_practice_allowed', true),
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
                        'marks' => $subItem['marks'] ?? 0,
                        'sort_order' => $j + 1,
                    ]);
                }
            }
        }

        return $this->success($checklist->load('items.subItems'), 'تم إنشاء قائمة التقييم بنجاح.', 201);
    }

    public function update(Request $request, $id)
    {
        $checklist = EvaluationChecklist::whereNull('doctor_id')->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill_type' => 'required|in:history_taking,clinical_examination,procedure,communication',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'is_practice_allowed' => 'boolean',
            'items' => 'required|array|min:1',
        ]);

        $totalMarks = collect($request->items)->sum('marks');

        $checklist->update([
            'title' => $request->title,
            'description' => $request->description,
            'skill_type' => $request->skill_type,
            'time_limit_minutes' => $request->time_limit_minutes,
            'is_practice_allowed' => $request->input('is_practice_allowed', $checklist->is_practice_allowed),
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
                        'marks' => $subItem['marks'] ?? 0,
                        'sort_order' => $j + 1,
                    ]);
                }
            }
        }

        return $this->success($checklist->load('items.subItems'), 'تم تحديث قائمة التقييم بنجاح.');
    }

    public function destroy($id)
    {
        $checklist = EvaluationChecklist::whereNull('doctor_id')->findOrFail($id);
        $checklist->items()->delete();
        $checklist->delete();
        return $this->success(null, 'تم حذف قائمة التقييم بنجاح.');
    }
}
