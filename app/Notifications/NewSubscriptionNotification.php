<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSubscriptionNotification extends Notification implements ShouldQueue
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
        $user = $subscription->user;

        $platform = $subscription->platform === 'android' ? 'Android (Google Play)' : 'Web (Stripe)';

        $product = match ($subscription->product_id) {
            'xs_offline_monthly', 'xs_pro_web_monthly' => 'Pro Monthly',
            'xs_offline_annual', 'xs_pro_web_annual' => 'Pro Annual',
            default => $subscription->product_id,
        };

        return (new MailMessage)
            ->subject('New XploreSmithers Pro subscription')
            ->greeting('A new Pro subscription has been activated.')
            ->line('**User:** '.($user?->name ?? 'Unknown'))
            ->line('**Email:** '.($user?->email ?? '—'))
            ->line('**Platform:** '.$platform)
            ->line('**Plan:** '.$product)
            ->line('**Subscribed at:** '.$subscription->created_at->toFormattedDateString())
            ->action('View subscription', route('admin.subscriptions.show', $subscription))
            ->line('You are receiving this because you are an administrator.');
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
