<?php

namespace App\Services;

use App\Models\FlashcardUserSetting;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FlashcardReviewScheduleService
{
    public function resolve(int $userId, FlashcardUserSetting $settings, Collection $catalog, Collection $candidates, Collection $reviewed, bool $restart, Closure $times): array
    {
        $date = now()->toDateString();
        $fingerprint = hash('sha256', json_encode($settings->only([
            'smart_review_enabled', 'daily_card_limit', 'smart_review_frequency_minutes',
            'active_from_time', 'active_to_time', 'quiet_start', 'quiet_end',
        ]), JSON_THROW_ON_ERROR));

        return app(FlashcardDailyScheduleService::class)->remember(
            $userId, $date, 'review_plan', $fingerprint,
            function () use ($userId, $date, $fingerprint, $settings, $catalog, $candidates, $reviewed, $restart, $times) {
                $saved = DB::table('flashcard_daily_schedules')->where('user_id', $userId)
                    ->where('schedule_date', $date)->where('kind', 'review_plan')->first();
                $plan = $saved ? json_decode($saved->payload, true, 512, JSON_THROW_ON_ERROR) : [];
                $entries = array_filter($plan['entries'] ?? [], fn ($entry, $id) => $catalog->has($id), ARRAY_FILTER_USE_BOTH);
                // Preserve both completed slots and pending times when upgrading an existing day.
                $legacyFingerprint = hash('sha256', json_encode($settings->only($settings->getFillable()), JSON_THROW_ON_ERROR));
                $legacy = ! $saved ? DB::table('flashcard_daily_schedules')->where('user_id', $userId)
                    ->where('schedule_date', $date)->where('kind', 'review')->where('fingerprint', $legacyFingerprint)->first() : null;
                if ($legacy) {
                    foreach (json_decode($legacy->payload, true, 512, JSON_THROW_ON_ERROR) as $id => $at) {
                        if (! $catalog->has($id)) {
                            continue;
                        }
                        $progress = $catalog[$id]['item']->user_progress;
                        $entries[$id] = [
                            'scheduled_at' => $at,
                            'baseline' => (int) ($progress?->times_shown ?? 0) -
                                ($progress?->last_shown_at?->toDateString() === $date ? 1 : 0),
                        ];
                    }
                }
                $pending = array_filter($entries, fn ($entry, $id) => (int) ($catalog[$id]['item']->user_progress?->times_shown ?? 0) <= $entry['baseline'], ARRAY_FILTER_USE_BOTH);

                if ($restart && $pending) {
                    throw ValidationException::withMessages(['review' => 'أكمل كروت اليوم المتبقية قبل إعادة المراجعة.']);
                }

                $repeat = $reviewed->filter(function ($entry) use ($settings, $restart) {
                    if ($restart || $settings->auto_restart_enabled) {
                        return true;
                    }

                    return match ($entry['pack']->restart_mode ?? 'none') {
                        'all' => true,
                        'hard_only', 'wrong_only' => $entry['item']->user_progress?->last_response === 'hard',
                        default => false,
                    };
                });
                $newCycle = ! $pending && $repeat->isNotEmpty() &&
                    ($restart || $candidates->isEmpty() || ($entries && count($entries) >= (int) $settings->daily_card_limit));
                $reset = (! $saved && ! $legacy) || ($saved && $saved->fingerprint !== $fingerprint) || $newCycle;
                if ($reset) {
                    $entries = [];
                }
                if ($reset || ! isset($plan['cycle'])) {
                    $plan['cycle'] = (string) Str::uuid();
                }

                // A future card is still pending: never restart a completed pack ahead of it.
                $selection = $newCycle ? $repeat : $candidates;
                $missing = $selection->reject(fn ($entry) => isset($entries[$entry['item']->id]))
                    ->take(max(0, (int) $settings->daily_card_limit - count($entries)))->values();
                $from = now()->startOfMinute();
                if ($newCycle) {
                    $from->addMinutes(max(1, (int) $settings->smart_review_frequency_minutes));
                } elseif ($entries) {
                    $afterLast = collect($entries)->map(fn ($entry) => Carbon::parse($entry['scheduled_at']))
                        ->sortBy(fn ($at) => $at->timestamp)->last()
                        ->addMinutes(max(1, (int) $settings->smart_review_frequency_minutes));
                    if ($afterLast->isAfter($from)) {
                        $from = $afterLast;
                    }
                }
                $scheduled = $times($missing->count(), $from);
                foreach ($missing as $index => $entry) {
                    $id = $entry['item']->id;
                    $at = ($scheduled[$index] ?? null)?->toIso8601String();
                    if ($at === null) {
                        continue;
                    }
                    $entries[$id] = [
                        'scheduled_at' => $at,
                        'baseline' => (int) ($entry['item']->user_progress?->times_shown ?? 0),
                    ];
                }

                return ['cycle' => $plan['cycle'], 'entries' => $entries];
            }, true
        );
    }
}
