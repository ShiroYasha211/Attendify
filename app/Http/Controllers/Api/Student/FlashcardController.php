<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\FlashcardItem;
use App\Models\FlashcardPack;
use App\Models\FlashcardProgress;
use App\Models\PublicPackStore;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FlashcardController extends StudentApiController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $packs = FlashcardPack::forUser($user->id)
            ->whereNull('parent_pack_id')
            ->with('childPacks:id,parent_pack_id,title,color,display_mode,is_active,source_pack_id')
            ->latest()
            ->get();

        $packs->each(function (FlashcardPack $pack) {
            $pack->items_count = $pack->cardsCount();
            $pack->children_count = $pack->childPacks->count();
        });

        return $this->success([
            'packs' => $packs,
            'stats' => [
                'total_packs' => $packs->count(),
                'active_packs' => $packs->where('is_active', true)->count(),
                'total_cards' => $packs->sum('items_count'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'notifications_enabled' => 'boolean',
            'daily_notification_count' => 'integer|min:1|max:50',
            'repeat_cycle' => 'required|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|date_format:H:i',
            'quiet_end' => 'nullable|date_format:H:i',
            'parent_pack_id' => 'nullable|integer',
        ]);

        $parentPack = $this->resolveParentPack($validated['parent_pack_id'] ?? null, $request->user()->id);

        $pack = $request->user()->flashcardPacks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#4f46e5',
            'display_mode' => $validated['display_mode'],
            'notifications_enabled' => (bool) ($validated['notifications_enabled'] ?? false),
            'daily_notification_count' => $validated['daily_notification_count'] ?? 5,
            'repeat_cycle' => $validated['repeat_cycle'],
            'quiet_start' => $validated['quiet_start'] ?? null,
            'quiet_end' => $validated['quiet_end'] ?? null,
            'parent_pack_id' => $parentPack?->id,
        ]);

        return $this->success(['pack' => $pack], 'Pack created successfully.', 201);
    }

    public function show(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('childPacks:id,user_id,parent_pack_id,title,color,display_mode,is_active,source_pack_id')
            ->firstOrFail();

        $items = $this->displayItemsForPack($pack)->values();
        $pack->items_count = $pack->cardsCount();

        return $this->success([
            'pack' => $pack,
            'items' => $items,
            'child_packs' => $pack->childPacks->map(function (FlashcardPack $childPack) {
                $childPack->items_count = $childPack->cardsCount();

                return $childPack;
            })->values(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'notifications_enabled' => 'boolean',
            'daily_notification_count' => 'integer|min:1|max:50',
            'repeat_cycle' => 'required|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|date_format:H:i',
            'quiet_end' => 'nullable|date_format:H:i',
            'parent_pack_id' => 'nullable|integer',
        ]);

        $parentPack = $this->resolveParentPack($validated['parent_pack_id'] ?? null, $request->user()->id, $pack);

        $pack->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#4f46e5',
            'display_mode' => $validated['display_mode'],
            'notifications_enabled' => (bool) ($validated['notifications_enabled'] ?? false),
            'daily_notification_count' => $validated['daily_notification_count'] ?? 5,
            'repeat_cycle' => $validated['repeat_cycle'],
            'quiet_start' => $validated['quiet_start'] ?? null,
            'quiet_end' => $validated['quiet_end'] ?? null,
            'parent_pack_id' => $pack->is_assigned ? $pack->parent_pack_id : $parentPack?->id,
        ]);

        return $this->success(['pack' => $pack], 'Pack updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->deletePackTree($pack);

        return $this->success(null, 'Pack deleted successfully.');
    }

    public function toggleActive(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $pack->update(['is_active' => !$pack->is_active]);

        return $this->success(['is_active' => $pack->is_active], 'Pack status updated.');
    }

    public function updateSettings(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'notifications_enabled' => 'boolean',
            'daily_notification_count' => 'integer|min:1|max:50',
            'repeat_cycle' => 'required|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|date_format:H:i',
            'quiet_end' => 'nullable|date_format:H:i',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
        ]);

        $pack->update([
            'notifications_enabled' => (bool) ($validated['notifications_enabled'] ?? false),
            'daily_notification_count' => $validated['daily_notification_count'] ?? $pack->daily_notification_count,
            'repeat_cycle' => $validated['repeat_cycle'],
            'quiet_start' => $validated['quiet_start'] ?? null,
            'quiet_end' => $validated['quiet_end'] ?? null,
            'display_mode' => $validated['display_mode'],
        ]);

        return $this->success(['pack' => $pack], 'Pack settings updated.');
    }

    public function import(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->assertCanManageItems($pack);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            $rows = $this->parseSpreadsheet($file->getPathname(), $extension);
            $itemsCountBefore = $pack->items()->count();
            $items = [];

            foreach ($rows as $index => $row) {
                if (empty($row[0])) {
                    continue;
                }

                $itemData = [
                    'pack_id' => $pack->id,
                    'item_type' => $pack->display_mode,
                    'front_content' => trim((string) $row[0]),
                    'priority' => 'normal',
                    'sort_order' => $itemsCountBefore + $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                switch ($pack->display_mode) {
                    case 'one_line':
                        $itemData['back_content'] = null;
                        break;
                    case 'mcq':
                        $options = [];
                        foreach ([1, 2, 3, 4, 5, 6] as $columnIndex) {
                            if (!empty($row[$columnIndex])) {
                                $options[] = trim((string) $row[$columnIndex]);
                            }
                        }
                        $itemData['options'] = json_encode($options);
                        $itemData['correct_option'] = isset($row[7]) && is_numeric($row[7]) ? (int) $row[7] : 0;
                        $itemData['back_content'] = null;
                        break;
                    default:
                        $itemData['back_content'] = isset($row[1]) ? trim((string) $row[1]) : null;
                        break;
                }

                $items[] = $itemData;
            }

            if (empty($items)) {
                return $this->error('No valid rows found in the file.', 422);
            }

            FlashcardItem::insert($items);

            return $this->success(['imported_count' => count($items)], 'Items imported successfully.');
        } catch (\Exception $e) {
            return $this->error('Failed to read spreadsheet: ' . $e->getMessage(), 500);
        }
    }

    public function storeItem(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->assertCanManageItems($pack);

        $itemType = $request->input('item_type', $pack->display_mode);
        $validated = $this->validateItemPayload($request, $itemType);
        $item = $pack->items()->create($this->mapItemData($validated, $pack, $itemType));

        return $this->success(['item' => $item], 'Item created successfully.', 201);
    }

    public function updateItem(Request $request, $itemId)
    {
        $item = FlashcardItem::with('pack')->findOrFail($itemId);

        if ($item->pack->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        $this->assertCanManageItems($item->pack);

        $itemType = $request->input('item_type', $item->resolved_item_type);
        $validated = $this->validateItemPayload($request, $itemType);

        $item->update($this->mapItemData($validated, $item->pack, $itemType, false));

        return $this->success(['item' => $item->fresh()], 'Item updated successfully.');
    }

    public function destroyItem(Request $request, $itemId)
    {
        $item = FlashcardItem::with('pack')->findOrFail($itemId);

        if ($item->pack->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        $this->assertCanManageItems($item->pack);
        $item->delete();

        return $this->success(null, 'Item deleted successfully.');
    }

    public function publicStore(Request $request)
    {
        $query = PublicPackStore::with(['pack' => function ($q) {
            $q->withCount('items')->select('id', 'title', 'description', 'color', 'icon', 'display_mode', 'user_id', 'is_public');
        }, 'pack.user:id,name'])
            ->where('is_active', true)
            ->whereHas('pack', fn ($q) => $q->where('is_public', true));

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
        $storeItems->getCollection()->transform(function (PublicPackStore $storeItem) {
            $storeItem->pack->items_count = $storeItem->pack->cardsCount();

            return $storeItem;
        });

        return $this->success([
            'store_items' => $storeItems,
        ]);
    }

    public function clonePack(Request $request, $id)
    {
        $user = $request->user();
        $sourcePack = FlashcardPack::where('is_public', true)->findOrFail($id);

        $existing = FlashcardPack::where('user_id', $user->id)
            ->where('source_pack_id', $sourcePack->id)
            ->whereNull('parent_pack_id')
            ->first();

        if ($existing) {
            return $this->error('This pack is already in your account.', 409);
        }

        $newPack = DB::transaction(function () use ($sourcePack, $user) {
            $clone = $this->cloneAssignedTree($sourcePack, $user->id);
            $sourcePack->storeEntry?->increment('downloads_count');

            return $clone;
        });

        return $this->success(['pack' => $newPack], 'Pack cloned successfully.', 201);
    }

    public function review(Request $request, $id)
    {
        $pack = FlashcardPack::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $items = $pack->effectiveItems()
            ->with('pack:id,title,color,display_mode')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'normal')")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $progressMap = FlashcardProgress::where('user_id', $request->user()->id)
            ->whereIn('item_id', $items->pluck('id'))
            ->get()
            ->keyBy('item_id');

        $items = $items
            ->map(function (FlashcardItem $item) use ($progressMap) {
                $item->user_progress = $progressMap->get($item->id);

                return $item;
            })
            ->sortBy(function (FlashcardItem $item) {
                $progress = $item->user_progress;

                return [
                    $progress?->isDue() === false ? 1 : 0,
                    $progress?->review_weight ? -1 * $progress->review_weight : -2,
                    match ($item->priority) {
                        'critical' => 0,
                        'high' => 1,
                        default => 2,
                    },
                    $item->sort_order,
                ];
            })
            ->values();

        return $this->success([
            'pack' => $pack->only(['id', 'title', 'display_mode', 'color', 'repeat_cycle']),
            'items' => $items,
        ]);
    }

    public function recordProgress(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:flashcard_items,id',
            'response_level' => 'nullable|in:easy,medium,hard',
            'is_correct' => 'nullable|boolean',
        ]);

        $responseLevel = $validated['response_level'] ?? $this->mapLegacyResponse((bool) ($validated['is_correct'] ?? false));
        $user = $request->user();
        $item = FlashcardItem::with('pack')->findOrFail($validated['item_id']);

        if (!$this->userCanAccessItemPack($user->id, $item->pack_id)) {
            return $this->error('Unauthorized for this item.', 403);
        }

        $progress = FlashcardProgress::firstOrCreate(
            ['user_id' => $user->id, 'item_id' => $item->id],
            ['times_shown' => 0, 'times_correct' => 0, 'review_weight' => 2]
        );

        $progress->times_shown += 1;
        if (in_array($responseLevel, ['easy', 'medium'], true)) {
            $progress->times_correct += 1;
        }

        $progress->last_response = $responseLevel;
        $progress->review_weight = match ($responseLevel) {
            'easy' => 1,
            'medium' => 2,
            'hard' => 3,
        };
        $progress->last_shown_at = now();
        $progress->next_review_at = $this->calculateNextReview($item->pack, $progress, $responseLevel);
        $progress->save();

        return $this->success(['progress' => $progress->fresh()], 'Progress recorded successfully.');
    }

    public function getDailyQueue(Request $request)
    {
        $user = $request->user();
        $packs = FlashcardPack::forUser($user->id)
            ->whereNull('parent_pack_id')
            ->active()
            ->where('notifications_enabled', true)
            ->get();

        $queue = [];

        foreach ($packs as $pack) {
            $items = $pack->effectiveItems()
                ->with('pack:id,title,color,display_mode')
                ->get();

            if ($items->isEmpty()) {
                continue;
            }

            $progressMap = FlashcardProgress::where('user_id', $user->id)
                ->whereIn('item_id', $items->pluck('id'))
                ->get()
                ->keyBy('item_id');

            $dueItems = $items
                ->map(function (FlashcardItem $item) use ($progressMap) {
                    $item->user_progress = $progressMap->get($item->id);

                    return $item;
                })
                ->filter(fn (FlashcardItem $item) => !$item->user_progress || $item->user_progress->isDue())
                ->sortBy(function (FlashcardItem $item) {
                    return [
                        -1 * ($item->user_progress?->review_weight ?? 2),
                        match ($item->priority) {
                            'critical' => 0,
                            'high' => 1,
                            default => 2,
                        },
                        $item->sort_order,
                    ];
                })
                ->values();

            if ($dueItems->isEmpty()) {
                $dueItems = $items->sortBy('sort_order')->values();
            }

            $selected = $dueItems->take($pack->daily_notification_count);
            [$startHour, $endHour] = $this->resolveQueueHours($pack);
            $count = $selected->count();
            $intervalMinutes = $count > 1 ? (($endHour - $startHour) * 60) / ($count - 1) : 0;

            foreach ($selected->values() as $index => $item) {
                $minutesOffset = (int) round($index * $intervalMinutes);
                $scheduledTime = now()->copy()->startOfDay()->addHours($startHour)->addMinutes($minutesOffset);

                $queue[] = [
                    'item_id' => $item->id,
                    'pack_id' => $pack->id,
                    'pack_title' => $pack->title,
                    'pack_color' => $pack->color,
                    'item_type' => $item->resolved_item_type,
                    'item_color' => $item->resolved_color,
                    'front_content' => $item->front_content,
                    'back_content' => $item->back_content,
                    'options' => $item->options,
                    'correct_option' => $item->correct_option,
                    'priority' => $item->priority,
                    'scheduled_time' => $scheduledTime->format('H:i'),
                    'response_actions' => [
                        ['key' => 'easy', 'label' => 'سهل', 'effect' => 'less_frequent'],
                        ['key' => 'medium', 'label' => 'متوسط', 'effect' => 'balanced'],
                        ['key' => 'hard', 'label' => 'صعب', 'effect' => 'high_priority'],
                    ],
                ];
            }
        }

        usort($queue, fn ($a, $b) => strcmp($a['scheduled_time'], $b['scheduled_time']));

        return $this->success([
            'queue' => $queue,
            'total' => count($queue),
            'date' => now()->toDateString(),
        ]);
    }

    private function resolveParentPack(?int $parentPackId, int $userId, ?FlashcardPack $currentPack = null): ?FlashcardPack
    {
        if (!$parentPackId) {
            return null;
        }

        $parentPack = FlashcardPack::where('id', $parentPackId)
            ->where('user_id', $userId)
            ->whereNull('source_pack_id')
            ->firstOrFail();

        if ($currentPack && in_array($parentPack->id, $currentPack->descendantPackIds(), true)) {
            abort(422, 'Cannot move a pack inside one of its descendants.');
        }

        return $parentPack;
    }

    private function assertCanManageItems(FlashcardPack $pack): void
    {
        if ($pack->is_assigned) {
            abort(422, 'Assigned packs cannot be edited directly.');
        }
    }

    private function validateItemPayload(Request $request, string $itemType): array
    {
        $rules = [
            'item_type' => 'nullable|in:flash_card,one_line,qa,mcq',
            'front_content' => 'required|string',
            'item_color' => 'nullable|string|max:7',
            'priority' => 'required|in:normal,high,critical',
        ];

        if ($itemType === 'mcq') {
            $rules['options'] = 'required|array|min:2|max:6';
            $rules['options.*'] = 'required|string|max:500';
            $rules['correct_option'] = 'required|integer|min:0';
        } elseif ($itemType !== 'one_line') {
            $rules['back_content'] = 'required|string';
        }

        return $request->validate($rules);
    }

    private function mapItemData(array $validated, FlashcardPack $pack, string $itemType, bool $isCreate = true): array
    {
        $data = [
            'item_type' => $itemType,
            'front_content' => $validated['front_content'],
            'back_content' => $itemType === 'one_line' || $itemType === 'mcq' ? null : ($validated['back_content'] ?? null),
            'item_color' => $validated['item_color'] ?? null,
            'priority' => $validated['priority'],
        ];

        if ($isCreate) {
            $data['sort_order'] = $pack->items()->count();
        }

        if ($itemType === 'mcq') {
            $options = array_values(array_filter($validated['options'] ?? [], fn ($option) => $option !== null && $option !== ''));
            $data['options'] = $options;
            $data['correct_option'] = (int) ($validated['correct_option'] ?? 0);
        } else {
            $data['options'] = null;
            $data['correct_option'] = null;
        }

        return $data;
    }

    private function displayItemsForPack(FlashcardPack $pack): Collection
    {
        if ($pack->is_assigned && $pack->sourcePack) {
            return $pack->sourcePack->items()->with('pack:id,title,color,display_mode')->orderBy('sort_order')->orderBy('id')->get();
        }

        return $pack->items()->with('pack:id,title,color,display_mode')->orderBy('sort_order')->orderBy('id')->get();
    }

    private function deletePackTree(FlashcardPack $pack): void
    {
        $pack->load('childPacks');

        foreach ($pack->childPacks as $childPack) {
            $this->deletePackTree($childPack);
        }

        $pack->delete();
    }

    private function cloneAssignedTree(FlashcardPack $sourcePack, int $userId, ?int $parentCloneId = null): FlashcardPack
    {
        $clone = $sourcePack->replicate(['user_id', 'is_public', 'source_pack_id', 'parent_pack_id']);
        $clone->user_id = $userId;
        $clone->is_public = false;
        $clone->source_pack_id = $sourcePack->id;
        $clone->parent_pack_id = $parentCloneId;
        $clone->save();

        $sourcePack->loadMissing('childPacks');
        foreach ($sourcePack->childPacks as $childPack) {
            $this->cloneAssignedTree($childPack, $userId, $clone->id);
        }

        return $clone;
    }

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
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                throw new \RuntimeException('PhpSpreadsheet is required to read Excel files.');
            }

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
        }

        return $rows;
    }

    private function looksLikeHeader(array $row): bool
    {
        $headerKeywords = ['front', 'back', 'question', 'answer', 'column', 'type', 'a', 'b'];

        foreach ($row as $cell) {
            if (in_array(strtolower(trim((string) ($cell ?? ''))), $headerKeywords, true)) {
                return true;
            }
        }

        return false;
    }

    private function mapLegacyResponse(bool $isCorrect): string
    {
        return $isCorrect ? 'easy' : 'hard';
    }

    private function calculateNextReview(FlashcardPack $pack, FlashcardProgress $progress, string $responseLevel)
    {
        $now = now();
        $scheduleMap = [
            'daily' => ['easy' => 3, 'medium' => 1, 'hard' => 0.25],
            'weekly' => ['easy' => 7, 'medium' => 3, 'hard' => 1],
            'monthly' => ['easy' => 30, 'medium' => 7, 'hard' => 2],
        ];

        $cycle = $scheduleMap[$pack->repeat_cycle] ?? $scheduleMap['daily'];
        $days = $cycle[$responseLevel] ?? 1;

        if ($responseLevel === 'easy') {
            $days = min(45, $days + max(0, $progress->times_correct - 1));
        }

        if ($responseLevel === 'hard') {
            return $now->copy()->addHours((int) max(4, $days * 24));
        }

        return $now->copy()->addDays((int) ceil($days));
    }

    private function resolveQueueHours(FlashcardPack $pack): array
    {
        $startHour = 8;
        $endHour = 22;

        if ($pack->quiet_start && $pack->quiet_end) {
            $quietStartHour = (int) substr((string) $pack->quiet_start, 0, 2);
            $quietEndHour = (int) substr((string) $pack->quiet_end, 0, 2);

            if ($quietStartHour < $quietEndHour) {
                $startHour = $quietEndHour;
            } else {
                $endHour = $quietStartHour;
            }
        }

        if ($endHour <= $startHour) {
            $startHour = 8;
            $endHour = 22;
        }

        return [$startHour, $endHour];
    }

    private function userCanAccessItemPack(int $userId, int $itemPackId): bool
    {
        if (FlashcardPack::where('user_id', $userId)->where('id', $itemPackId)->exists()) {
            return true;
        }

        $sourcePacks = FlashcardPack::where('user_id', $userId)
            ->whereNotNull('source_pack_id')
            ->with('sourcePack')
            ->get();

        foreach ($sourcePacks as $assignedPack) {
            if ($assignedPack->source_pack_id === $itemPackId) {
                return true;
            }

            if ($assignedPack->sourcePack && in_array($itemPackId, $assignedPack->sourcePack->descendantPackIds(), true)) {
                return true;
            }
        }

        return false;
    }
}
