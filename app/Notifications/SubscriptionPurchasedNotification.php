<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionPurchasedNotification extends Notification implements ShouldQueue
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

        $platform = $subscription->platform === 'android' ? 'Google Play' : 'the web';

        $message = (new MailMessage)
            ->subject('Welcome to XploreSmithers Pro')
            ->greeting('Thanks for subscribing!')
            ->line('Your XploreSmithers Pro subscription is now active. You have full access to Pro features on both the website and the Android app.')
            ->line('**Plan:** '.$subscription->productLabel())
            ->line('**Purchased via:** '.$platform);

        if ($subscription->expires_at !== null) {
            $label = $subscription->auto_renewing ? 'Renews on' : 'Active until';
            $message->line("**{$label}:** ".$subscription->expires_at->toFormattedDateString());
        }

        return $message
            ->action('Manage your subscription', route('settings.subscription'))
            ->line('Happy exploring!');
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
