<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashcardPack;
use App\Models\FlashcardItem;
use App\Models\PublicPackStore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlashcardController extends Controller
{
    /**
     * عرض كل الحزم (العامة + التابعة للمستخدمين).
     */
    public function index(Request $request)
    {
        $query = FlashcardPack::with(['user:id,name,role', 'storeEntry'])
            ->withCount('items');

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'public') {
                $query->where('is_public', true)
                      ->whereHas('user', fn($q) => $q->where('role', 'admin'));
            } elseif ($request->type === 'private') {
                $query->where('is_public', false)
                      ->whereHas('user', fn($q) => $q->where('role', 'admin'));
            }
        } else {
            // Default to showing only admin-owned packs if no type filter is applied
            $query->whereHas('user', fn($q) => $q->where('role', 'admin'));
        }

        if ($request->filled('display_mode')) {
            $query->where('display_mode', $request->display_mode);
        }

        // Stats
        $totalPacks = FlashcardPack::count();
        $publicPacks = FlashcardPack::where('is_public', true)
            ->whereHas('user', fn($q) => $q->where('role', 'admin'))
            ->count();
        $totalCards = FlashcardItem::count();
        $totalUsers = FlashcardPack::distinct('user_id')->count('user_id');

        $packs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.flashcards.index', compact(
            'packs', 'totalPacks', 'publicPacks', 'totalCards', 'totalUsers'
        ));
    }

    /**
     * نموذج إنشاء حزمة عامة.
     */
    public function create()
    {
        return view('admin.flashcards.create');
    }

    /**
     * حفظ حزمة عامة جديدة.
     */
    public function store(Request $request)
    {
        $request->validate([
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
        ]);

        $pack = FlashcardPack::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'color' => $request->color ?? '#4f46e5',
            'display_mode' => $request->display_mode,
            'daily_notification_count' => $request->daily_notification_count ?? 5,
            'repeat_cycle' => $request->repeat_cycle ?? 'daily',
            'quiet_start' => $request->quiet_start ?? '22:00',
            'quiet_end' => $request->quiet_end ?? '08:00',
            'is_active' => $request->has('is_active'),
            'notifications_enabled' => $request->has('notifications_enabled'),
            'is_public' => true,
        ]);

        // Auto-publish removed. Packs must be published explicitly via publishToStore.

        return redirect()->route('admin.flashcards.show', $pack)
            ->with('success', 'تم إنشاء الحزمة العامة بنجاح! يمكنك الآن إضافة البطاقات.');
    }

    /**
     * عرض تفاصيل حزمة.
     */
    public function show(FlashcardPack $flashcard)
    {
        $flashcard->load(['user:id,name,role', 'storeEntry']);
        $flashcard->loadCount('items');
        $items = $flashcard->items()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.flashcards.show', compact('flashcard', 'items'));
    }

    /**
     * نموذج تعديل الحزمة.
     */
    public function edit(FlashcardPack $flashcard)
    {
        $flashcard->load('storeEntry');
        return view('admin.flashcards.edit', compact('flashcard'));
    }

    /**
     * حفظ تعديلات الحزمة.
     */
    public function update(Request $request, FlashcardPack $flashcard)
    {
        $request->validate([
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
        ]);

        $data = $request->only([
            'title', 'description', 'color', 'display_mode', 
            'daily_notification_count', 'repeat_cycle', 'quiet_start', 'quiet_end'
        ]);
        
        $data['is_active'] = $request->has('is_active');
        $data['notifications_enabled'] = $request->has('notifications_enabled');

        $flashcard->update($data);

        // Update store entry category if exists
        if ($flashcard->storeEntry) {
            $flashcard->storeEntry->update(['category' => $request->category]);
        }

        return redirect()->route('admin.flashcards.show', $flashcard)
            ->with('success', 'تم تحديث الحزمة بنجاح.');
    }

    /**
     * حذف حزمة.
     */
    public function destroy(FlashcardPack $flashcard)
    {
        $flashcard->delete();

        return redirect()->route('admin.flashcards.index')
            ->with('success', 'تم حذف الحزمة نهائياً.');
    }

    /**
     * Publish a pack to the public store.
     */
    public function publishToStore(Request $request, $id)
    {
        $pack = FlashcardPack::findOrFail($id);

        // Security check: Only admins can publish their own packs to the store
        if (!$pack->user || !$pack->user->hasRole(\App\Enums\UserRole::ADMIN)) {
            return back()->with('error', 'خطأ: لا يمكن نشر هذه الحزمة في المتجر.');
        }

        // Check if it's a "Store-Ready" version (Admin owned)
        // If the admin wants to publish, they should have gone through the Review & Clone process
        // unless they created it themselves as an admin pack.
        
        $storeEntry = PublicPackStore::where('pack_id', $pack->id)->first();

        if ($storeEntry) {
            $storeEntry->update(['is_active' => true]);
        } else {
            PublicPackStore::create([
                'pack_id' => $pack->id,
                'published_by' => Auth::id(),
                'category' => $pack->category ?? 'General',
                'is_active' => true,
            ]);
        }

        $pack->update(['is_public' => true]);

        return back()->with('success', 'تم تفعيل ظهور الحزمة في المتجر بنجاح.');
    }

    /**
     * Create an official editable clone for review before publishing to store.
     */
    public function cloneAndReview(FlashcardPack $flashcard)
    {
        // Clone the pack AND items (because this will be the "Master" version for the store)
        $newPack = DB::transaction(function () use ($flashcard) {
            $clone = $flashcard->replicate(['user_id', 'is_public', 'source_pack_id']);
            $clone->user_id = Auth::id();
            $clone->title = "نسخة المتجر: " . $flashcard->title;
            $clone->is_public = false; // Not public until explicitly published
            $clone->source_pack_id = $flashcard->id;
            $clone->save();

            // Clone items for the master version so they can be edited/cleaned up
            foreach ($flashcard->items as $item) {
                $newItem = $item->replicate(['pack_id']);
                $newItem->pack_id = $clone->id;
                $newItem->save();
            }

            return $clone;
        });

        return redirect()->route('admin.flashcards.show', $newPack)
            ->with('success', 'تم إنشاء نسخة للمراجعة. يمكنك الآن تعديل المحتوى والتأكد منه قبل النشر النهائي.');
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
                    'college' => $user->college->name ?? 'الكلية غير محددة',
                ]
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'لم يتم العثور على طالب بهذا الرقم']);
    }

    /**
     * تعيين حزمة لمستخدم محدد.
     */
    public function assignToUser(Request $request, FlashcardPack $flashcard)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUser = User::findOrFail($request->user_id);

        // Check if already assigned
        $existing = FlashcardPack::where('user_id', $targetUser->id)
            ->where('source_pack_id', $flashcard->id)
            ->first();

        if ($existing) {
            return back()->with('info', "المستخدم «{$targetUser->name}» لديه هذه الحزمة بالفعل.");
        }

        DB::transaction(function () use ($flashcard, $targetUser) {
            $newPack = $flashcard->replicate(['user_id', 'is_public', 'source_pack_id']);
            $newPack->user_id = $targetUser->id;
            $newPack->is_public = false;
            $newPack->source_pack_id = $flashcard->id;
            $newPack->save();
            
            // Note: We don't clone items here anymore to prevent data duplication.
            // Student progress will be tracked against the original item IDs.
        });

        return back()->with('success', "تم تعيين الحزمة للمستخدم «{$targetUser->name}» بنجاح.");
    }

    /**
     * عرض وإدارة المتجر العام.
     */
    public function storeManagement(Request $request)
    {
        $query = PublicPackStore::with(['pack.user', 'pack' => fn($q) => $q->withCount('items')])
            ->where('is_active', true)
            ->whereHas('pack', fn($q) => $q->where('is_public', true));

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

        return view('admin.flashcards.store', compact('storeItems'));
    }

    /**
     * Toggle visibility of a pack.
     */
    public function toggleVisibility(Request $request, FlashcardPack $flashcard)
    {
        // Security check: Only admins can manage their own packs for PUBLISHING
        // We allow hiding any pack from the store, but preventing making non-admin packs public directly.
        if (!$flashcard->is_public && (!$flashcard->user || !$flashcard->user->hasRole(\App\Enums\UserRole::ADMIN))) {
            $msg = 'لا يمكن تحويل حزمة الطالب للعامة مباشرة، يرجى (إنشاء نسخة للمراجعة) أولاً.';
            if ($request->wantsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg
                ], 403);
            }
            return back()->with('error', $msg);
        }

        $flashcard->is_public = !$flashcard->is_public;
        $flashcard->save();

        if ($flashcard->is_public) {
            // Ensure store entry exists and is active
            PublicPackStore::updateOrCreate(
                ['pack_id' => $flashcard->id],
                [
                    'published_by' => Auth::id(), 
                    'category' => $flashcard->category ?? 'General', 
                    'is_active' => true
                ]
            );
        } else {
            // Deactivate in store instead of deleting, to keep stats/records
            if ($flashcard->storeEntry) {
                $flashcard->storeEntry->update(['is_active' => false]);
            }
        }

        $msg = $flashcard->is_public ? 'الحزمة الآن عامة في المتجر' : 'الحزمة الآن مخفية عن المتجر';
        
        if ($request->wantsJson() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'is_public' => $flashcard->is_public,
                'message' => $msg
            ]);
        }
        
        return back()->with('success', $msg);
    }

    /**
     * استيراد بطاقات من Excel/CSV.
     */
    public function import(Request $request, FlashcardPack $flashcard)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            $rows = $this->parseSpreadsheet($file->getPathname(), $extension);
            $itemsCountBefore = $flashcard->items()->count();

            $items = [];
            foreach ($rows as $index => $row) {
                if (empty($row[0])) continue;

                $itemData = [
                    'pack_id' => $flashcard->id,
                    'front_content' => trim($row[0]),
                    'priority' => 'normal',
                    'sort_order' => $itemsCountBefore + $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Type-specific mapping
                switch ($flashcard->display_mode) {
                    case 'one_line':
                        $itemData['back_content'] = null;
                        break;
                    case 'mcq':
                        $options = [];
                        if (isset($row[1])) $options[] = trim($row[1]);
                        if (isset($row[2])) $options[] = trim($row[2]);
                        if (isset($row[3])) $options[] = trim($row[3]);
                        if (isset($row[4])) $options[] = trim($row[4]);
                        $itemData['options'] = json_encode($options);
                        $itemData['correct_option'] = isset($row[5]) && is_numeric($row[5]) ? (int)$row[5] : 0;
                        $itemData['back_content'] = null;
                        break;
                    default: // flash_card, qa
                        $itemData['back_content'] = isset($row[1]) ? trim($row[1]) : null;
                        break;
                }

                $items[] = $itemData;
            }

            if (empty($items)) {
                return back()->with('error', 'الملف لا يحتوي على بيانات صالحة.');
            }

            FlashcardItem::insert($items);

            return back()->with('success', 'تم استيراد ' . count($items) . ' بطاقة بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء قراءة الملف: ' . $e->getMessage());
        }
    }

    /**
     * إضافة بطاقة واحدة.
     */
    public function storeItem(Request $request, FlashcardPack $flashcard)
    {
        $rules = [
            'front_content' => 'required|string',
            'priority' => 'required|in:normal,high,critical',
        ];

        if ($flashcard->display_mode === 'mcq') {
            $rules['options'] = 'required|array|min:2|max:6';
            $rules['options.*'] = 'required|string|max:500';
            $rules['correct_option'] = 'required|integer|min:0';
        } elseif ($flashcard->display_mode !== 'one_line') {
            $rules['back_content'] = 'required|string';
        }

        $request->validate($rules);

        $data = $request->only(['front_content', 'back_content', 'priority']);
        $data['sort_order'] = $flashcard->items()->count();

        if ($flashcard->display_mode === 'mcq') {
            $data['options'] = $request->options;
            $data['correct_option'] = (int)$request->correct_option;
            $data['back_content'] = null;
        }

        if ($flashcard->display_mode === 'one_line') {
            $data['back_content'] = null;
        }

        $flashcard->items()->create($data);

        return back()->with('success', 'تم إضافة البطاقة بنجاح.');
    }

    /**
     * تعديل بطاقة.
     */
    public function updateItem(Request $request, FlashcardItem $item)
    {
        $request->validate([
            'front_content' => 'required|string',
            'back_content' => 'nullable|string',
            'options' => 'nullable|array|min:2|max:6',
            'options.*' => 'string|max:500',
            'correct_option' => 'nullable|integer|min:0',
            'priority' => 'required|in:normal,high,critical',
        ]);

        $data = $request->only(['front_content', 'back_content', 'priority']);

        if ($item->pack->display_mode === 'mcq' && $request->has('options')) {
            $data['options'] = $request->options;
            $data['correct_option'] = $request->correct_option;
        }

        $item->update($data);

        return back()->with('success', 'تم تحديث البطاقة بنجاح.');
    }

    /**
     * معاينة الحزمة للأدمن.
     */
    public function preview(FlashcardPack $flashcard)
    {
        $items = $flashcard->effectiveItems()
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'normal')")
            ->orderBy('sort_order')
            ->get();

        return view('admin.flashcards.preview', compact('flashcard', 'items'));
    }

    /**
     * حذف بطاقة.
     */
    public function destroyItem(FlashcardItem $item)
    {
        $item->delete();

        return back()->with('success', 'تم حذف البطاقة.');
    }

    // ── Private Helpers ──

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

    /**
     * عرض وإدارة تعيينات الحزم للطلاب
     */
    public function assignmentsManagement(Request $request)
    {
        $query = FlashcardPack::with(['user', 'sourcePack' => function ($q) {
                $q->select('id', 'title', 'display_mode');
            }])
            ->whereNotNull('source_pack_id')
            ->whereDoesntHave('items'); // Because assigned packs have 0 physical items

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('student_number', 'like', "%{$search}%");
                })->orWhereHas('sourcePack', function($sourceQuery) use ($search) {
                    $sourceQuery->where('title', 'like', "%{$search}%");
                });
            });
        }

        $assignments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.flashcards.assignments', compact('assignments'));
    }

    /**
     * إلغاء تعيين الحزمة للطالب
     */
    public function cancelAssignment(FlashcardPack $pack)
    {
        if ($pack->source_pack_id !== null && $pack->items()->count() === 0) {
            $pack->delete();
            return back()->with('success', 'تم إلغاء التعيين وحذف الحزمة من حساب الطالب بنجاح.');
        }

        return back()->with('error', 'لا يمكن إلغاء هذه الحزمة لأنها ليست تعييناً مباشراً من الإدارة.');
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
