<?php

namespace App\Support;

use App\Observers\SubscriptionObserver;

/**
 * The subscription lifecycle moments that notify the customer and the owner.
 *
 * These are derived centrally in {@see SubscriptionObserver} from
 * column transitions, so every platform (Apple, Google Play, Stripe) and every
 * write path (webhook, verify-purchase, admin panel, scheduled command) produces
 * the same events without dispatching notifications itself.
 */
enum SubscriptionEvent: string
{
    /**
     * A paid subscription started without a trial. Not one of the five requested
     * events, but the owner was already alerted on new subscriptions before this
     * pipeline existed, so it is carried through here rather than dropped.
     */
    case Purchased = 'purchased';

    case TrialStarted = 'trial_started';
    case TrialEndingSoon = 'trial_ending_soon';
    case TrialConverted = 'trial_converted';
    case PaymentFailed = 'payment_failed';
    case Canceled = 'canceled';

    /**
     * Short human label, used in admin email bodies and log output.
     */
    public function label(): string
    {
        return match ($this) {
            self::Purchased => 'New subscription',
            self::TrialStarted => 'Free trial started',
            self::TrialEndingSoon => 'Free trial ending soon',
            self::TrialConverted => 'Trial converted to paid',
            self::PaymentFailed => 'Payment failed',
            self::Canceled => 'Subscription canceled',
        };
    }

    /**
     * Subject line for the owner/admin copy of this event.
     */
    public function adminSubject(): string
    {
        return match ($this) {
            self::Purchased => 'New XploreSmithers Pro subscription',
            self::TrialStarted => 'New free trial started',
            self::TrialEndingSoon => 'A free trial is ending soon',
            self::TrialConverted => 'A trial converted to a paid subscription',
            self::PaymentFailed => 'A subscription payment failed',
            self::Canceled => 'A subscription was canceled',
        };
    }
}
