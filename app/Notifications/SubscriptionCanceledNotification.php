<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCanceledNotification extends Notification implements ShouldQueue
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
        $subscription = $this->subscription;

        $message = (new MailMessage)
            ->subject('Your XploreSmithers Pro subscription has been canceled')
            ->greeting('Sorry to see you go.')
            ->line('Your XploreSmithers Pro subscription ('.$subscription->productLabel().') has been canceled.');

        if ($subscription->expires_at !== null && $subscription->expires_at->isFuture()) {
            $message->line('You keep Pro access until **'.$subscription->expires_at->toFormattedDateString().'**.');
        }

        return $message
            ->line('Changed your mind? You can resubscribe at any time.')
            ->action('Resubscribe to Pro', route('pro.show'))
            ->line('Thanks for having been a Pro member!');
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
