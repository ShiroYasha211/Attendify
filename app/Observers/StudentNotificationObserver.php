<?php

namespace App\Observers;

use App\Jobs\SendPushNotificationJob;
use App\Models\StudentNotification;

class StudentNotificationObserver
{
    public function created(StudentNotification $studentNotification): void
    {
        if (! $studentNotification->user_id) {
            return;
        }

        SendPushNotificationJob::dispatch($studentNotification->id);
    }
}
