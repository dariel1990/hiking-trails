<?php

namespace App\Listeners\Concerns;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Throwable;

trait RecordsEmailLogs
{
    protected function recordEmailLog(mixed $notifiable, Notification $notification, string $status, ?string $error = null): void
    {
        EmailLog::create([
            'notification_type' => $notification::class,
            'recipient_email' => $this->recipientEmail($notifiable, $notification),
            'notifiable_type' => $notifiable instanceof Model ? $notifiable->getMorphClass() : null,
            'notifiable_id' => $notifiable instanceof Model ? $notifiable->getKey() : null,
            'subject' => $this->subject($notifiable, $notification),
            'status' => $status,
            'error' => $error !== null ? Str::limit($error, 2000) : null,
            'payload' => base64_encode(serialize($notification)),
        ]);
    }

    protected function recipientEmail(mixed $notifiable, Notification $notification): string
    {
        $route = $notifiable->routeNotificationFor('mail', $notification);

        if (is_array($route)) {
            $key = array_key_first($route);

            return is_string($key) ? $key : (string) reset($route);
        }

        return (string) $route;
    }

    protected function subject(mixed $notifiable, Notification $notification): ?string
    {
        try {
            return method_exists($notification, 'toMail')
                ? $notification->toMail($notifiable)->subject
                : null;
        } catch (Throwable) {
            return null;
        }
    }
}
