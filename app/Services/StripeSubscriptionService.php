<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;

class StripeSubscriptionService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient((string) config('services.stripe.secret'));
    }

    /**
     * Map a plan key (monthly|annual) to the configured Stripe price id.
     */
    public function priceFor(string $plan): ?string
    {
        return config("services.stripe.prices.{$plan}");
    }

    /**
     * Map a Stripe price id back to our internal web product SKU.
     */
    public function productIdForPrice(?string $priceId): string
    {
        return $priceId === config('services.stripe.prices.annual')
            ? 'xs_pro_web_annual'
            : 'xs_pro_web_monthly';
    }

    public function createCheckoutSession(
        User $user,
        string $plan,
        string $successUrl,
        string $cancelUrl,
        string $currency = 'CAD',
    ): CheckoutSession {
        $params = [
            'mode' => 'subscription',
            'line_items' => [['price' => $this->priceFor($plan), 'quantity' => 1]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $user->id,
            'allow_promotion_codes' => true,
            'currency' => strtolower($currency),
            'subscription_data' => [
                'trial_period_days' => setting('trial_days', config('services.stripe.trial_days')),
                'metadata' => ['user_id' => $user->id],
            ],
            'metadata' => ['user_id' => $user->id],
        ];

        if ($user->stripe_customer_id) {
            $params['customer'] = $user->stripe_customer_id;
        } else {
            $params['customer_email'] = $user->email;
        }

        return $this->stripe->checkout->sessions->create($params);
    }

    public function createBillingPortalSession(User $user, string $returnUrl): PortalSession
    {
        return $this->stripe->billingPortal->sessions->create([
            'customer' => $user->stripe_customer_id,
            'return_url' => $returnUrl,
        ]);
    }

    public function retrieveCheckoutSession(string $sessionId): CheckoutSession
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['subscription']]);
    }

    public function retrieveSubscription(string $subscriptionId): StripeSubscription
    {
        return $this->stripe->subscriptions->retrieve($subscriptionId, []);
    }

    /**
     * Persist or refresh a local subscription row from a Stripe subscription object.
     */
    public function syncSubscriptionFromStripe(StripeSubscription $sub, User $user): Subscription
    {
        $priceId = $sub->items->data[0]->price->id ?? null;
        $periodEnd = $sub->current_period_end ?? ($sub->items->data[0]->current_period_end ?? null);

        if ($sub->customer && ! $user->stripe_customer_id) {
            $user->forceFill(['stripe_customer_id' => (string) $sub->customer])->save();
        }

        $subscription = Subscription::updateOrCreate(
            ['purchase_token' => $sub->id],
            [
                'user_id' => $user->id,
                'platform' => 'web',
                'product_id' => $this->productIdForPrice($priceId),
                'status' => $this->mapStatus($sub->status),
                // mapStatus collapses trialing into active, so the trial is
                // carried separately — without it a trial start is
                // indistinguishable from a purchase, and a trial converting to
                // paid changes no column at all.
                'is_trial' => $sub->status === 'trialing',
                'trial_ends_at' => $sub->trial_end ? Carbon::createFromTimestampUTC($sub->trial_end) : null,
                'expires_at' => $periodEnd ? Carbon::createFromTimestampUTC($periodEnd) : null,
                'auto_renewing' => ! ($sub->cancel_at_period_end ?? false),
                'raw_payload' => $sub->toArray(),
            ]
        );

        return $subscription;
    }

    /**
     * Stripe's customer.subscription.trial_will_end event. This only refreshes
     * the local row now — the reminder email itself is sent by
     * `subscriptions:send-trial-reminders`, so that all three platforms use one
     * code path (Apple emits no equivalent event, and Google Play none either).
     *
     * Sending here as well would double-mail Stripe customers, and the old
     * dedupe shared `expiry_reminder_sent_at` with the cancellation-expiry
     * reminder, so one silently suppressed the other.
     */
    public function handleTrialWillEnd(StripeSubscription $sub, User $user): void
    {
        $this->syncSubscriptionFromStripe($sub, $user);
    }

    public function mapStatus(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'trialing', 'active' => 'active',
            'past_due' => 'in_grace_period',
            default => 'expired',
        };
    }
}
