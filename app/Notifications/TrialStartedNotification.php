<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialStartedNotification extends Notification implements ShouldQueue
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
            ->subject('Your XploreSmithers Pro free trial has started')
            ->greeting('Your free trial is live!')
            ->line('You now have full access to XploreSmithers Pro — offline maps, GPX downloads, recreation sites and Pro videos.')
            ->line('**Plan after trial:** '.$subscription->productLabel())
            ->line('**Started via:** '.$subscription->platformLabel());

        $endsAt = $subscription->trial_ends_at ?? $subscription->expires_at;

        if ($endsAt !== null) {
            $message->line('**Free until:** '.$endsAt->toFormattedDateString());
            $message->line('We will remind you before the trial ends. If you keep it, your subscription continues automatically from that date.');
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
            'trial_ends_at' => $this->subscription->trial_ends_at?->toIso8601String(),
        ];
    }
}
