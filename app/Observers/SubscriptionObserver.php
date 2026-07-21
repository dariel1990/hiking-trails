<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpiredNotification;
use App\Services\SubscriptionEventNotifier;
use App\Support\SubscriptionEvent;
use Illuminate\Notifications\Notification;
use Throwable;

/**
 * Derives subscription lifecycle events from column transitions and hands them to
 * {@see SubscriptionEventNotifier}, which emails the subscriber and the owner.
 *
 * Every write path funnels through here — Stripe webhook, Apple notifications,
 * Google Play verify/RTDN, the admin panel and the scheduled expiry command — so
 * platform integrations only have to persist the right columns and never dispatch
 * mail themselves. Events fire on genuine transitions only, so webhook replays
 * and repeated syncs stay silent.
 */
class SubscriptionObserver
{
    public function __construct(private readonly SubscriptionEventNotifier $notifier) {}

    public function created(Subscription $subscription): void
    {
        if ($subscription->status !== 'active') {
            return;
        }

        $this->notifier->dispatch(
            $subscription->is_trial ? SubscriptionEvent::TrialStarted : SubscriptionEvent::Purchased,
            $subscription,
        );
    }

    public function updated(Subscription $subscription): void
    {
        // A trial converting to paid changes no status — the row stays "active"
        // throughout — so this has to key on is_trial, not status.
        if ($this->convertedFromTrial($subscription)) {
            $this->notifier->dispatch(SubscriptionEvent::TrialConverted, $subscription);

            return;
        }

        if (! $subscription->wasChanged('status')) {
            // A self-cancel on Stripe/Apple keeps the subscription active until the
            // paid period ends and only flips auto-renewal. Confirm the
            // cancellation right away instead of staying silent until expiry.
            if ($this->turnedOffAutoRenewal($subscription)) {
                $this->notifier->dispatch(SubscriptionEvent::Canceled, $subscription);
            }

            return;
        }

        $event = match ($subscription->status) {
            'in_grace_period', 'on_hold' => SubscriptionEvent::PaymentFailed,
            'canceled', 'cancelled' => SubscriptionEvent::Canceled,
            default => null,
        };

        if ($event !== null) {
            $this->notifier->dispatch($event, $subscription);

            return;
        }

        // Expiry is not one of the five owner-notified events; the subscriber is
        // still told their access has ended.
        if ($subscription->status === 'expired') {
            $this->notifyUser($subscription, new SubscriptionExpiredNotification($subscription));
        }
    }

    private function convertedFromTrial(Subscription $subscription): bool
    {
        return $subscription->wasChanged('is_trial')
            && ! $subscription->is_trial
            && $subscription->status === 'active';
    }

    private function turnedOffAutoRenewal(Subscription $subscription): bool
    {
        return $subscription->wasChanged('auto_renewing')
            && ! $subscription->auto_renewing
            && $subscription->status === 'active';
    }

    /**
     * Direct customer send for the two notifications that are not lifecycle
     * events (plain purchase and expiry), keeping the same failure isolation.
     */
    private function notifyUser(Subscription $subscription, Notification $notification): void
    {
        $user = $subscription->user;

        if ($user === null) {
            return;
        }

        try {
            $user->notify($notification);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
