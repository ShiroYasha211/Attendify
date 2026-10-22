<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Student\FlashcardController;
use App\Models\FlashcardItem;
use App\Models\FlashcardPack;
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
            '2026_10_21_000001_create_flashcard_progress_events_table.php',
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
        $this->assertSame([
            '2026-10-19' => 2, '2026-10-20' => 2, '2026-10-21' => 2,
        ], array_count_values(array_map(fn ($item) => substr($item['scheduled_at'], 0, 10), $data['items'])));
        $this->assertCount(6, array_unique(array_column($data['items'], 'scheduled_at')));
        $this->assertSame('09:01', Carbon::parse($data['items'][0]['scheduled_at'])->format('H:i'));
        $this->assertSame('08:00', Carbon::parse($data['items'][2]['scheduled_at'])->format('H:i'));
        $this->assertSame(30.0, Carbon::parse($data['items'][0]['scheduled_at'])
            ->diffInMinutes(Carbon::parse($data['items'][1]['scheduled_at'])));
        $this->assertSame($data['items'], $this->response(query: ['days' => 3])['items']);
    }

    public function test_late_night_schedule_keeps_future_days_separate(): void
    {
        $this->addPack('one_line', 8);
        $this->response();
        FlashcardUserSetting::where('user_id', 1)->update([
            'one_line_daily_limit' => 3, 'one_line_frequency_minutes' => 1,
        ]);
        Carbon::setTestNow(Carbon::parse('2026-10-19 23:58:00'));
        $items = $this->response(query: ['days' => 3])['items'];
        $this->assertSame([
            '2026-10-19' => 1, '2026-10-20' => 3, '2026-10-21' => 3,
        ], array_count_values(array_map(fn ($item) => substr($item['scheduled_at'], 0, 10), $items)));
        foreach ($items as $item) {
            $this->assertTrue(Carbon::parse($item['scheduled_at'])->isAfter(now()));
        }
    }

    public function test_legacy_snapshot_is_rebuilt_without_changing_user_settings(): void
    {
        $ids = $this->addPack('one_line', 1);
        $this->response();
        $settings = FlashcardUserSetting::where('user_id', 1)->firstOrFail();
        $item = FlashcardItem::findOrFail($ids[0]);
        $pack = FlashcardPack::findOrFail($item->pack_id);
        $legacyFingerprint = hash('sha256', json_encode([
            $settings->only($settings->getFillable()),
            [[
                $pack->id, $pack->updated_at?->toIso8601String(),
                $item->id, $item->updated_at?->toIso8601String(),
                $item->front_content, $item->priority, $item->sort_order,
            ]],
        ], JSON_THROW_ON_ERROR));
        DB::table('flashcard_daily_schedules')->where('user_id', 1)->where('kind', 'one_line')->update([
            'fingerprint' => $legacyFingerprint,
            'payload' => json_encode(['items' => [['scheduled_at' => '2026-10-19T08:00:00+00:00']]], JSON_THROW_ON_ERROR),
        ]);
        Carbon::setTestNow(Carbon::parse('2026-10-19 09:10:00'));

        $rebuilt = $this->response();
        $this->assertSame('09:11', Carbon::parse($rebuilt['items'][0]['scheduled_at'])->format('H:i'));
        $this->assertNotSame($legacyFingerprint, DB::table('flashcard_daily_schedules')
            ->where('user_id', 1)->where('kind', 'one_line')->value('fingerprint'));
        $this->assertSame($settings->getAttributes(), $settings->fresh()->getAttributes());
        $this->assertSame($rebuilt['items'], $this->response()['items']);
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
            'user_id' => 1, 'item_id' => $ids[0], 'times_shown' => 1, 'last_shown_at' => now(), 'next_review_at' => now()->addDay(),
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

    public function test_retrying_a_review_event_does_not_count_the_answer_twice(): void
    {
        $ids = $this->addPack('mcq', 1);
        $query = ['item_id' => $ids[0], 'response_level' => 'easy',
            'client_event_id' => '92000000-0000-4000-8000-000000000001'];
        $first = $this->response('recordProgress', $query);
        $second = $this->response('recordProgress', $query);
        $this->assertSame($first, $second);
        $this->assertSame(1, DB::table('flashcard_progress')->where('item_id', $ids[0])->value('times_shown'));
        $this->assertSame(1, DB::table('flashcard_progress_events')->count());
        $request = Request::create('/test', 'POST', array_replace($query, ['response_level' => 'hard']));
        $request->setUserResolver(fn () => User::findOrFail(1));
        $this->assertSame(409, app(FlashcardController::class)->recordProgress($request)->getStatusCode());
        $this->assertSame(1, DB::table('flashcard_progress')->where('item_id', $ids[0])->value('times_shown'));
    }

    public function test_future_pending_card_prevents_pack_and_global_restart(): void
    {
        $ids = $this->addPack('qa', 2);
        $first = $this->response('getDailyQueue');
        FlashcardUserSetting::where('user_id', 1)->update(['auto_restart_enabled' => true]);
        DB::table('flashcard_packs')->update(['restart_mode' => 'all']);
        $this->response('recordProgress', ['item_id' => $ids[0], 'response_level' => 'easy']);
        Carbon::setTestNow(Carbon::parse('2026-10-19 09:05:00'));
        $second = $this->response('getDailyQueue');
        $this->assertCount(1, $second['queue']);
        $this->assertSame($first['queue'][1], $second['queue'][0]);
        $this->assertFalse($second['queue'][0]['available_now']);
        $this->assertSame($second, $this->response('getDailyQueue'));
    }

    public function test_automatic_round_has_new_keys_and_does_not_restart_on_each_fetch(): void
    {
        $ids = $this->addPack('mcq', 2);
        $first = $this->response('getDailyQueue');
        FlashcardUserSetting::where('user_id', 1)->update(['auto_restart_enabled' => true]);
        foreach ($ids as $id) {
            $this->response('recordProgress', ['item_id' => $id, 'response_level' => 'easy']);
        }
        Carbon::setTestNow(Carbon::parse('2026-10-19 10:00:00'));
        $next = $this->response('getDailyQueue');
        $this->assertCount(2, $next['queue']);
        $this->assertNotSame($first['queue'][0]['review_key'], $next['queue'][0]['review_key']);
        $this->assertSame('10:30', Carbon::parse($next['queue'][0]['scheduled_at'])->format('H:i'));
        $this->assertFalse($next['queue'][0]['available_now']);
        Carbon::setTestNow(Carbon::parse('2026-10-19 10:05:00'));
        $this->assertSame($next['queue'], $this->response('getDailyQueue')['queue']);
    }

    public function test_manual_restart_is_server_owned_and_preserves_progress(): void
    {
        $ids = $this->addPack('qa', 1);
        $first = $this->response('getDailyQueue');
        $this->response('recordProgress', ['item_id' => $ids[0], 'response_level' => 'easy']);
        $this->assertSame([], $this->response('getDailyQueue')['queue']);
        $restarted = $this->response('restartDailyReview');
        $this->assertNotSame($first['queue'][0]['review_key'], $restarted['queue'][0]['review_key']);
        $this->assertSame($restarted['queue'], $this->response('getDailyQueue')['queue']);
        $this->assertSame(1, DB::table('flashcard_progress')->value('times_shown'));
        $this->assertSame([], $this->response('getDailyQueue', user: 2)['queue']);
    }

    public function test_manual_restart_cannot_replace_future_pending_cards(): void
    {
        $this->addPack('qa', 2);
        $this->response('getDailyQueue');
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->response('restartDailyReview');
    }

    public function test_existing_release_schedule_is_adopted_without_moving_times(): void
    {
        $ids = $this->addPack('qa', 1);
        $this->response('settings');
        $settings = FlashcardUserSetting::where('user_id', 1)->firstOrFail();
        $at = '2026-10-19T11:45:00+00:00';
        DB::table('flashcard_daily_schedules')->insert([
            'user_id' => 1, 'schedule_date' => '2026-10-19', 'kind' => 'review',
            'fingerprint' => hash('sha256', json_encode($settings->only($settings->getFillable()), JSON_THROW_ON_ERROR)),
            'payload' => json_encode([$ids[0] => $at]), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $queue = $this->response('getDailyQueue')['queue'];
        $this->assertTrue(Carbon::parse($at)->equalTo(Carbon::parse($queue[0]['scheduled_at'])));
        $this->assertSame($queue, $this->response('getDailyQueue')['queue']);
    }

    public function test_upgrading_a_partial_day_does_not_replace_completed_slots_with_extra_cards(): void
    {
        $ids = $this->addPack('qa', 3);
        $this->response('settings');
        FlashcardUserSetting::where('user_id', 1)->update(['daily_card_limit' => 2]);
        $settings = FlashcardUserSetting::where('user_id', 1)->firstOrFail();
        DB::table('flashcard_daily_schedules')->insert([
            'user_id' => 1, 'schedule_date' => '2026-10-19', 'kind' => 'review',
            'fingerprint' => hash('sha256', json_encode($settings->only($settings->getFillable()), JSON_THROW_ON_ERROR)),
            'payload' => json_encode([$ids[0] => now()->toIso8601String(), $ids[1] => now()->addMinutes(30)->toIso8601String()]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->response('recordProgress', ['item_id' => $ids[0], 'response_level' => 'easy']);
        $queue = $this->response('getDailyQueue')['queue'];
        $this->assertCount(1, $queue);
        $this->assertSame($ids[1], $queue[0]['item_id']);
        $this->assertSame($queue, $this->response('getDailyQueue')['queue']);
    }
}
