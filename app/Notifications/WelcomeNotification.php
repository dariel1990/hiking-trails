<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public bool $viaGoogle = false) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Welcome to XploreSmithers')
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('Thanks for joining XploreSmithers — your guide to hiking trails, fishing lakes, and outdoor adventures around Smithers, BC.');

        if ($this->viaGoogle) {
            $message->line('You signed up with your Google account, so there is no password to remember — just use **Sign in with Google** whenever you come back.');
        } else {
            $message->line('Keep an eye out for a separate email asking you to verify your email address.');
        }

        return $message
            ->line('Here is what you can do with your account: save favourite trails, submit trail photos, and go Pro for offline maps and GPX downloads.')
            ->action('Start exploring', route('trails.index'))
            ->line('Happy trails!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'via_google' => $this->viaGoogle,
        ];
    }
}
