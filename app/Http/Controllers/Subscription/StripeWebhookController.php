<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StripeSubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller
{
    public function __construct(private StripeSubscriptionService $stripe) {}

    public function handle(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) $secret,
            );
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            return response('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        try {
            match ($event->type) {
                'checkout.session.completed' => $this->onCheckoutCompleted($event->data->object),
                'customer.subscription.created',
                'customer.subscription.updated',
                'customer.subscription.deleted' => $this->onSubscriptionChange($event->data->object),
                'customer.subscription.trial_will_end' => $this->onTrialWillEnd($event->data->object),
                'invoice.payment_failed' => $this->onInvoicePaymentFailed($event->data->object),
                default => null,
            };
        } catch (Throwable $e) {
            report($e);

            // Acknowledge so Stripe doesn't hammer retries; we log for follow-up.
            return response('ok', Response::HTTP_OK);
        }

        return response('ok', Response::HTTP_OK);
    }

    private function onCheckoutCompleted(object $session): void
    {
        if (empty($session->subscription)) {
            return;
        }

        $user = $this->resolveUser(
            $session->metadata->user_id ?? ($session->client_reference_id ?? null),
            $session->customer ?? null,
        );

        if (! $user) {
            return;
        }

        $sub = $this->stripe->retrieveSubscription((string) $session->subscription);
        $this->stripe->syncSubscriptionFromStripe($sub, $user);
    }

    private function onSubscriptionChange(StripeSubscription $sub): void
    {
        $user = $this->resolveUser($sub->metadata->user_id ?? null, $sub->customer ?? null);

        if ($user) {
            $this->stripe->syncSubscriptionFromStripe($sub, $user);
        }
    }

    private function onTrialWillEnd(StripeSubscription $sub): void
    {
        $user = $this->resolveUser($sub->metadata->user_id ?? null, $sub->customer ?? null);

        if ($user) {
            $this->stripe->handleTrialWillEnd($sub, $user);
        }
    }

    /**
     * A failed charge. Stripe usually also emits customer.subscription.updated
     * with past_due, but not always immediately for the first charge after a
     * trial — handling the invoice event closes that gap. Re-syncing is safe:
     * the observer only emails on a real status change, so if the subscription
     * event already moved the row, this one is silent.
     */
    private function onInvoicePaymentFailed(object $invoice): void
    {
        if (empty($invoice->subscription)) {
            return;
        }

        $user = $this->resolveUser(null, $invoice->customer ?? null);

        if (! $user) {
            return;
        }

        $sub = $this->stripe->retrieveSubscription((string) $invoice->subscription);
        $this->stripe->syncSubscriptionFromStripe($sub, $user);
    }

    private function resolveUser(int|string|null $userId, int|string|null $customerId): ?User
    {
        if ($userId && $user = User::find($userId)) {
            return $user;
        }

        if ($customerId) {
            return User::where('stripe_customer_id', (string) $customerId)->first();
        }

        return null;
    }
}
