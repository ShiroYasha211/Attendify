<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student\StudentScheduleItem;
use App\Models\StudentNotification;
use Carbon\Carbon;

class SendStudyReminders extends Command
{
    protected $signature = 'study:send-reminders';
    protected $description = 'Send recurring study reminders based on user preference (daily/weekly)';

    public function handle()
    {
        $today = Carbon::today();
        $dayOfWeek = $today->dayOfWeek; // 0=Sunday ... 6=Saturday

        // ── Daily Reminders ──
        // Get all pending study items with repeat_type = 'daily'
        $dailyItems = StudentScheduleItem::where('repeat_type', 'daily')
            ->where('is_completed', false)
            ->whereNotNull('scheduled_date')
            ->get();

        $dailySent = 0;
        foreach ($dailyItems as $item) {
            StudentNotification::create([
                'user_id' => $item->user_id,
                'type'    => 'study_reminder',
                'title'   => '📖 تذكير يومي بالمذاكرة',
                'message' => "لا تنسَ مذاكرة: {$item->display_title}",
                'data'    => [
                    'schedule_item_id' => $item->id,
                    'action_url'       => '/student/schedule',
                ],
            ]);
            $dailySent++;
        }

        // ── Weekly Reminders (send on Saturday = start of week) ──
        $weeklyItems = StudentScheduleItem::where('repeat_type', 'weekly')
            ->where('is_completed', false)
            ->whereNotNull('scheduled_date')
            ->get();

        $weeklySent = 0;
        // Send on Saturday (dayOfWeek = 6) — start of Arabic week
        if ($dayOfWeek === 6) {
            foreach ($weeklyItems as $item) {
                StudentNotification::create([
                    'user_id' => $item->user_id,
                    'type'    => 'study_reminder',
                    'title'   => '📅 تذكير أسبوعي بالمذاكرة',
                    'message' => "تذكير أسبوعي: {$item->display_title}",
                    'data'    => [
                        'schedule_item_id' => $item->id,
                        'action_url'       => '/student/schedule',
                    ],
                ]);
                $weeklySent++;
            }
        }

        $this->info("Sent {$dailySent} daily + {$weeklySent} weekly study reminders.");

        return Command::SUCCESS;
    }
}
