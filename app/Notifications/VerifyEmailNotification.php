<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your XploreSmithers email address')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Please confirm this is your email address so you can use every feature of your XploreSmithers account.')
            ->action('Verify email address', $url)
            ->line('This link expires in 60 minutes. If you did not create an account, no further action is required.');
    }
}
