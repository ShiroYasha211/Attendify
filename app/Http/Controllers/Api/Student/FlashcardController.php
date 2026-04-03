<?php

namespace App\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use App\Models\FlashcardPack;
use App\Models\FlashcardItem;
use App\Models\FlashcardProgress;
use App\Models\PublicPackStore;
use Illuminate\Support\Facades\DB;

class FlashcardController extends StudentApiController
{
    /**
     * جلب جميع الحزم الخاصة بالمستخدم.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $packs = FlashcardPack::forUser($user->id)
            ->withCount('items')
            ->latest()
            ->get();

        return $this->success([
            'packs' => $packs,
            'stats' => [
                'total_packs' => $packs->count(),
                'active_packs' => $packs->where('is_active', true)->count(),
                'total_cards' => $packs->sum('items_count'),
            ],
        ]);
    }

    /**
     * إنشاء حزمة جديدة.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'notifications_enabled' => 'boolean',
            'daily_notification_count' => 'integer|min:1|max:50',
            'repeat_cycle' => 'required|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|date_format:H:i',
            'quiet_end' => 'nullable|date_format:H:i',
        ]);

        $pack = $request->user()->flashcardPacks()->create($request->only([
            'title', 'description', 'color', 'display_mode',
            'notifications_enabled', 'daily_notification_count',
            'repeat_cycle', 'quiet_start', 'quiet_end',
        ]));

        $pack->loadCount('items');

        return $this->success(['pack' => $pack], 'تم إنشاء الحزمة بنجاح.', 201);
    }

    /**
     * تفاصيل حزمة + بطاقاتها.
     */
    public function show(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->firstOrFail();

        $items = $pack->effectiveItems()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->success([
            'pack' => $pack,
            'items' => $items,
        ]);
    }

    /**
     * تعديل حزمة.
     */
    public function update(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'notifications_enabled' => 'boolean',
            'daily_notification_count' => 'integer|min:1|max:50',
            'repeat_cycle' => 'required|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|date_format:H:i',
            'quiet_end' => 'nullable|date_format:H:i',
        ]);

        $pack->update($request->only([
            'title', 'description', 'color', 'display_mode',
            'notifications_enabled', 'daily_notification_count',
            'repeat_cycle', 'quiet_start', 'quiet_end',
        ]));

        $pack->loadCount('items');

        return $this->success(['pack' => $pack], 'تم تحديث الحزمة بنجاح.');
    }

    /**
     * حذف حزمة.
     */
    public function destroy(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $pack->delete();

        return $this->success(null, 'تم حذف الحزمة نهائياً.');
    }

    /**
     * تشغيل/إيقاف حزمة.
     */
    public function toggleActive(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $pack->update(['is_active' => !$pack->is_active]);

        return $this->success([
            'is_active' => $pack->is_active,
        ], $pack->is_active ? 'تم تفعيل الحزمة.' : 'تم إيقاف الحزمة.');
    }

    /**
     * تحديث إعدادات الإشعارات.
     */
    public function updateSettings(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'notifications_enabled' => 'boolean',
            'daily_notification_count' => 'integer|min:1|max:50',
            'repeat_cycle' => 'required|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|date_format:H:i',
            'quiet_end' => 'nullable|date_format:H:i',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
        ]);

        $pack->update($request->only([
            'notifications_enabled', 'daily_notification_count',
            'repeat_cycle', 'quiet_start', 'quiet_end', 'display_mode',
        ]));

        return $this->success(['pack' => $pack], 'تم تحديث إعدادات الإشعارات.');
    }

    /**
     * استيراد بطاقات من Excel/CSV.
     */
    public function import(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            $rows = $this->parseSpreadsheet($file->getPathname(), $extension);

            $items = [];
            foreach ($rows as $index => $row) {
                if (empty($row[0])) continue;

                $items[] = [
                    'pack_id' => $pack->id,
                    'front_content' => trim($row[0]),
                    'back_content' => ($pack->display_mode === 'one_line') ? null : (isset($row[1]) ? trim($row[1]) : null),
                    'priority' => 'normal',
                    'sort_order' => $pack->items()->count() + $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($items)) {
                return $this->error('الملف لا يحتوي على بيانات صالحة.', 422);
            }

            FlashcardItem::insert($items);

            return $this->success([
                'imported_count' => count($items),
            ], 'تم استيراد ' . count($items) . ' بطاقة بنجاح.');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء قراءة الملف: ' . $e->getMessage(), 500);
        }
    }

    /**
     * إضافة بطاقة واحدة.
     */
    public function storeItem(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'front_content' => 'required|string',
            'back_content' => 'nullable|string',
            'options' => 'nullable|array|min:2|max:6',
            'options.*' => 'string|max:500',
            'correct_option' => 'nullable|integer|min:0',
            'priority' => 'required|in:normal,high,critical',
        ]);

        $data = $request->only(['front_content', 'priority']);
        $data['back_content'] = ($pack->display_mode === 'one_line') ? null : $request->back_content;
        $data['sort_order'] = $pack->items()->count();

        if ($pack->display_mode === 'mcq' && $request->has('options')) {
            $data['options'] = $request->options;
            $data['correct_option'] = $request->correct_option;
        }

        $item = $pack->items()->create($data);

        return $this->success(['item' => $item], 'تم إضافة البطاقة بنجاح.', 201);
    }

    /**
     * تعديل بطاقة.
     */
    public function updateItem(Request $request, $itemId)
    {
        $item = FlashcardItem::findOrFail($itemId);

        if ($item->pack->user_id !== $request->user()->id) {
            return $this->error('ليس لديك صلاحية.', 403);
        }

        $request->validate([
            'front_content' => 'required|string',
            'back_content' => 'nullable|string',
            'options' => 'nullable|array|min:2|max:6',
            'options.*' => 'string|max:500',
            'correct_option' => 'nullable|integer|min:0',
            'priority' => 'required|in:normal,high,critical',
        ]);

        $data = $request->only(['front_content', 'priority']);
        $data['back_content'] = ($item->pack->display_mode === 'one_line') ? null : $request->back_content;

        if ($item->pack->display_mode === 'mcq' && $request->has('options')) {
            $data['options'] = $request->options;
            $data['correct_option'] = $request->correct_option;
        }

        $item->update($data);

        return $this->success(['item' => $item], 'تم تحديث البطاقة بنجاح.');
    }

    /**
     * حذف بطاقة.
     */
    public function destroyItem(Request $request, $itemId)
    {
        $item = FlashcardItem::findOrFail($itemId);

        if ($item->pack->user_id !== $request->user()->id) {
            return $this->error('ليس لديك صلاحية.', 403);
        }

        $item->delete();

        return $this->success(null, 'تم حذف البطاقة.');
    }

    /**
     * تصفح المتجر العام.
     */
    public function publicStore(Request $request)
    {
        $query = PublicPackStore::with(['pack' => function ($q) {
            $q->withCount('items')->select('id', 'title', 'description', 'color', 'icon', 'display_mode', 'user_id', 'is_public');
        }, 'pack.user:id,name'])
        ->where('is_active', true)
        ->whereHas('pack', fn($q) => $q->where('is_public', true));

        if ($request->filled('display_mode')) {
            $query->whereHas('pack', function ($q) use ($request) {
                $q->where('display_mode', $request->display_mode);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pack', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $storeItems = $query->latest()->paginate(15);

        return $this->success([
            'store_items' => $storeItems,
        ]);
    }

    /**
     * سحب حزمة عامة.
     */
    public function clonePack(Request $request, $id)
    {
        $user = $request->user();
        $sourcePack = FlashcardPack::where('is_public', true)->findOrFail($id);

        $existing = FlashcardPack::where('user_id', $user->id)
            ->where('source_pack_id', $sourcePack->id)
            ->first();

        if ($existing) {
            return $this->error('لقد قمت بسحب هذه الحزمة مسبقاً.', 409);
        }

        $newPack = DB::transaction(function () use ($sourcePack, $user) {
            $newPack = $sourcePack->replicate(['user_id', 'is_public', 'source_pack_id']);
            $newPack->user_id = $user->id;
            $newPack->is_public = false;
            $newPack->source_pack_id = $sourcePack->id;
            $newPack->save();

            // items are NOT replicated anymore to save space and ensure updates sync
            $sourcePack->storeEntry?->increment('downloads_count');

            return $newPack;
        });

        $newPack->loadCount('items');

        return $this->success(['pack' => $newPack], 'تم سحب الحزمة بنجاح!', 201);
    }

    /**
     * جلب بطاقات للمراجعة (مرتبة حسب الأولوية).
     */
    public function review(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $items = $pack->effectiveItems()
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'normal')")
            ->orderBy('sort_order')
            ->get();

        // Load user progress for each item
        $progressMap = FlashcardProgress::where('user_id', $request->user()->id)
            ->whereIn('item_id', $items->pluck('id'))
            ->get()
            ->keyBy('item_id');

        $items->each(function ($item) use ($progressMap) {
            $item->user_progress = $progressMap->get($item->id);
        });

        return $this->success([
            'pack' => $pack->only(['id', 'title', 'display_mode', 'color']),
            'items' => $items,
        ]);
    }

    /**
     * تسجيل تقدم مراجعة.
     */
    public function recordProgress(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:flashcard_items,id',
            'is_correct' => 'required|boolean',
        ]);

        $user = $request->user();
        $item = FlashcardItem::findOrFail($request->item_id);

        // Security Check: User must own either the pack directly OR a pack that references this item
        $ownsPack = FlashcardPack::where('user_id', $user->id)
            ->where(function($q) use ($item) {
                $q->where('id', $item->pack_id)
                  ->orWhere('source_pack_id', $item->pack_id);
            })->exists();

        if (!$ownsPack) {
            return $this->error('ليس لديك صلاحية لتسجيل التقدم لهذا العنصر.', 403);
        }

        $progress = FlashcardProgress::firstOrCreate(
            ['user_id' => $user->id, 'item_id' => $item->id],
            ['times_shown' => 0, 'times_correct' => 0]
        );

        $progress->increment('times_shown');
        if ($request->is_correct) {
            $progress->increment('times_correct');
        }

        $nextReview = $request->is_correct
            ? now()->addDays(min(30, pow(2, $progress->times_correct)))
            : now()->addDay();

        $progress->update([
            'last_shown_at' => now(),
            'next_review_at' => $nextReview,
        ]);

        return $this->success([
            'progress' => $progress->fresh(),
        ], 'تم تسجيل التقدم.');
    }

    /**
     * قائمة الإشعارات اليومية (Daily Queue).
     * يستخدمها Flutter لجدولة الإشعارات المحلية.
     */
    public function getDailyQueue(Request $request)
    {
        $user = $request->user();

        $packs = FlashcardPack::forUser($user->id)
            ->active()
            ->where('notifications_enabled', true)
            ->get();

        $queue = [];

        foreach ($packs as $pack) {
            $items = $pack->effectiveItems()->get();
            if ($items->isEmpty()) continue;

            // Prioritize high/critical items
            $prioritized = $items->sortBy(function ($item) {
                return match ($item->priority) {
                    'critical' => 0,
                    'high' => 1,
                    'normal' => 2,
                    default => 3,
                };
            });

            // Pick N items based on daily_notification_count
            $selected = $prioritized->take($pack->daily_notification_count);

            // Calculate time slots (distribute evenly, respecting quiet hours)
            $startHour = 8;
            $endHour = 22;

            if ($pack->quiet_start && $pack->quiet_end) {
                $qs = (int) substr($pack->quiet_start, 0, 2);
                $qe = (int) substr($pack->quiet_end, 0, 2);
                // Simple: use hours outside quiet period
                if ($qs < $qe) {
                    // quiet in the middle of the day
                    $startHour = $qe;
                } else {
                    $endHour = $qs;
                }
            }

            $count = $selected->count();
            $intervalMinutes = $count > 1 ? (($endHour - $startHour) * 60) / ($count - 1) : 0;

            $index = 0;
            foreach ($selected as $item) {
                $minutesOffset = (int) ($index * $intervalMinutes);
                $scheduledTime = now()->startOfDay()
                    ->addHours($startHour)
                    ->addMinutes($minutesOffset);

                $queue[] = [
                    'item_id' => $item->id,
                    'pack_id' => $pack->id,
                    'pack_title' => $pack->title,
                    'pack_color' => $pack->color,
                    'display_mode' => $pack->display_mode,
                    'front_content' => $item->front_content,
                    'back_content' => $item->back_content,
                    'options' => $item->options,
                    'correct_option' => $item->correct_option,
                    'priority' => $item->priority,
                    'scheduled_time' => $scheduledTime->format('H:i'),
                ];

                $index++;
            }
        }

        // Sort by scheduled time
        usort($queue, fn($a, $b) => strcmp($a['scheduled_time'], $b['scheduled_time']));

        return $this->success([
            'queue' => $queue,
            'total' => count($queue),
            'date' => now()->toDateString(),
        ]);
    }

    // ── Private Helpers ──

    /**
     * Parse Excel/CSV file.
     */
    private function parseSpreadsheet(string $path, string $extension): array
    {
        $rows = [];

        if ($extension === 'csv') {
            $handle = fopen($path, 'r');
            $isFirst = true;
            while (($data = fgetcsv($handle)) !== false) {
                if ($isFirst && $this->looksLikeHeader($data)) {
                    $isFirst = false;
                    continue;
                }
                $isFirst = false;
                $rows[] = $data;
            }
            fclose($handle);
        } else {
            if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $worksheet = $spreadsheet->getActiveSheet();
                $isFirst = true;
                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    $data = [];
                    foreach ($cellIterator as $cell) {
                        $data[] = $cell->getValue();
                    }
                    if ($isFirst && $this->looksLikeHeader($data)) {
                        $isFirst = false;
                        continue;
                    }
                    $isFirst = false;
                    $rows[] = $data;
                }
            } else {
                throw new \RuntimeException('يرجى تثبيت حزمة phpoffice/phpspreadsheet لدعم ملفات Excel.');
            }
        }

        return $rows;
    }

    private function looksLikeHeader(array $row): bool
    {
        $headerKeywords = ['front', 'back', 'سؤال', 'جواب', 'question', 'answer', 'العمود', 'column', 'a', 'b'];
        foreach ($row as $cell) {
            if (in_array(strtolower(trim($cell ?? '')), $headerKeywords)) {
                return true;
            }
        }
        return false;
    }
}
