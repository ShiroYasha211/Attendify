<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\StudentNotification;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        $this->checkAndNotify($attendance);
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        $this->checkAndNotify($attendance);
    }

    /**
     * Check status and notify if absent.
     */
    protected function checkAndNotify(Attendance $attendance): void
    {
        // Only notify if status is 'absent'
        // For 'updated', we check if it was already absent to avoid duplicate notifications?
        // Actually, if it changes FROM absent TO present, we might want to notify "Attendance corrected"?
        // But the requirement is specifically "Notify when recording absence".

        if ($attendance->status === 'absent') {
            // Avoid duplicate notifications for the same day/subject if possible?
            // But the observer runs on save.
            // If I update 'absent' to 'absent', it might trigger again if I don't check isDirty.

            if ($attendance->wasRecentlyCreated || $attendance->isDirty('status')) {
                StudentNotification::create([
                    'user_id' => $attendance->student_id,
                    'type'    => 'attendance',
                    'title'   => 'تسجيل غياب',
                    'message' => "تم تسجيل غيابك في مادة {$attendance->subject->name} بتاريخ {$attendance->date->format('Y-m-d')}.",
                    'data'    => [
                        'subject_id' => $attendance->subject_id,
                        'date'       => $attendance->date->format('Y-m-d'),
                    ],
                ]);
            }
        }
    }
}
