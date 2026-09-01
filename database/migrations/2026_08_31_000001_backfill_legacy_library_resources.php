<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SECTIONS = [
        'lectures' => ['title' => 'المحاضرات', 'icon' => 'video_library'],
        'summaries' => ['title' => 'الملخصات', 'icon' => 'summarize'],
        'quizzes' => ['title' => 'الكويزات', 'icon' => 'quiz'],
        'exams' => ['title' => 'نماذج الاختبارات', 'icon' => 'assignment'],
        'references' => ['title' => 'المراجع', 'icon' => 'menu_book'],
        'other' => ['title' => 'أخرى', 'icon' => 'folder'],
    ];

    public function up(): void
    {
        $subjectIds = DB::table('course_resources')
            ->whereNotNull('subject_id')
            ->distinct()
            ->orderBy('subject_id')
            ->pluck('subject_id');

        foreach ($subjectIds as $subjectId) {
            DB::transaction(function () use ($subjectId) {
                $creatorId = DB::table('course_resources')
                    ->where('subject_id', $subjectId)
                    ->orderBy('id')
                    ->value('created_by');

                foreach (['shared' => 'المكتبة المشتركة', 'course' => 'مصادر المقرر'] as $type => $name) {
                    $spaceId = $this->ensureSpace((int) $subjectId, $type, $name, $creatorId ? (int) $creatorId : null);
                    if (! $spaceId) {
                        continue;
                    }

                    $nodeIds = $this->ensureSections($spaceId, $creatorId ? (int) $creatorId : null);
                    DB::table('course_resources')
                        ->where('subject_id', $subjectId)
                        ->orderBy('id')
                        ->chunkById(500, function ($resources) use ($nodeIds) {
                            $now = now();
                            $placements = [];
                            foreach ($resources as $resource) {
                                $slug = $this->sectionFor((string) $resource->category);
                                $placements[] = [
                                    'library_node_id' => $nodeIds[$slug],
                                    'course_resource_id' => $resource->id,
                                    'sort_order' => $resource->id,
                                    'attached_by' => $resource->created_by,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                            DB::table('library_node_resources')->insertOrIgnore($placements);
                        });
                }
            });
        }
    }

    public function down(): void
    {
        // Data migration: keep library structures because users may edit them after deployment.
    }

    private function ensureSpace(int $subjectId, string $type, string $name, ?int $creatorId): ?int
    {
        $spaceId = DB::table('library_spaces')
            ->where('subject_id', $subjectId)
            ->where('type', $type)
            ->whereNull('deleted_at')
            ->value('id');

        if ($spaceId) {
            return (int) $spaceId;
        }

        DB::table('library_spaces')->insertOrIgnore([
            'subject_id' => $subjectId,
            'type' => $type,
            'name' => $name,
            'is_active' => true,
            'created_by' => $creatorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $spaceId = DB::table('library_spaces')
            ->where('subject_id', $subjectId)
            ->where('type', $type)
            ->whereNull('deleted_at')
            ->value('id');

        return $spaceId ? (int) $spaceId : null;
    }

    /** @return array<string, int> */
    private function ensureSections(int $spaceId, ?int $creatorId): array
    {
        $nodeIds = [];
        $sortOrder = 0;
        foreach (self::SECTIONS as $slug => $section) {
            $nodeId = DB::table('library_nodes')
                ->where('library_space_id', $spaceId)
                ->whereNull('parent_id')
                ->where('slug', $slug)
                ->whereNull('deleted_at')
                ->value('id');

            if (! $nodeId) {
                $nodeId = DB::table('library_nodes')->insertGetId([
                    'library_space_id' => $spaceId,
                    'parent_id' => null,
                    'title' => $section['title'],
                    'slug' => $slug,
                    'node_type' => 'section',
                    'icon' => $section['icon'],
                    'sort_order' => $sortOrder,
                    'is_system' => true,
                    'is_active' => true,
                    'created_by' => $creatorId,
                    'updated_by' => $creatorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $nodeIds[$slug] = (int) $nodeId;
            $sortOrder++;
        }

        return $nodeIds;
    }

    private function sectionFor(string $category): string
    {
        return match (strtolower(trim($category))) {
            'lectures', 'lecture' => 'lectures',
            'summaries', 'summary' => 'summaries',
            'quizzes', 'quiz' => 'quizzes',
            'exams', 'exam' => 'exams',
            'references', 'reference', 'book' => 'references',
            default => 'other',
        };
    }
};
