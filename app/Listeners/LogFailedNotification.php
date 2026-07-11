<?php

namespace App\Listeners;

use App\Listeners\Concerns\RecordsEmailLogs;
use App\Models\EmailLog;
use Illuminate\Notifications\Events\NotificationFailed;
use Throwable;

class LogFailedNotification
{
    use RecordsEmailLogs;

    public function handle(NotificationFailed $event): void
    {
        if ($event->channel !== 'mail') {
            return;
        }

        try {
            $exception = $event->data['exception'] ?? null;

            $this->recordEmailLog(
                $event->notifiable,
                $event->notification,
                EmailLog::STATUS_FAILED,
                $exception instanceof Throwable ? $exception->getMessage() : null,
            );
        } catch (Throwable $e) {
            report($e);
        }
    }
}
