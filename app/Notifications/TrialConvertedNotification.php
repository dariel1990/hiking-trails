<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialConvertedNotification extends Notification implements ShouldQueue
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
            ->subject('Your XploreSmithers Pro subscription has started')
            ->greeting('Your free trial has ended — you are now on Pro.')
            ->line('Your first payment went through and your XploreSmithers Pro subscription is active. Nothing changes for you: all Pro features stay unlocked.')
            ->line('**Plan:** '.$subscription->productLabel())
            ->line('**Billed via:** '.$subscription->platformLabel());

        if ($subscription->expires_at !== null) {
            $label = $subscription->auto_renewing ? 'Next renewal' : 'Active until';
            $message->line("**{$label}:** ".$subscription->expires_at->toFormattedDateString());
        }

        return $message
            ->action('Manage your subscription', route('settings.subscription'))
            ->line('Thanks for supporting XploreSmithers!');
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
