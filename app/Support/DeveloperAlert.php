<?php

namespace App\Support;

use App\Models\Setting;
use App\Notifications\ScheduledTaskFailedNotification;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Emails the configured developer address (admin settings → System) when a
 * scheduled task fails. Silently does nothing when no address is configured,
 * and never throws — an alerting failure must not mask the original failure.
 */
class DeveloperAlert
{
    public static function send(string $task, string $summary): void
    {
        try {
            $email = Setting::get(Setting::DEVELOPER_EMAIL);

            if (blank($email)) {
                return;
            }

            Notification::route('mail', $email)
                ->notify(new ScheduledTaskFailedNotification($task, $summary));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
