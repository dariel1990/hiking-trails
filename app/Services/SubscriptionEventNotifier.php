<?php

namespace App\Services;

use App\Models\Subscription;
use App\Notifications\SubscriptionCanceledNotification;
use App\Notifications\SubscriptionLifecycleAdminNotification;
use App\Notifications\SubscriptionPaymentIssueNotification;
use App\Notifications\SubscriptionPurchasedNotification;
use App\Notifications\TrialConvertedNotification;
use App\Notifications\TrialEndingSoonNotification;
use App\Notifications\TrialStartedNotification;
use App\Support\SubscriptionEvent;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Throwable;

/**
 * The single fan-out point for subscription lifecycle emails: every event goes
 * to the subscriber and to the owner address configured in admin settings.
 *
 * Callers only describe *what happened* — they never choose recipients — so
 * changing who is copied is a one-place change, and no platform integration can
 * accidentally email one audience but not the other.
 *
 * Sends are individually guarded: a mail failure must never break the billing
 * write that triggered it, and failing to reach the owner must not stop the
 * customer being told.
 */
class SubscriptionEventNotifier
{
    public function dispatch(SubscriptionEvent $event, Subscription $subscription): void
    {
        $this->notifyCustomer($event, $subscription);
        $this->notifyOwner($event, $subscription);
    }

    private function notifyCustomer(SubscriptionEvent $event, Subscription $subscription): void
    {
        $user = $subscription->user;

        if ($user === null) {
            return;
        }

        try {
            $user->notify($this->customerNotification($event, $subscription));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function notifyOwner(SubscriptionEvent $event, Subscription $subscription): void
    {
        try {
            $email = setting('owner_notification_email');

            if (blank($email)) {
                return;
            }

            NotificationFacade::route('mail', $email)
                ->notify(new SubscriptionLifecycleAdminNotification($event, $subscription));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function customerNotification(SubscriptionEvent $event, Subscription $subscription): Notification
    {
        return match ($event) {
            SubscriptionEvent::Purchased => new SubscriptionPurchasedNotification($subscription),
            SubscriptionEvent::TrialStarted => new TrialStartedNotification($subscription),
            SubscriptionEvent::TrialEndingSoon => new TrialEndingSoonNotification($subscription),
            SubscriptionEvent::TrialConverted => new TrialConvertedNotification($subscription),
            SubscriptionEvent::PaymentFailed => new SubscriptionPaymentIssueNotification($subscription),
            SubscriptionEvent::Canceled => new SubscriptionCanceledNotification($subscription),
        };
    }
}
