<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\FlashcardItem;
use App\Models\FlashcardPack;
use App\Models\PublicPackStore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlashcardController extends Controller
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
                $query->where('is_public', true)
                    ->whereHas('user', fn ($q) => $q->where('role', 'admin'));
            } elseif ($request->type === 'private') {
                $query->where('is_public', false)
                    ->whereHas('user', fn ($q) => $q->where('role', 'admin'));
            }
        } else {
            $query->whereHas('user', fn ($q) => $q->where('role', 'admin'));
        }

        if ($request->filled('display_mode')) {
            $query->where('display_mode', $request->display_mode);
        }

        $packs = $query->latest()->paginate(20)->withQueryString();
        $packs->getCollection()->transform(function (FlashcardPack $pack) {
            $pack->items_count = $pack->cardsCount();
            $pack->children_count = $pack->childPacks()->count();

            return $pack;
        });

        $totalPacks = FlashcardPack::count();
        $publicPacks = FlashcardPack::where('is_public', true)
            ->whereHas('user', fn ($q) => $q->where('role', 'admin'))
            ->count();
        $totalCards = FlashcardItem::count();
        $totalUsers = FlashcardPack::distinct('user_id')->count('user_id');

        return view('admin.flashcards.index', compact('packs', 'totalPacks', 'publicPacks', 'totalCards', 'totalUsers'));
    }

    public function create()
    {
        $parentPacks = $this->availableAdminParentPacks();

        return view('admin.flashcards.create', compact('parentPacks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'category' => 'nullable|string|max:255',
            'daily_notification_count' => 'nullable|integer|min:1|max:24',
            'repeat_cycle' => 'nullable|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|string',
            'quiet_end' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'notifications_enabled' => 'nullable|boolean',
            'parent_pack_id' => 'nullable|integer',
        ]);

        $parentPack = $this->resolveAdminParentPack($validated['parent_pack_id'] ?? null);

        $pack = FlashcardPack::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#4f46e5',
            'display_mode' => $validated['display_mode'],
            'daily_notification_count' => $validated['daily_notification_count'] ?? 5,
            'repeat_cycle' => $validated['repeat_cycle'] ?? 'daily',
            'quiet_start' => $validated['quiet_start'] ?? '22:00',
            'quiet_end' => $validated['quiet_end'] ?? '08:00',
            'is_active' => $request->has('is_active'),
            'notifications_enabled' => $request->has('notifications_enabled'),
            'is_public' => true,
            'parent_pack_id' => $parentPack?->id,
        ]);

        return redirect()->route('admin.flashcards.show', $pack)
            ->with('success', 'تم إنشاء الحزمة العامة بنجاح.');
    }

    public function show(FlashcardPack $flashcard)
    {
        $flashcard->load(['user:id,name,role', 'storeEntry']);
        $flashcard->items_count = $flashcard->cardsCount();
        $items = $this->displayItemsForPack($flashcard)->values();
        $childPacks = $flashcard->childPacks()->latest()->get();
        foreach ($childPacks as $childPack) {
            $childPack->items_count = $childPack->cardsCount();
        }

        $parentPacks = $this->availableAdminParentPacks($flashcard);

        return view('admin.flashcards.show', compact('flashcard', 'items', 'childPacks', 'parentPacks'));
    }

    public function edit(FlashcardPack $flashcard)
    {
        $flashcard->load('storeEntry');
        $parentPacks = $this->availableAdminParentPacks($flashcard);

        return view('admin.flashcards.edit', compact('flashcard', 'parentPacks'));
    }

    public function update(Request $request, FlashcardPack $flashcard)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'category' => 'nullable|string|max:255',
            'daily_notification_count' => 'nullable|integer|min:1|max:24',
            'repeat_cycle' => 'nullable|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|string',
            'quiet_end' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'notifications_enabled' => 'nullable|boolean',
            'parent_pack_id' => 'nullable|integer',
        ]);

        $parentPack = $this->resolveAdminParentPack($validated['parent_pack_id'] ?? null, $flashcard);

        $flashcard->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#4f46e5',
            'display_mode' => $validated['display_mode'],
            'daily_notification_count' => $validated['daily_notification_count'] ?? $flashcard->daily_notification_count,
            'repeat_cycle' => $validated['repeat_cycle'] ?? $flashcard->repeat_cycle,
            'quiet_start' => $validated['quiet_start'] ?? $flashcard->quiet_start,
            'quiet_end' => $validated['quiet_end'] ?? $flashcard->quiet_end,
            'is_active' => $request->has('is_active'),
            'notifications_enabled' => $request->has('notifications_enabled'),
            'parent_pack_id' => $parentPack?->id,
        ]);

        if ($flashcard->storeEntry) {
            $flashcard->storeEntry->update(['category' => $validated['category'] ?? null]);
        }

        return redirect()->route('admin.flashcards.show', $flashcard)
            ->with('success', 'تم تحديث الحزمة بنجاح.');
    }

    public function destroy(FlashcardPack $flashcard)
    {
        $this->deletePackTree($flashcard);

        return redirect()->route('admin.flashcards.index')
            ->with('success', 'تم حذف الحزمة وكل التفرعات التابعة لها.');
    }

    public function publishToStore(Request $request, $id)
    {
        $pack = FlashcardPack::findOrFail($id);

        if (!$pack->user || !$pack->user->hasRole(UserRole::ADMIN)) {
            return back()->with('error', 'لا يمكن نشر هذه الحزمة في المتجر.');
        }

        $storeEntry = PublicPackStore::where('pack_id', $pack->id)->first();

        if ($storeEntry) {
            $storeEntry->update(['is_active' => true]);
        } else {
            PublicPackStore::create([
                'pack_id' => $pack->id,
                'published_by' => Auth::id(),
                'category' => $request->input('category', 'General'),
                'is_active' => true,
            ]);
        }

        $pack->update(['is_public' => true]);

        return back()->with('success', 'تم تفعيل ظهور الحزمة في المتجر.');
    }

    public function cloneAndReview(FlashcardPack $flashcard)
    {
        $newPack = DB::transaction(function () use ($flashcard) {
            return $this->cloneEditableTree($flashcard, Auth::id(), null, true);
        });

        return redirect()->route('admin.flashcards.show', $newPack)
            ->with('success', 'تم إنشاء نسخة مراجعة كاملة مع الحزم الفرعية والعناصر.');
    }

    public function searchStudent(Request $request)
    {
        $request->validate(['student_number' => 'required|string']);

        $user = User::with('college')->where('student_number', $request->student_number)->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'student_number' => $user->student_number,
                    'college' => $user->college->name ?? 'غير محدد',
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'لم يتم العثور على طالب بهذا الرقم']);
    }

    public function assignToUser(Request $request, FlashcardPack $flashcard)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUser = User::findOrFail($request->user_id);
        $existing = FlashcardPack::where('user_id', $targetUser->id)
            ->where('source_pack_id', $flashcard->id)
            ->whereNull('parent_pack_id')
            ->first();

        if ($existing) {
            return back()->with('info', "المستخدم «{$targetUser->name}» لديه هذه الحزمة بالفعل.");
        }

        DB::transaction(function () use ($flashcard, $targetUser) {
            $this->cloneAssignedTree($flashcard, $targetUser->id);
        });

        return back()->with('success', "تم تعيين الحزمة للمستخدم «{$targetUser->name}» بكل تفرعاتها.");
    }

    public function storeManagement(Request $request)
    {
        $query = PublicPackStore::with(['pack.user', 'pack'])
            ->where('is_active', true)
            ->whereHas('pack', fn ($q) => $q->where('is_public', true));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pack', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('display_mode')) {
            $query->whereHas('pack', function ($q) use ($request) {
                $q->where('display_mode', $request->display_mode);
            });
        }

        $storeItems = $query->latest()->paginate(15);
        $storeItems->getCollection()->transform(function (PublicPackStore $storeItem) {
            $storeItem->pack->items_count = $storeItem->pack->cardsCount();

            return $storeItem;
        });

        return view('admin.flashcards.store', compact('storeItems'));
    }

    public function toggleVisibility(Request $request, FlashcardPack $flashcard)
    {
        if (!$flashcard->is_public && (!$flashcard->user || !$flashcard->user->hasRole(UserRole::ADMIN))) {
            $message = 'لا يمكن جعل حزمة غير إدارية عامة مباشرة. أنشئ نسخة مراجعة أولًا.';

            if ($request->wantsJson() || $request->isJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return back()->with('error', $message);
        }

        $flashcard->is_public = !$flashcard->is_public;
        $flashcard->save();

        if ($flashcard->is_public) {
            PublicPackStore::updateOrCreate(
                ['pack_id' => $flashcard->id],
                [
                    'published_by' => Auth::id(),
                    'category' => $flashcard->storeEntry->category ?? 'General',
                    'is_active' => true,
                ]
            );
        } elseif ($flashcard->storeEntry) {
            $flashcard->storeEntry->update(['is_active' => false]);
        }

        $message = $flashcard->is_public ? 'الحزمة أصبحت عامة في المتجر.' : 'تم إخفاء الحزمة من المتجر.';

        if ($request->wantsJson() || $request->isJson()) {
            return response()->json(['success' => true, 'is_public' => $flashcard->is_public, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function import(Request $request, FlashcardPack $flashcard)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        $this->assertCanManageItems($flashcard);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            $rows = $this->parseSpreadsheet($file->getPathname(), $extension);
            $itemsCountBefore = $flashcard->items()->count();
            $items = [];

            foreach ($rows as $index => $row) {
                if (empty($row[0])) {
                    continue;
                }

                $itemData = [
                    'pack_id' => $flashcard->id,
                    'item_type' => $flashcard->display_mode,
                    'front_content' => trim((string) $row[0]),
                    'priority' => 'normal',
                    'sort_order' => $itemsCountBefore + $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                switch ($flashcard->display_mode) {
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
                return back()->with('error', 'الملف لا يحتوي على بيانات صالحة.');
            }

            FlashcardItem::insert($items);

            return back()->with('success', 'تم استيراد ' . count($items) . ' عنصرًا بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء قراءة الملف: ' . $e->getMessage());
        }
    }

    public function storeItem(Request $request, FlashcardPack $flashcard)
    {
        $this->assertCanManageItems($flashcard);
        $itemType = $request->input('item_type', $flashcard->display_mode);
        $validated = $this->validateItemPayload($request, $itemType);

        $flashcard->items()->create($this->mapItemData($validated, $flashcard, $itemType));

        return back()->with('success', 'تمت إضافة العنصر بنجاح.');
    }

    public function updateItem(Request $request, FlashcardItem $item)
    {
        $this->assertCanManageItems($item->pack);
        $itemType = $request->input('item_type', $item->resolved_item_type);
        $validated = $this->validateItemPayload($request, $itemType);

        $item->update($this->mapItemData($validated, $item->pack, $itemType, false));

        return back()->with('success', 'تم تحديث العنصر بنجاح.');
    }

    public function preview(FlashcardPack $flashcard)
    {
        $items = $flashcard->effectiveItems()
            ->with('pack:id,title,color,display_mode')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'normal')")
            ->orderBy('sort_order')
            ->get();

        return view('admin.flashcards.preview', compact('flashcard', 'items'));
    }

    public function destroyItem(FlashcardItem $item)
    {
        $this->assertCanManageItems($item->pack);
        $item->delete();

        return back()->with('success', 'تم حذف العنصر.');
    }

    public function assignmentsManagement(Request $request)
    {
        $query = FlashcardPack::with(['user', 'sourcePack' => function ($q) {
            $q->select('id', 'title', 'display_mode');
        }])
            ->whereNotNull('source_pack_id')
            ->whereNull('parent_pack_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                })->orWhereHas('sourcePack', function ($sourceQuery) use ($search) {
                    $sourceQuery->where('title', 'like', "%{$search}%");
                });
            });
        }

        $assignments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.flashcards.assignments', compact('assignments'));
    }

    public function cancelAssignment(FlashcardPack $pack)
    {
        if ($pack->source_pack_id !== null && $pack->parent_pack_id === null) {
            $this->deletePackTree($pack);

            return back()->with('success', 'تم إلغاء التعيين وحذف شجرة الحزمة من حساب الطالب.');
        }

        return back()->with('error', 'هذه ليست حزمة تعيين مباشرة قابلة للإلغاء من هنا.');
    }

    private function availableAdminParentPacks(?FlashcardPack $currentPack = null): Collection
    {
        $packs = FlashcardPack::where('user_id', Auth::id())
            ->whereNull('source_pack_id')
            ->orderBy('title')
            ->get();

        if (!$currentPack) {
            return $packs;
        }

        $blockedIds = $currentPack->descendantPackIds();
        $blockedIds[] = $currentPack->id;

        return $packs->reject(fn (FlashcardPack $pack) => in_array($pack->id, $blockedIds, true))->values();
    }

    private function resolveAdminParentPack(?int $parentPackId, ?FlashcardPack $currentPack = null): ?FlashcardPack
    {
        if (!$parentPackId) {
            return null;
        }

        $parentPack = FlashcardPack::where('id', $parentPackId)
            ->where('user_id', Auth::id())
            ->whereNull('source_pack_id')
            ->firstOrFail();

        if ($currentPack && in_array($parentPack->id, $currentPack->descendantPackIds(), true)) {
            abort(422, 'لا يمكن نقل الحزمة داخل أحد تفرعاتها.');
        }

        return $parentPack;
    }

    private function displayItemsForPack(FlashcardPack $pack): Collection
    {
        if ($pack->is_assigned && $pack->sourcePack) {
            return $pack->sourcePack->items()->with('pack:id,title,color,display_mode')->orderBy('sort_order')->orderBy('id')->get();
        }

        return $pack->items()->with('pack:id,title,color,display_mode')->orderBy('sort_order')->orderBy('id')->get();
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

    private function cloneEditableTree(FlashcardPack $sourcePack, int $userId, ?int $parentCloneId = null, bool $isRoot = false): FlashcardPack
    {
        $clone = $sourcePack->replicate(['user_id', 'is_public', 'parent_pack_id']);
        $clone->user_id = $userId;
        $clone->title = $isRoot ? 'نسخة المتجر: ' . $sourcePack->title : $sourcePack->title;
        $clone->is_public = false;
        $clone->source_pack_id = null;
        $clone->parent_pack_id = $parentCloneId;
        $clone->save();

        $sourcePack->loadMissing(['items', 'childPacks']);

        foreach ($sourcePack->items as $item) {
            $newItem = $item->replicate(['pack_id']);
            $newItem->pack_id = $clone->id;
            $newItem->save();
        }

        foreach ($sourcePack->childPacks as $childPack) {
            $this->cloneEditableTree($childPack, $userId, $clone->id);
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

    private function assertCanManageItems(FlashcardPack $pack): void
    {
        if ($pack->is_assigned) {
            abort(422, 'الحزم المعيّنة لا تقبل تعديل العناصر مباشرة.');
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
                throw new \RuntimeException('يرجى تثبيت الحزمة phpoffice/phpspreadsheet لدعم ملفات Excel.');
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
}
