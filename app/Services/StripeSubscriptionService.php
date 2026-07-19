<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\TrialEndingSoonNotification;
use Illuminate\Support\Carbon;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;
use Throwable;

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
                'expires_at' => $periodEnd ? Carbon::createFromTimestampUTC($periodEnd) : null,
                'auto_renewing' => ! ($sub->cancel_at_period_end ?? false),
                'raw_payload' => $sub->toArray(),
            ]
        );

        // Notify admins the first time a subscription is created in an active state.
        // Keying on wasRecentlyCreated dedupes across the two entry points (webhook and
        // the checkout success redirect) and skips renewal/update syncs of existing rows.
        if ($subscription->wasRecentlyCreated && $subscription->status === 'active') {
            User::where('is_admin', true)->each(function (User $admin) use ($subscription): void {
                try {
                    $admin->notify(new NewSubscriptionNotification($subscription));
                } catch (Throwable $e) {
                    report($e);
                }
            });
        }

        return $subscription;
    }

    /**
     * Map a Stripe subscription status to our local entitlement status.
     */
    /**
     * Stripe's customer.subscription.trial_will_end event (fired ~3 days before
     * a trial converts to a paid subscription). Tells the user their card is
     * about to be charged. The expiry_reminder_sent_at stamp dedupes webhook
     * retries — one reminder per billing period.
     */
    public function handleTrialWillEnd(StripeSubscription $sub, User $user): void
    {
        $subscription = $this->syncSubscriptionFromStripe($sub, $user);

        if (! $subscription->isEntitled()) {
            return;
        }

        $alreadyReminded = $subscription->expiry_reminder_sent_at !== null
            && ($subscription->expires_at === null
                || $subscription->expiry_reminder_sent_at->gte($subscription->expires_at->copy()->subDays(8)));

        if ($alreadyReminded) {
            return;
        }

        try {
            $user->notify(new TrialEndingSoonNotification($subscription));
            $subscription->forceFill(['expiry_reminder_sent_at' => now()])->save();
        } catch (Throwable $e) {
            report($e);
        }
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
