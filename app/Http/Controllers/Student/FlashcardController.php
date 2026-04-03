<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FlashcardPack;
use App\Models\FlashcardItem;
use App\Models\PublicPackStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlashcardController extends Controller
{
    /**
     * الصفحة الرئيسية: حزمي + الحزم العامة.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Fetch user's packs with items or source items
        $packs = FlashcardPack::forUser($user->id)
            ->latest()
            ->get();

        foreach ($packs as $pack) {
            $pack->items_count = $pack->cardsCount();
        }

        // Stats
        $totalPacks = $packs->count();
        $activePacks = $packs->where('is_active', true)->count();
        $totalCards = $packs->sum('items_count');

        return view('student.flashcards.index', compact(
            'packs', 'totalPacks', 'activePacks', 'totalCards'
        ));
    }

    /**
     * نموذج إنشاء حزمة.
     */
    public function create()
    {
        return view('student.flashcards.create');
    }

    /**
     * حفظ حزمة جديدة.
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

        $pack = Auth::user()->flashcardPacks()->create($request->only([
            'title', 'description', 'color', 'display_mode',
            'notifications_enabled', 'daily_notification_count',
            'repeat_cycle', 'quiet_start', 'quiet_end',
        ]));

        return redirect()->route('student.flashcards.show', $pack)
            ->with('success', 'تم إنشاء الحزمة بنجاح! يمكنك الآن إضافة البطاقات.');
    }

    /**
     * عرض بطاقات الحزمة.
     */
    public function show(FlashcardPack $flashcard)
    {
        $this->authorizeOwnership($flashcard);

        $items = $flashcard->effectiveItems()->orderBy('sort_order')->orderBy('id')->get();
        $highPriorityCount = $items->whereIn('priority', ['high', 'critical'])->count();

        return view('student.flashcards.show', compact('flashcard', 'items', 'highPriorityCount'));
    }

    /**
     * تعديل إعدادات الحزمة.
     */
    public function edit(FlashcardPack $flashcard)
    {
        $this->authorizeOwnership($flashcard);
        return view('student.flashcards.edit', compact('flashcard'));
    }

    /**
     * حفظ التعديلات.
     */
    public function update(Request $request, FlashcardPack $flashcard)
    {
        $this->authorizeOwnership($flashcard);

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

        $flashcard->update($request->only([
            'title', 'description', 'color', 'display_mode',
            'notifications_enabled', 'daily_notification_count',
            'repeat_cycle', 'quiet_start', 'quiet_end',
        ]));

        return redirect()->route('student.flashcards.show', $flashcard)
            ->with('success', 'تم تحديث الحزمة بنجاح.');
    }

    /**
     * حذف الحزمة.
     */
    public function destroy(FlashcardPack $flashcard)
    {
        $this->authorizeOwnership($flashcard);
        $flashcard->delete();

        return redirect()->route('student.flashcards.index')
            ->with('success', 'تم حذف الحزمة نهائياً.');
    }

    /**
     * تشغيل/إيقاف الحزمة.
     */
    public function toggleActive(FlashcardPack $flashcard)
    {
        $this->authorizeOwnership($flashcard);
        $flashcard->update(['is_active' => !$flashcard->is_active]);

        $status = $flashcard->is_active ? 'تم تفعيل' : 'تم إيقاف';
        return back()->with('success', "{$status} الحزمة «{$flashcard->title}» بنجاح.");
    }

    /**
     * تحديث إعدادات الإشعارات فقط.
     */
    public function updateSettings(Request $request, FlashcardPack $flashcard)
    {
        $this->authorizeOwnership($flashcard);

        $request->validate([
            'notifications_enabled' => 'boolean',
            'daily_notification_count' => 'integer|min:1|max:50',
            'repeat_cycle' => 'required|in:daily,weekly,monthly',
            'quiet_start' => 'nullable|date_format:H:i',
            'quiet_end' => 'nullable|date_format:H:i',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
        ]);

        $flashcard->update($request->only([
            'notifications_enabled', 'daily_notification_count',
            'repeat_cycle', 'quiet_start', 'quiet_end', 'display_mode',
        ]));

        return back()->with('success', 'تم تحديث إعدادات الإشعارات بنجاح.');
    }

    /**
     * استيراد بطاقات من Excel/CSV.
     */
    public function import(Request $request, FlashcardPack $flashcard)
    {
        $this->authorizeOwnership($flashcard);

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
                if (empty($row[0])) continue; // skip empty rows

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
                        if (isset($row[4])) $options[] = trim($row[4]); // optional 4th
                        $itemData['options'] = json_encode($options);
                        // Column F (row[5]) as correct_option index, default 0
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
        $this->authorizeOwnership($flashcard);

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
            $data['back_content'] = null; // MCQs don't use back face in the same way
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
        $this->authorizeOwnership($item->pack);

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
     * حذف بطاقة.
     */
    public function destroyItem(FlashcardItem $item)
    {
        $this->authorizeOwnership($item->pack);
        $item->delete();

        return back()->with('success', 'تم حذف البطاقة.');
    }

    /**
     * تصفح الحزم العامة.
     */
    public function publicStore(Request $request)
    {
        $query = PublicPackStore::with(['pack.user:id,name', 'pack' => function ($q) {
            $q->withCount('items');
        }])
        ->where('is_active', true)
        ->whereHas('pack', fn($q) => $q->where('is_public', true));

        // Filter by display_mode
        if ($request->filled('display_mode')) {
            $query->whereHas('pack', function ($q) use ($request) {
                $q->where('display_mode', $request->display_mode);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pack', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $storeItems = $query->latest()->paginate(12)->withQueryString();

        return view('student.flashcards.store', compact('storeItems'));
    }

    /**
     * سحب (استنساخ) حزمة عامة.
     */
    public function clonePack(FlashcardPack $flashcard)
    {
        $user = Auth::user();

        // Check if already cloned
        $existing = FlashcardPack::where('user_id', $user->id)
            ->where('source_pack_id', $flashcard->id)
            ->first();

        if ($existing) {
            return back()->with('info', 'لقد قمت بسحب هذه الحزمة مسبقاً.');
        }

        DB::transaction(function () use ($flashcard, $user) {
            // Clone the pack
            $newPack = $flashcard->replicate(['user_id', 'is_public', 'source_pack_id']);
            $newPack->user_id = $user->id;
            $newPack->is_public = false;
            $newPack->source_pack_id = $flashcard->id;
            $newPack->save();

            // Increment download count (if store entry exists)
            if ($flashcard->storeEntry) {
                $flashcard->storeEntry->increment('downloads_count');
            }
        });

        return redirect()->route('student.flashcards.index')
            ->with('success', "تم سحب الحزمة «{$flashcard->title}» بنجاح!");
    }

    /**
     * بدء جلسة مراجعة.
     */
    public function review(FlashcardPack $flashcard)
    {
        $this->authorizeOwnership($flashcard);

        $items = $flashcard->effectiveItems()
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'normal')")
            ->orderBy('sort_order')
            ->get();

        return view('student.flashcards.review', compact('flashcard', 'items'));
    }

    /**
     * تسجيل تقدم بطاقة.
     */
    public function recordProgress(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:flashcard_items,id',
            'is_correct' => 'required|boolean',
        ]);

        $user = Auth::user();
        $item = FlashcardItem::findOrFail($request->item_id);
        $this->authorizeOwnership($item->pack);

        $progress = \App\Models\FlashcardProgress::firstOrCreate(
            ['user_id' => $user->id, 'item_id' => $item->id],
            ['times_shown' => 0, 'times_correct' => 0]
        );

        $progress->increment('times_shown');
        if ($request->is_correct) {
            $progress->increment('times_correct');
        }
        $progress->update([
            'last_shown_at' => now(),
            'next_review_at' => $this->calculateNextReview($progress, $request->is_correct),
        ]);

        return back()->with('success', 'تم تسجيل التقدم.');
    }

    // ── Private Helpers ──

    /**
     * Ensure the authenticated user owns the pack.
     */
    private function authorizeOwnership(FlashcardPack $pack): void
    {
        if ($pack->user_id !== Auth::id()) {
            abort(403, 'ليس لديك صلاحية الوصول لهذه الحزمة.');
        }
    }

    /**
     * Parse an Excel/CSV file into rows.
     */
    private function parseSpreadsheet(string $path, string $extension): array
    {
        $rows = [];

        if ($extension === 'csv') {
            $handle = fopen($path, 'r');
            $isFirst = true;
            while (($data = fgetcsv($handle)) !== false) {
                // Skip header if it looks like one
                if ($isFirst && $this->looksLikeHeader($data)) {
                    $isFirst = false;
                    continue;
                }
                $isFirst = false;
                $rows[] = $data;
            }
            fclose($handle);
        } else {
            // For xlsx/xls, use PhpSpreadsheet if available, otherwise simple xlsx reader
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
     * Check if a row looks like a header.
     */
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

    /**
     * Simple spaced repetition: calculate next review time.
     */
    private function calculateNextReview($progress, bool $isCorrect): \Carbon\Carbon
    {
        $interval = 1; // days

        if ($isCorrect) {
            // Double the interval for each correct answer, max 30 days
            $interval = min(30, pow(2, $progress->times_correct));
        }

        return now()->addDays($interval);
    }
}
