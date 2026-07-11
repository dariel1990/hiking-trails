<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringSoonNotification extends Notification implements ShouldQueue
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
        $expiresAt = $this->subscription->expires_at;
        $days = max(1, (int) ceil(now()->diffInHours($expiresAt, false) / 24));

        $when = $days === 1 ? 'tomorrow' : "in {$days} days";

        return (new MailMessage)
            ->subject("Your XploreSmithers Pro subscription expires {$when}")
            ->greeting('Your Pro access is ending soon.')
            ->line('Your XploreSmithers Pro subscription ('.$this->subscription->productLabel().') expires on **'.$expiresAt->toFormattedDateString().'** and is not set to renew.')
            ->line('Renew now to keep uninterrupted access to offline maps, GPX downloads, and all other Pro features.')
            ->action('Renew your subscription', route('pro.show'))
            ->line('If you meant to let it lapse, no action is needed.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'user_id' => $this->subscription->user_id,
            'expires_at' => $this->subscription->expires_at?->toIso8601String(),
        ];
    }
}
