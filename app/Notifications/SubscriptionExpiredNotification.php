<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

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
            ->subject('Your XploreSmithers Pro subscription has ended')
            ->greeting('Your Pro subscription has ended.')
            ->line('Your XploreSmithers Pro subscription ('.$this->subscription->productLabel().') has expired, and Pro features are no longer available on your account.')
            ->line('You can restore full access to offline maps, GPX downloads, and all other Pro features at any time.')
            ->action('Resubscribe to Pro', route('pro.show'))
            ->line('Thanks for exploring with us!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'user_id' => $this->subscription->user_id,
        ];
    }
}
