<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\FlashcardItem;
use App\Models\FlashcardPack;
use App\Models\PublicPackStore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlashcardController extends AdminApiController
{
    public function index(Request $request)
    {
        $query = FlashcardPack::with(['user:id,name,role', 'storeEntry'])
            ->whereNull('parent_pack_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'public') {
                $query->where('is_public', true);
            } elseif ($request->type === 'private') {
                $query->where('is_public', false);
            }
        }

        if ($request->filled('display_mode')) {
            $query->where('display_mode', $request->display_mode);
        }

        $packs = $query->latest()->paginate(20);
        $packs->getCollection()->transform(function (FlashcardPack $pack) {
            $pack->items_count = $pack->cardsCount();
            $pack->children_count = $pack->childPacks()->count();

            return $pack;
        });

        return $this->paginated($packs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'category' => 'nullable|string|max:255',
            'parent_pack_id' => 'nullable|integer',
        ]);

        $parentPack = $this->resolveAdminParentPack($validated['parent_pack_id'] ?? null, $request->user()->id);

        $pack = FlashcardPack::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#4f46e5',
            'display_mode' => $validated['display_mode'],
            'notifications_enabled' => $request->boolean('notifications_enabled', true),
            'daily_notification_count' => $request->input('daily_notification_count', 5),
            'repeat_cycle' => $request->input('repeat_cycle', 'daily'),
            'quiet_start' => $request->input('quiet_start', '22:00'),
            'quiet_end' => $request->input('quiet_end', '08:00'),
            'is_public' => true,
            'parent_pack_id' => $parentPack?->id,
        ]);

        PublicPackStore::create([
            'pack_id' => $pack->id,
            'published_by' => $request->user()->id,
            'category' => $validated['category'] ?? null,
        ]);

        return $this->success(['pack' => $pack], 'Pack created successfully.', 201);
    }

    public function show($id)
    {
        $pack = FlashcardPack::with(['user:id,name,role', 'storeEntry', 'childPacks:id,user_id,parent_pack_id,title,color,display_mode,is_active,source_pack_id'])
            ->findOrFail($id);

        $pack->items_count = $pack->cardsCount();

        $items = ($pack->is_assigned && $pack->sourcePack)
            ? $pack->sourcePack->items()->with('pack:id,title,color,display_mode')->orderBy('sort_order')->orderBy('id')->get()
            : $pack->items()->with('pack:id,title,color,display_mode')->orderBy('sort_order')->orderBy('id')->get();

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
        $pack = FlashcardPack::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'category' => 'nullable|string|max:255',
            'parent_pack_id' => 'nullable|integer',
        ]);

        $parentPack = $this->resolveAdminParentPack($validated['parent_pack_id'] ?? null, $request->user()->id, $pack);

        $pack->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#4f46e5',
            'display_mode' => $validated['display_mode'],
            'notifications_enabled' => $request->boolean('notifications_enabled', $pack->notifications_enabled),
            'daily_notification_count' => $request->input('daily_notification_count', $pack->daily_notification_count),
            'repeat_cycle' => $request->input('repeat_cycle', $pack->repeat_cycle),
            'quiet_start' => $request->input('quiet_start', $pack->quiet_start),
            'quiet_end' => $request->input('quiet_end', $pack->quiet_end),
            'parent_pack_id' => $parentPack?->id,
        ]);

        if ($pack->storeEntry) {
            $pack->storeEntry->update(['category' => $validated['category'] ?? null]);
        }

        return $this->success(['pack' => $pack], 'Pack updated successfully.');
    }

    public function destroy($id)
    {
        $pack = FlashcardPack::findOrFail($id);
        $this->deletePackTree($pack);

        return $this->success(null, 'Pack deleted successfully.');
    }

    public function publishToStore(Request $request, $id)
    {
        $pack = FlashcardPack::findOrFail($id);

        if ($pack->storeEntry) {
            $pack->storeEntry->update(['is_active' => true, 'category' => $request->input('category', $pack->storeEntry->category)]);
        } else {
            PublicPackStore::create([
                'pack_id' => $pack->id,
                'published_by' => $request->user()->id,
                'category' => $request->input('category'),
            ]);
        }

        $pack->update(['is_public' => true]);

        return $this->success(null, 'Pack published successfully.');
    }

    public function assignToUser(Request $request, $id)
    {
        $pack = FlashcardPack::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUser = User::findOrFail($request->user_id);

        $existing = FlashcardPack::where('user_id', $targetUser->id)
            ->where('source_pack_id', $pack->id)
            ->whereNull('parent_pack_id')
            ->first();

        if ($existing) {
            return $this->error('This user already has the assigned tree.', 409);
        }

        DB::transaction(function () use ($pack, $targetUser) {
            $this->cloneAssignedTree($pack, $targetUser->id);
        });

        return $this->success(null, 'Pack tree assigned successfully.');
    }

    public function import(Request $request, $id)
    {
        $pack = FlashcardPack::findOrFail($id);

        if ($pack->is_assigned) {
            return $this->error('Assigned packs cannot be edited directly.', 422);
        }

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
        $pack = FlashcardPack::findOrFail($id);

        if ($pack->is_assigned) {
            return $this->error('Assigned packs cannot be edited directly.', 422);
        }

        $itemType = $request->input('item_type', $pack->display_mode);
        $validated = $this->validateItemPayload($request, $itemType);
        $item = $pack->items()->create($this->mapItemData($validated, $pack, $itemType));

        return $this->success(['item' => $item], 'Item created successfully.', 201);
    }

    public function updateItem(Request $request, $itemId)
    {
        $item = FlashcardItem::with('pack')->findOrFail($itemId);

        if ($item->pack->is_assigned) {
            return $this->error('Assigned packs cannot be edited directly.', 422);
        }

        $itemType = $request->input('item_type', $item->resolved_item_type);
        $validated = $this->validateItemPayload($request, $itemType);
        $item->update($this->mapItemData($validated, $item->pack, $itemType, false));

        return $this->success(['item' => $item->fresh()], 'Item updated successfully.');
    }

    public function destroyItem($itemId)
    {
        $item = FlashcardItem::with('pack')->findOrFail($itemId);

        if ($item->pack->is_assigned) {
            return $this->error('Assigned packs cannot be edited directly.', 422);
        }

        $item->delete();

        return $this->success(null, 'Item deleted successfully.');
    }

    private function resolveAdminParentPack(?int $parentPackId, int $userId, ?FlashcardPack $currentPack = null): ?FlashcardPack
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

    private function deletePackTree(FlashcardPack $pack): void
    {
        $pack->load('childPacks');

        foreach ($pack->childPacks as $childPack) {
            $this->deletePackTree($childPack);
        }

        $pack->delete();
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
}
