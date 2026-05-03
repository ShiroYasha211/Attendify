<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student\StudentScheduleItem;
use App\Services\StudyReminderNotificationService;
use Carbon\Carbon;

class SendStudyReminders extends Command
{
    protected $signature = 'study:send-reminders';
    protected $description = 'Send recurring study reminders based on user preference (daily/weekly)';

    public function handle(StudyReminderNotificationService $studyReminderNotifications)
    {
        $today = Carbon::today();
        $dayOfWeek = $today->dayOfWeek; // 0=Sunday ... 6=Saturday

        // ── Daily Reminders ──
        // Get all pending study items with repeat_type = 'daily'
        $dailyItems = StudentScheduleItem::where('repeat_type', 'daily')
            ->where('is_completed', false)
            ->where('reminder_sent', false)
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<=', $today)
            ->get();

        $dailySent = 0;
        foreach ($dailyItems as $item) {
            $studyReminderNotifications->notify(
                $item,
                'تذكير يومي بالمذاكرة',
                "لا تنسَ مذاكرة: {$item->display_title}"
            );
            $item->update(['reminder_sent' => true]);
            $dailySent++;
        }

        // ── Weekly Reminders (send on Saturday = start of week) ──
        $weeklyItems = StudentScheduleItem::where('repeat_type', 'weekly')
            ->where('is_completed', false)
            ->where('reminder_sent', false)
            ->whereNotNull('scheduled_date')
            ->whereDate('scheduled_date', '<=', $today)
            ->get();

        $weeklySent = 0;
        // Send on Saturday (dayOfWeek = 6) — start of Arabic week
        if ($dayOfWeek === 6) {
            foreach ($weeklyItems as $item) {
                $studyReminderNotifications->notify(
                    $item,
                    'تذكير أسبوعي بالمذاكرة',
                    "تذكير أسبوعي: {$item->display_title}"
                );
                $item->update(['reminder_sent' => true]);
                $weeklySent++;
            }
        }

        $this->info("Sent {$dailySent} daily + {$weeklySent} weekly study reminders.");

        return Command::SUCCESS;
    }
}
