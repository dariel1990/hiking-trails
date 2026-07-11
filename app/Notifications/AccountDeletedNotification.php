<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $name) {}

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
            ->subject('Your XploreSmithers account has been deleted')
            ->greeting('Hi '.$this->name.',')
            ->line('Your XploreSmithers account and its data have been permanently deleted, as requested.')
            ->line('If you did **not** request this, please contact us right away.')
            ->line('You are always welcome back — thanks for exploring with us!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
