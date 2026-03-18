<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use App\Models\Student\StudentScheduleItem;
use App\Models\Academic\Lecture;
use App\Models\CourseResource;
use Carbon\Carbon;

class StudentScheduleController extends StudentApiController
{
    /**
     * Display the Smart Study Hub items.
     */
    public function index(Request $request)
    {
        $student = $request->user();

        $tab = $request->get('tab', 'study'); // study | reminders | resources | assignments

        // ─── Dashboard Stats ───
        $stats = [
            'today' => StudentScheduleItem::where('user_id', $student->id)
                ->pending()->today()->count(),
            'overdue' => StudentScheduleItem::where('user_id', $student->id)
                ->overdue()->count(),
            'completed' => StudentScheduleItem::where('user_id', $student->id)
                ->completed()
                ->where('completed_at', '>=', now()->startOfWeek())
                ->count(),
            'high_priority' => StudentScheduleItem::where('user_id', $student->id)
                ->pending()->highPriority()->count(),
            'total_pending' => StudentScheduleItem::where('user_id', $student->id)
                ->pending()->count(),
            'study_count' => StudentScheduleItem::where('user_id', $student->id)
                ->studyItems()->pending()->count(),
            'reminders_count' => StudentScheduleItem::where('user_id', $student->id)
                ->reminders()->pending()->count(),
            'assignments_count' => StudentScheduleItem::where('user_id', $student->id)
                ->where('item_type', 'assignment')->pending()->count(),
            'resources_count' => StudentScheduleItem::where('user_id', $student->id)
                ->myResources()->count(),
        ];

        // ─── Fetch items based on active tab ───
        $query = StudentScheduleItem::where('user_id', $student->id)
            ->with('referenceable');

        switch ($tab) {
            case 'reminders':
                $query->reminders()
                    ->where('is_completed', false)
                    ->orderByRaw("scheduled_date < CURDATE() DESC")
                    ->orderBy('scheduled_date', 'asc')
                    ->orderBy('reminder_at', 'asc');
                break;

            case 'assignments':
                $query->where('item_type', 'assignment')
                    ->orderByRaw("is_completed ASC")
                    ->orderByRaw("scheduled_date < CURDATE() DESC")
                    ->orderBy('scheduled_date', 'asc');
                break;

            case 'resources':
                $query->myResources()
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('created_at', 'desc');
                break;

            default: // 'study'
                $query->studyItems()
                    ->orderByRaw("CASE WHEN is_completed = 1 THEN 1 ELSE 0 END ASC")
                    ->orderByRaw('scheduled_date IS NULL ASC')
                    ->orderBy('scheduled_date', 'asc')
                    ->orderBy('sort_order', 'asc')
                    ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 WHEN 'low' THEN 2 END ASC");
                break;
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'overdue':
                    $query->overdue();
                    break;
                case 'completed':
                    $query->completed();
                    break;
                case 'today':
                    $query->today();
                    break;
                case 'high':
                    $query->highPriority();
                    break;
            }
        }

        $items = $query->get();

        foreach ($items as $item) {
            if ($item->is_overdue && $item->status !== 'overdue') {
                $item->update(['status' => 'overdue']);
            }
        }

        return $this->success([
            'stats' => $stats,
            'tab' => $tab,
            'items' => $items,
        ]);
    }

    /**
     * Store a schedule item (from lectures/resources pages).
     */
    public function store(Request $request)
    {
        $request->validate([
            'referenceable_type' => 'nullable|string',
            'referenceable_id' => 'nullable|integer',
            'title' => 'nullable|string|max:255',
            'scheduled_date' => 'nullable|date',
            'note' => 'nullable|string',
            'priority' => 'nullable|in:high,medium,low',
            'item_type' => 'nullable|in:study,reminder,resource',
            'category_tag' => 'nullable|string|max:100',
            'repeat_type' => 'nullable|in:none,daily,weekly',
            'reminder_at' => 'nullable|date',
        ]);

        $student = $request->user();

        $modelClass = null;
        if ($request->referenceable_type) {
            $typeMap = [
                'lecture' => Lecture::class,
                'resource' => CourseResource::class,
            ];
            $modelClass = $typeMap[$request->referenceable_type] ?? $request->referenceable_type;

            if (!class_exists($modelClass)) {
                return $this->error('نوع المحتوى غير صالح.', 400);
            }

            if ($request->referenceable_id && !$modelClass::find($request->referenceable_id)) {
                return $this->error('المحتوى غير موجود.', 404);
            }

            $exists = StudentScheduleItem::where('user_id', $student->id)
                ->where('referenceable_type', $modelClass)
                ->where('referenceable_id', $request->referenceable_id)
                ->exists();

            if ($exists) {
                return $this->error('هذا المصدر مضاف بالفعل إلى قائمتك.', 400);
            }
        }

        $itemType = $request->item_type ?? 'study';

        if (!$request->item_type && $modelClass) {
            $itemType = ($modelClass === CourseResource::class) ? 'resource' : 'study';
        }

        $item = StudentScheduleItem::create([
            'user_id' => $student->id,
            'referenceable_type' => $modelClass,
            'referenceable_id' => $request->referenceable_id,
            'title' => $request->title,
            'scheduled_date' => $request->scheduled_date,
            'note' => $request->note,
            'priority' => $request->priority ?? 'medium',
            'item_type' => $itemType,
            'category_tag' => $request->category_tag,
            'repeat_type' => $request->repeat_type ?? 'none',
            'reminder_at' => $request->reminder_at,
            'sort_order' => StudentScheduleItem::where('user_id', $student->id)->max('sort_order') + 1,
        ]);

        return $this->success(['item' => $item], 'تمت الإضافة إلى مركز الدراسة بنجاح', 201);
    }

    /**
     * Store a custom task.
     */
    public function storeCustomTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'scheduled_date' => 'nullable|date',
            'note' => 'nullable|string',
            'priority' => 'nullable|in:high,medium,low',
            'item_type' => 'required|in:study,reminder,resource',
            'category_tag' => 'nullable|string|max:100',
            'repeat_type' => 'nullable|in:none,daily,weekly',
            'reminder_at' => 'nullable|date',
        ]);

        $student = $request->user();

        $item = StudentScheduleItem::create([
            'user_id' => $student->id,
            'referenceable_type' => null,
            'referenceable_id' => null,
            'title' => $request->title,
            'scheduled_date' => $request->scheduled_date,
            'note' => $request->note,
            'priority' => $request->priority ?? 'medium',
            'item_type' => $request->item_type,
            'category_tag' => $request->category_tag,
            'repeat_type' => $request->repeat_type ?? 'none',
            'reminder_at' => $request->reminder_at,
            'sort_order' => StudentScheduleItem::where('user_id', $student->id)->max('sort_order') + 1,
        ]);

        return $this->success(['item' => $item], 'تمت إضافة المهمة بنجاح', 201);
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, $id)
    {
        $student = $request->user();
        $item = StudentScheduleItem::where('user_id', $student->id)->findOrFail($id);
        $message = 'تم التحديث بنجاح';

        $request->validate([
            'is_completed' => 'sometimes|boolean',
            'scheduled_date' => 'nullable|date',
            'note' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'priority' => 'nullable|in:high,medium,low',
            'status' => 'nullable|in:pending,in_progress,completed,overdue',
            'category_tag' => 'nullable|string|max:100',
            'reminder_at' => 'nullable|date',
        ]);

        if ($request->has('is_completed') && $request->is_completed && $item->repeat_type !== 'none') {
            $currentDate = $item->scheduled_date ? Carbon::parse($item->scheduled_date) : Carbon::today();
            $originalReminderTime = $item->reminder_at ? Carbon::parse($item->reminder_at)->format('H:i:s') : null;

            $nextDate = $currentDate->copy();

            if ($item->repeat_type === 'daily') {
                $nextDate = $currentDate->isPast() ? Carbon::tomorrow() : $currentDate->addDay();
            } elseif ($item->repeat_type === 'weekly') {
                $nextDate = $currentDate->isPast() ? Carbon::today()->addWeek() : $currentDate->addWeek();
            }

            $newItem = $item->replicate();
            $newItem->scheduled_date = $nextDate;
            $newItem->status = 'pending';
            $newItem->is_completed = false;
            $newItem->completed_at = null;
            $newItem->reminder_sent = false;

            if ($originalReminderTime) {
                $newItem->reminder_at = $nextDate->copy()->setTimeFromTimeString($originalReminderTime);
            }

            $newItem->save();
            $message = '✅ تم الإنجاز! تم جدولة الجلسة القادمة ليوم ' . $nextDate->isoFormat('dddd Y-MM-DD');
        }

        if ($request->has('is_completed')) {
            $item->is_completed = $request->is_completed;
            $item->completed_at = $request->is_completed ? now() : null;
            $item->status = $request->is_completed ? 'completed' : 'pending';
        }

        if ($request->has('scheduled_date')) $item->scheduled_date = $request->scheduled_date;
        if ($request->has('note')) $item->note = $request->note;
        if ($request->has('title')) $item->title = $request->title;
        if ($request->has('priority')) $item->priority = $request->priority;
        if ($request->has('status')) $item->status = $request->status;
        if ($request->has('category_tag')) $item->category_tag = $request->category_tag;
        if ($request->has('reminder_at')) $item->reminder_at = $request->reminder_at;

        $item->save();

        return $this->success(['item' => $item], $message);
    }

    /**
     * Remove from storage.
     */
    public function destroy(Request $request, $id)
    {
        $student = $request->user();
        $item = StudentScheduleItem::where('user_id', $student->id)->findOrFail($id);
        $item->delete();

        return $this->success([], 'تم الحذف من الجدول');
    }

    /**
     * Reorder items.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.order' => 'required|integer',
        ]);

        $student = $request->user();

        foreach ($request->items as $itemData) {
            StudentScheduleItem::where('user_id', $student->id)
                ->where('id', $itemData['id'])
                ->update(['sort_order' => $itemData['order']]);
        }

        return $this->success([], 'تم إعادة الترتيب بنجاح.');
    }

    /**
     * Check for due reminders.
     */
    public function checkReminders(Request $request)
    {
        $student = $request->user();

        $dueReminders = StudentScheduleItem::where('user_id', $student->id)
            ->where('reminder_sent', false)
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', now())
            ->where('is_completed', false)
            ->get();

        $results = [];
        foreach ($dueReminders as $reminder) {
            $reminder->update(['reminder_sent' => true]);
            $results[] = [
                'id' => $reminder->id,
                'title' => $reminder->display_title,
                'scheduled_date' => $reminder->scheduled_date?->format('Y-m-d'),
                'priority' => $reminder->priority,
            ];
        }

        return $this->success([
            'reminders' => $results,
            'count' => count($results),
        ]);
    }
}
