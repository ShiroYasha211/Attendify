<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Models\FlashcardPack;
use App\Models\FlashcardItem;
use App\Models\PublicPackStore;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FlashcardController extends AdminApiController
{
    /**
     * جلب كل الحزم مع فلاتر.
     */
    public function index(Request $request)
    {
        $query = FlashcardPack::with(['user:id,name,role', 'storeEntry'])
            ->withCount('items');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
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

        return $this->paginated($packs);
    }

    /**
     * إنشاء حزمة عامة.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'category' => 'nullable|string|max:255',
        ]);

        $pack = FlashcardPack::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'color' => $request->color ?? '#4f46e5',
            'display_mode' => $request->display_mode,
            'notifications_enabled' => $request->boolean('notifications_enabled', true),
            'daily_notification_count' => $request->input('daily_notification_count', 5),
            'repeat_cycle' => $request->input('repeat_cycle', 'daily'),
            'quiet_start' => $request->input('quiet_start', '22:00'),
            'quiet_end' => $request->input('quiet_end', '08:00'),
            'is_public' => true,
        ]);

        PublicPackStore::create([
            'pack_id' => $pack->id,
            'published_by' => $request->user()->id,
            'category' => $request->category,
        ]);

        $pack->loadCount('items');

        return $this->success(['pack' => $pack], 'تم إنشاء الحزمة العامة بنجاح.', 201);
    }

    /**
     * عرض تفاصيل حزمة + بطاقاتها.
     */
    public function show($id)
    {
        $pack = FlashcardPack::with(['user:id,name,role', 'storeEntry'])
            ->withCount('items')
            ->findOrFail($id);

        $items = $pack->effectiveItems()->orderBy('sort_order')->orderBy('id')->get();

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
        $pack = FlashcardPack::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'display_mode' => 'required|in:flash_card,one_line,qa,mcq',
            'category' => 'nullable|string|max:255',
        ]);

        $pack->update($request->only([
            'title', 'description', 'color', 'display_mode',
            'notifications_enabled', 'daily_notification_count',
            'repeat_cycle', 'quiet_start', 'quiet_end'
        ]));

        if ($pack->storeEntry) {
            $pack->storeEntry->update(['category' => $request->category]);
        }

        $pack->loadCount('items');

        return $this->success(['pack' => $pack], 'تم تحديث الحزمة بنجاح.');
    }

    /**
     * حذف حزمة.
     */
    public function destroy($id)
    {
        FlashcardPack::findOrFail($id)->delete();

        return $this->success(null, 'تم حذف الحزمة نهائياً.');
    }

    /**
     * نشر حزمة في المتجر العام.
     */
    public function publishToStore(Request $request, $id)
    {
        $pack = FlashcardPack::findOrFail($id);

        $request->validate([
            'category' => 'nullable|string|max:255',
        ]);

        if ($pack->storeEntry) {
            return $this->error('الحزمة منشورة بالفعل في المتجر.', 409);
        }

        $pack->update(['is_public' => true]);

        PublicPackStore::create([
            'pack_id' => $pack->id,
            'published_by' => $request->user()->id,
            'category' => $request->category,
        ]);

        return $this->success(null, 'تم نشر الحزمة في المتجر العام بنجاح.');
    }

    /**
     * تعيين حزمة لمستخدم محدد.
     */
    public function assignToUser(Request $request, $id)
    {
        $pack = FlashcardPack::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUser = User::findOrFail($request->user_id);

        $existing = FlashcardPack::where('user_id', $targetUser->id)
            ->where('source_pack_id', $pack->id)
            ->first();

        if ($existing) {
            return $this->error("المستخدم «{$targetUser->name}» لديه هذه الحزمة بالفعل.", 409);
        }

        DB::transaction(function () use ($pack, $targetUser) {
            $newPack = $pack->replicate(['user_id', 'is_public', 'source_pack_id']);
            $newPack->user_id = $targetUser->id;
            $newPack->is_public = false;
            $newPack->source_pack_id = $pack->id;
            $newPack->save();

            // items are NOT replicated anymore to save space and ensure updates sync
        });

        return $this->success(null, "تم تعيين الحزمة للمستخدم «{$targetUser->name}» بنجاح.");
    }

    /**
     * استيراد بطاقات من Excel/CSV.
     */
    public function import(Request $request, $id)
    {
        $pack = FlashcardPack::findOrFail($id);

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
