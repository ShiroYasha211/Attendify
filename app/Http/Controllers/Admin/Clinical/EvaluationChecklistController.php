<?php

namespace App\Http\Controllers\Admin\Clinical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinical\EvaluationChecklist;
use App\Models\Clinical\ChecklistItem;

class EvaluationChecklistController extends Controller
{
    /**
     * Display a listing of the standard checklists.
     */
    public function index(Request $request)
    {
        $query = EvaluationChecklist::whereNull('doctor_id')->with('items');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('skill_type')) {
            $query->where('skill_type', $request->skill_type);
        }

        $checklists = $query->latest()->paginate(15)->withQueryString();

        return view('admin.clinical.checklists.index', compact('checklists'));
    }

    /**
     * Show the form for creating a new standard checklist.
     */
    public function create()
    {
        return view('admin.clinical.checklists.create');
    }

    /**
     * Store a newly created standard checklist in storage.
     */
    public function store(Request $request)
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
                    return back()->withInput()->with('error', "مجموع درجات العناصر الفرعية يجب أن يساوي درجة العنصر الرئيسي '{$item['description']}' ({$item['marks']}).");
                }
            }
        }

        $checklist = EvaluationChecklist::create([
            'title' => $request->title,
            'description' => $request->description,
            'doctor_id' => null, // Global Standard Checklist
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

        return redirect()->route('admin.clinical.checklists.index')->with('success', 'تم إنشاء قائمة التقييم الأساسية بنجاح.');
    }

    /**
     * Display the specified checklist.
     */
    public function show(string $id)
    {
        $checklist = EvaluationChecklist::whereNull('doctor_id')->with('items.subItems')->findOrFail($id);
        return view('admin.clinical.checklists.show', compact('checklist'));
    }

    /**
     * Show the form for editing the standard checklist.
     */
    public function edit(string $id)
    {
        $checklist = EvaluationChecklist::whereNull('doctor_id')->with('items.subItems')->findOrFail($id);
        
        // Prepare data for JS
        $itemsData = $checklist->items->whereNull('parent_id')->map(function ($mainItem) {
            return [
                'id' => $mainItem->id,
                'description' => $mainItem->description,
                'marks' => $mainItem->marks,
                'sub_items' => $mainItem->subItems->map(function ($subItem) {
                    return [
                        'id' => $subItem->id,
                        'description' => $subItem->description,
                        'marks' => $subItem->marks,
                    ];
                })->values()->toArray()
            ];
        })->values()->toArray();

        return view('admin.clinical.checklists.edit', compact('checklist', 'itemsData'));
    }

    /**
     * Update the standard checklist in storage.
     */
    public function update(Request $request, string $id)
    {
        $checklist = EvaluationChecklist::whereNull('doctor_id')->findOrFail($id);

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
                    return back()->withInput()->with('error', "مجموع درجات العناصر الفرعية يجب أن يساوي درجة العنصر الرئيسي '{$item['description']}' ({$item['marks']}).");
                }
            }
        }

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

        return redirect()->route('admin.clinical.checklists.index')->with('success', 'تم تحديث قائمة التقييم الأساسية بنجاح.');
    }

    /**
     * Remove the standard checklist from storage.
     */
    public function destroy(string $id)
    {
        $checklist = EvaluationChecklist::whereNull('doctor_id')->findOrFail($id);
        $checklist->items()->delete();
        $checklist->delete();
        return redirect()->route('admin.clinical.checklists.index')->with('success', 'تم حذف قائمة التقييم الأساسية بنجاح.');
    }
}
