<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Services\SubscriptionEventNotifier;
use App\Support\SubscriptionEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The owner's copy of every subscription lifecycle event. One class covering all
 * five events keeps the fan-out in {@see SubscriptionEventNotifier}
 * to a single call and keeps the email log filterable by event via the payload.
 */
class SubscriptionLifecycleAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SubscriptionEvent $event,
        public Subscription $subscription,
    ) {}

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

        $message = (new MailMessage)
            ->subject('XploreSmithers: '.$this->event->adminSubject())
            ->greeting($this->event->label())
            ->line('**User:** '.($user?->name ?? 'Unknown'))
            ->line('**Email:** '.($user?->email ?? '—'))
            ->line('**Platform:** '.$subscription->platformLabel())
            ->line('**Plan:** '.$subscription->productLabel())
            ->line('**Status:** '.$subscription->status);

        $message = $this->appendEventDetail($message);

        return $message
            ->action('View subscription', route('admin.subscriptions.show', $subscription))
            ->line('You are receiving this because you are set as the owner notification address in admin settings.');
    }

    private function appendEventDetail(MailMessage $message): MailMessage
    {
        $subscription = $this->subscription;

        return match ($this->event) {
            SubscriptionEvent::TrialStarted, SubscriptionEvent::TrialEndingSoon => $subscription->trial_ends_at !== null
                ? $message->line('**Trial ends:** '.$subscription->trial_ends_at->toFormattedDateString())
                : $message,
            SubscriptionEvent::Purchased, SubscriptionEvent::TrialConverted, SubscriptionEvent::Canceled => $subscription->expires_at !== null
                ? $message->line('**'.($this->event === SubscriptionEvent::Canceled ? 'Access until' : 'Next renewal').':** '.$subscription->expires_at->toFormattedDateString())
                : $message,
            SubscriptionEvent::PaymentFailed => $message->line('The subscriber has been emailed asking them to update their payment method.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event->value,
            'subscription_id' => $this->subscription->id,
            'user_id' => $this->subscription->user_id,
        ];
    }
}
