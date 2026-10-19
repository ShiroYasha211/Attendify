<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

class FlashcardDailyScheduleService
{
    public function remember(int $userId, string $date, string $kind, string $fingerprint, Closure $build, bool $force = false): array
    {
        return DB::transaction(function () use ($userId, $date, $kind, $fingerprint, $build, $force) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            $query = DB::table('flashcard_daily_schedules')
                ->where('user_id', $userId)->where('schedule_date', $date)->where('kind', $kind);
            $saved = $query->first();
            if ($saved && $saved->fingerprint === $fingerprint && ! $force) {
                return json_decode($saved->payload, true, 512, JSON_THROW_ON_ERROR);
            }
            $payload = $build();
            $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
            if ($saved && $saved->fingerprint === $fingerprint && $saved->payload === $encoded) {
                return $payload;
            }
            $values = [
                'fingerprint' => $fingerprint,
                'payload' => $encoded,
                'updated_at' => now(),
            ];
            if ($saved) {
                $query->update($values);
            } else {
                DB::table('flashcard_daily_schedules')->insert($values + [
                    'user_id' => $userId, 'schedule_date' => $date, 'kind' => $kind, 'created_at' => now(),
                ]);
            }

            return $payload;
        });
    }

    public function lastSelectedDates(int $userId, string $before): array
    {
        $dates = [];
        DB::table('flashcard_daily_schedules')->where('user_id', $userId)
            ->where('kind', 'one_line')->where('schedule_date', '<', $before)
            ->orderBy('schedule_date')->select(['schedule_date', 'payload'])
            ->cursor()->each(function ($row) use (&$dates) {
                $payload = json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR);
                foreach ($payload['items'] ?? [] as $item) {
                    $dates[(int) $item['item_id']] = $row->schedule_date;
                }
            });

        return $dates;
    }
}
