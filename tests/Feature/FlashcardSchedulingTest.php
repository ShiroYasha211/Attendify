<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Student\FlashcardController;
use App\Models\FlashcardUserSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FlashcardSchedulingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-19 09:00:00'));
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
        });
        DB::table('users')->insert([['id' => 1, 'name' => 'First'], ['id' => 2, 'name' => 'Second']]);
        foreach ([
            '2026_03_22_060000_create_flashcard_packs_table.php',
            '2026_03_22_060001_create_flashcard_items_table.php',
            '2026_03_22_060002_create_flashcard_progress_table.php',
            '2026_04_21_180000_expand_flashcards_for_nested_mixed_mode.php',
            '2026_06_01_000001_add_user_schedule_preferences_to_flashcard_packs_table.php',
            '2026_06_01_000002_create_flashcard_user_settings_table.php',
            '2026_06_07_000001_add_one_line_notification_settings_to_flashcard_user_settings_table.php',
            '2026_10_19_000001_create_flashcard_daily_schedules_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function addPack(string $type, int $count): array
    {
        $pack = DB::table('flashcard_packs')->insertGetId([
            'user_id' => 1, 'title' => 'Pack', 'display_mode' => $type,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $ids[] = DB::table('flashcard_items')->insertGetId([
                'pack_id' => $pack, 'item_type' => $type, 'front_content' => 'Item '.$i,
                'sort_order' => $i, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $ids;
    }

    private function response(string $method = 'oneLineNotifications', array $query = [], int $user = 1): array
    {
        $request = Request::create('/test', 'GET', $query);
        $request->setUserResolver(fn () => User::findOrFail($user));

        return app(FlashcardController::class)->{$method}($request)->getData(true)['data'];
    }

    public function test_repeated_requests_preserve_ids_and_times(): void
    {
        $this->addPack('one_line', 8);
        $first = $this->response();
        Carbon::setTestNow(Carbon::parse('2026-10-19 09:10:00'));
        $second = $this->response();
        $this->assertSame($first['items'], $second['items']);
    }

    public function test_three_days_rotate_without_repeating_unseen_messages(): void
    {
        $this->addPack('one_line', 8);
        $this->response();
        FlashcardUserSetting::where('user_id', 1)->update(['one_line_daily_limit' => 2]);
        $data = $this->response(query: ['days' => 3]);
        $this->assertCount(6, $data['items']);
        $this->assertCount(6, array_unique(array_column($data['items'], 'item_id')));
        $this->assertSame('2026-10-22', substr($data['valid_until'], 0, 10));
    }

    public function test_conflicting_quiet_window_creates_no_forbidden_notifications(): void
    {
        $this->addPack('one_line', 3);
        $this->response();
        FlashcardUserSetting::where('user_id', 1)->update([
            'one_line_active_from_time' => '09:00', 'one_line_active_to_time' => '10:00',
            'one_line_quiet_start' => '00:00', 'one_line_quiet_end' => '23:59',
        ]);
        $this->assertSame([], $this->response()['items']);
    }

    public function test_hundred_messages_are_supported_and_stay_in_their_day(): void
    {
        $this->addPack('one_line', 100);
        $this->response();
        FlashcardUserSetting::where('user_id', 1)->update(['one_line_daily_limit' => 100, 'one_line_frequency_minutes' => 1]);
        $items = $this->response()['items'];
        $this->assertCount(100, $items);
        foreach ($items as $item) {
            $this->assertSame('2026-10-19', substr($item['scheduled_at'], 0, 10));
        }
    }

    public function test_schedules_are_isolated_by_user(): void
    {
        $this->addPack('one_line', 4);
        $this->response();
        $this->assertSame([], $this->response(user: 2)['items']);
    }

    public function test_review_uses_due_date_and_preserves_remaining_times(): void
    {
        $ids = $this->addPack('qa', 3);
        DB::table('flashcard_progress')->insert([
            'user_id' => 1, 'item_id' => $ids[2], 'next_review_at' => now()->addDay(),
        ]);
        $first = $this->response('getDailyQueue');
        $this->assertCount(2, $first['queue']);
        $remaining = $first['queue'][1];
        DB::table('flashcard_progress')->insert([
            'user_id' => 1, 'item_id' => $ids[0], 'last_shown_at' => now(), 'next_review_at' => now()->addDay(),
        ]);
        Carbon::setTestNow(Carbon::parse('2026-10-19 09:15:00'));
        $second = $this->response('getDailyQueue');
        $this->assertCount(1, $second['queue']);
        $this->assertSame($remaining['scheduled_at'], $second['queue'][0]['scheduled_at']);
    }

    public function test_cards_becoming_due_later_today_are_not_lost_after_an_empty_request(): void
    {
        $ids = $this->addPack('qa', 1);
        DB::table('flashcard_progress')->insert([
            'user_id' => 1, 'item_id' => $ids[0], 'next_review_at' => now()->addHour(),
        ]);
        $this->assertSame([], $this->response('getDailyQueue')['queue']);
        Carbon::setTestNow(Carbon::parse('2026-10-19 10:00:00'));
        $queue = $this->response('getDailyQueue')['queue'];
        $this->assertCount(1, $queue);
        $this->assertSame($ids[0], $queue[0]['item_id']);
    }
}
