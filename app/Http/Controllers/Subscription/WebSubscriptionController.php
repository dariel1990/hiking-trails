<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\CheckoutRequest;
use App\Services\StripeSubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class WebSubscriptionController extends Controller
{
    public function __construct(private StripeSubscriptionService $stripe) {}

    public function show(Request $request): View
    {
        return view('subscription.pro', [
            'isPro' => (bool) $request->user()?->hasActiveProEntitlement(),
            'stripeEnabled' => (bool) config('services.stripe.enabled'),
            'priceMonthly' => '4.99',
            'priceAnnual' => '39.99',
            'trialDays' => (int) config('services.stripe.trial_days'),
        ]);
    }

    public function checkout(CheckoutRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasActiveProEntitlement()) {
            return redirect()->route('pro.show')->with('success', 'You already have XploreSmithers Pro.');
        }

        if (! config('services.stripe.enabled')) {
            return redirect()->route('pro.show')
                ->with('error', 'Payments are not configured yet. Please check back soon.');
        }

        try {
            $session = $this->stripe->createCheckoutSession(
                $user,
                $request->string('plan')->value(),
                route('pro.success').'?session_id={CHECKOUT_SESSION_ID}',
                route('pro.cancel'),
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

        if ($sessionId && config('services.stripe.enabled')) {
            try {
                $session = $this->stripe->retrieveCheckoutSession((string) $sessionId);
                if ($session->subscription) {
                    $this->stripe->syncSubscriptionFromStripe($session->subscription, $request->user());
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('pro.show')
            ->with('success', 'Welcome to XploreSmithers Pro! Your subscription is active.');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('pro.show')->with('error', 'Checkout cancelled — no charge was made.');
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
