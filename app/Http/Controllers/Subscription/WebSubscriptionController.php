<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\CheckoutRequest;
use App\Services\RegionalPricingService;
use App\Services\StripeSubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class WebSubscriptionController extends Controller
{
    public function __construct(
        private StripeSubscriptionService $stripe,
        private RegionalPricingService $pricing,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        if (! config('subscriptions.enabled')) {
            return redirect()->route('home');
        }

        $pricing = $this->pricing->forRequest();

        return view('subscription.pro', [
            'isPro' => (bool) $request->user()?->hasActiveProEntitlement(),
            'stripeEnabled' => (bool) config('services.stripe.enabled'),
            'priceMonthly' => $pricing['monthly'],
            'priceAnnual' => $pricing['annual'],
            'symbol' => $pricing['symbol'],
            'currency' => $pricing['currency'],
            'trialDays' => (int) config('services.stripe.trial_days'),
        ]);
    }

    public function checkout(CheckoutRequest $request): RedirectResponse
    {
        if (! config('subscriptions.enabled')) {
            return redirect()->route('home');
        }

        $user = $request->user();

        if ($user->hasActiveProEntitlement()) {
            return redirect()->route('pro.show')->with('success', 'You already have XploreSmithers Pro.');
        }

        if (! config('services.stripe.enabled')) {
            return redirect()->route('pro.show')
                ->with('error', 'Payments are not configured yet. Please check back soon.');
        }

        try {
            $pricing = $this->pricing->forRequest();

            $session = $this->stripe->createCheckoutSession(
                $user,
                $request->string('plan')->value(),
                route('pro.success').'?session_id={CHECKOUT_SESSION_ID}',
                route('pro.cancel'),
                $pricing['currency'],
            );

            return redirect()->away($session->url);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('pro.show')
                ->with('error', 'We could not start checkout. Please try again.');
        }
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        $synced = false;

        if ($sessionId && config('services.stripe.enabled')) {
            try {
                $session = $this->stripe->retrieveCheckoutSession((string) $sessionId);

                if ((string) $session->client_reference_id !== (string) $request->user()->id) {
                    return redirect()->route('pro.show');
                }

                if ($session->subscription) {
                    $this->stripe->syncSubscriptionFromStripe($session->subscription, $request->user());
                    $synced = true;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $synced
            ? redirect()->route('pro.show')->with('success', 'Welcome to XploreSmithers Pro! Your subscription is active.')
            : redirect()->route('pro.show')->with('info', 'Payment received. Your subscription will activate shortly — refresh in a moment.');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('pro.show')->with('error', 'Checkout cancelled, no charge was made.');
    }

    public function portal(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->stripe_customer_id || ! config('services.stripe.enabled')) {
            return redirect()->route('pro.show')
                ->with('error', 'No billing account found to manage yet.');
        }

        try {
            $session = $this->stripe->createBillingPortalSession($user, route('pro.show'));

            return redirect()->away($session->url);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('pro.show')->with('error', 'Could not open the billing portal.');
        }
    }
}
