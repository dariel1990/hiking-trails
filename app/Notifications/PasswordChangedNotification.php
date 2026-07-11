<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your XploreSmithers password was changed')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('The password on your XploreSmithers account was changed on '.now()->toDayDateTimeString().'.')
            ->line('If this was you, no further action is needed.')
            ->line('If you did **not** make this change, reset your password immediately to secure your account.')
            ->action('Reset your password', route('password.request'))
            ->line('You are receiving this because account security changes always trigger an alert.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
