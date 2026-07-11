<?php

namespace App\Listeners;

use App\Listeners\Concerns\RecordsEmailLogs;
use App\Models\EmailLog;
use Illuminate\Notifications\Events\NotificationSent;
use Throwable;

class LogSentNotification
{
    use RecordsEmailLogs;

    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'mail') {
            return;
        }

        try {
            $this->recordEmailLog($event->notifiable, $event->notification, EmailLog::STATUS_SENT);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
