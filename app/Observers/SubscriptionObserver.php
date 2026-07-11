<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Notifications\SubscriptionCanceledNotification;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\SubscriptionPaymentIssueNotification;
use App\Notifications\SubscriptionPurchasedNotification;
use Illuminate\Notifications\Notification;
use Throwable;

/**
 * Sends customer-facing lifecycle emails for every subscription status change,
 * regardless of which code path performed the write (Stripe webhook, Google Play
 * verify, admin panel, scheduled expiry command). Emails fire only when the
 * status column actually changes, so webhook replays stay silent.
 */
class SubscriptionObserver
{
    public function created(Subscription $subscription): void
    {
        if ($subscription->status === 'active') {
            $this->notifyUser($subscription, new SubscriptionPurchasedNotification($subscription));
        }
    }

    public function updated(Subscription $subscription): void
    {
        if (! $subscription->wasChanged('status')) {
            // A Stripe self-cancel keeps the subscription active until the paid
            // period ends and only flips cancel_at_period_end (synced here as
            // auto_renewing). Confirm the cancellation right away instead of
            // staying silent until the expiry email.
            if ($this->turnedOffAutoRenewal($subscription)) {
                $this->notifyUser($subscription, new SubscriptionCanceledNotification($subscription));
            }

            return;
        }

        $notification = match ($subscription->status) {
            'in_grace_period', 'on_hold' => new SubscriptionPaymentIssueNotification($subscription),
            'canceled', 'cancelled' => new SubscriptionCanceledNotification($subscription),
            'expired' => new SubscriptionExpiredNotification($subscription),
            default => null,
        };

        if ($notification !== null) {
            $this->notifyUser($subscription, $notification);
        }
    }

    private function turnedOffAutoRenewal(Subscription $subscription): bool
    {
        return $subscription->wasChanged('auto_renewing')
            && ! $subscription->auto_renewing
            && $subscription->status === 'active';
    }

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
