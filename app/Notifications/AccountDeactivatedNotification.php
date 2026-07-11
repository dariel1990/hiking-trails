<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeactivatedNotification extends Notification implements ShouldQueue
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
            ->subject('Your XploreSmithers account has been deactivated')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Your XploreSmithers account has been deactivated, as requested.')
            ->line('You will no longer be able to sign in. If you change your mind, contact us and we can restore your account.')
            ->line('If you did **not** request this, please get in touch right away.')
            ->line('Thanks for exploring with us!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
