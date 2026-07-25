<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\AppStoreSubscriptionVerifier;
use App\Services\GooglePlaySubscriptionSyncService;
use App\Services\GooglePlaySubscriptionVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class BillingController extends Controller
{
    public function verifyPurchase(
        Request $request,
        GooglePlaySubscriptionVerifier $verifier,
        AppStoreSubscriptionVerifier $appStoreVerifier,
        GooglePlaySubscriptionSyncService $sync,
    ): JsonResponse {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'in:android,ios'],
            'productId' => ['required', 'string', 'in:'.implode(',', Subscription::OFFLINE_PRODUCT_IDS)],
            'purchaseToken' => ['required', 'string'],
        ]);

        $userId = $request->user()->id;
        $productId = $validated['productId'];
        $purchaseToken = $validated['purchaseToken'];

        if ($validated['platform'] === 'ios') {
            return $this->verifyIosPurchase($appStoreVerifier, $userId, $productId, $purchaseToken);
        }

        $existing = Subscription::query()->where('purchase_token', $purchaseToken)->first();

        if ($existing && $existing->user_id !== $userId) {
            return response()->json(
                ['message' => 'Purchase token already bound to another account.'],
                Response::HTTP_CONFLICT,
            );
        }

        try {
            $payload = $verifier->getSubscription($purchaseToken);
        } catch (Throwable $e) {
            Log::warning('Google Play verifyPurchase failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Could not verify purchase'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $attributes = $sync->attributesFromPayload($payload);
        $acknowledgementState = (string) ($payload['acknowledgementState'] ?? '');

        $subscription = Subscription::updateOrCreate(
            ['purchase_token' => $purchaseToken],
            array_merge($attributes, [
                'user_id' => $userId,
                'platform' => 'android',
                'product_id' => $productId,
            ]),
        );

        $expiresAt = $subscription->expires_at;
        $isActive = in_array($attributes['status'], Subscription::ENTITLED_STATUSES, true)
            && ($expiresAt === null || $expiresAt->isFuture());

        if ($isActive && $acknowledgementState === 'ACKNOWLEDGEMENT_STATE_PENDING') {
            try {
                $verifier->acknowledge($productId, $purchaseToken);
            } catch (Throwable $e) {
                Log::warning('Google Play acknowledge failed', ['error' => $e->getMessage()]);
            }
        }

        return $this->entitlementResponse($subscription, $isActive);
    }

    /**
     * iOS mirror of the Android flow. `purchaseToken` is the StoreKit 2 signed
     * transaction (JWS); the verifier resolves it against Apple's App Store
     * Server API. The stable originalTransactionId — not the per-launch JWS —
     * is what identifies the subscription row, so the account-conflict check
     * runs after verification.
     */
    private function verifyIosPurchase(
        AppStoreSubscriptionVerifier $verifier,
        int $userId,
        string $productId,
        string $signedTransaction,
    ): JsonResponse {
        try {
            $result = $verifier->verify($signedTransaction);
        } catch (Throwable $e) {
            Log::warning('App Store verifyPurchase failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Could not verify purchase'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $originalTransactionId = $result['originalTransactionId'];

        $existing = Subscription::query()->where('purchase_token', $originalTransactionId)->first();

        if ($existing && $existing->user_id !== $userId) {
            return response()->json(
                ['message' => 'Purchase token already bound to another account.'],
                Response::HTTP_CONFLICT,
            );
        }

        // Prefer the product Apple reports; fall back to the client's claim.
        $verifiedProductId = $result['productId'];
        if (! in_array($verifiedProductId, Subscription::OFFLINE_PRODUCT_IDS, true)) {
            $verifiedProductId = $productId;
        }

        $subscription = Subscription::updateOrCreate(
            ['purchase_token' => $originalTransactionId],
            [
                'user_id' => $userId,
                'platform' => 'ios',
                'product_id' => $verifiedProductId,
                'original_transaction_id' => $originalTransactionId,
                'status' => $result['status'],
                'is_trial' => $result['isTrial'],
                'expires_at' => $result['expiresAt'],
                'auto_renewing' => $result['autoRenewing'],
                'raw_payload' => $result['raw'],
            ],
        );

        // No acknowledge step on iOS — StoreKit transactions are finished by
        // the app itself (completePurchase).
        return $this->entitlementResponse($subscription, $subscription->isEntitled());
    }

    private function entitlementResponse(Subscription $subscription, bool $isActive): JsonResponse
    {
        return response()->json([
            'entitlement' => [
                'active' => $isActive,
                'productId' => $subscription->product_id,
                'status' => $subscription->status,
                'expiresAt' => $subscription->expires_at?->toIso8601String(),
                'inGracePeriod' => $subscription->status === 'in_grace_period',
            ],
        ]);
    }

    /**
     * Google Real-Time Developer Notifications webhook (Pub/Sub push).
     *
     * Public route (no Sanctum, and exempt from VerifyAppKey — Pub/Sub sends no
     * X-App-Key). Genuineness is enforced by a shared secret on the push
     * subscription URL (?token=).
     *
     * The notification body is treated as a trigger only, never as truth: we
     * re-fetch authoritative state from Google Play by purchase token, exactly
     * as the hourly reconcile does, so a forged push can at worst force a
     * re-sync to the real state. Persisting through updateOrCreate lets the
     * SubscriptionObserver email on genuine transitions.
     *
     * Always 204 after the token check so Pub/Sub stops retrying.
     */
    public function rtdn(
        Request $request,
        GooglePlaySubscriptionVerifier $verifier,
        GooglePlaySubscriptionSyncService $sync,
    ): Response {
        $expected = config('services.google_play.rtdn_token');

        if (! empty($expected) && ! hash_equals((string) $expected, (string) $request->query('token'))) {
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->handleRtdn($request, $verifier, $sync);
        } catch (Throwable $e) {
            // Never surface a 5xx to Pub/Sub — that just triggers retries. Log
            // and move on; the hourly reconcile is the backstop.
            report($e);
        }

        return response()->noContent();
    }

    private function handleRtdn(
        Request $request,
        GooglePlaySubscriptionVerifier $verifier,
        GooglePlaySubscriptionSyncService $sync,
    ): void {
        $raw = base64_decode((string) $request->input('message.data'), true);
        $notification = $raw ? json_decode($raw, true) : null;

        if (! is_array($notification)) {
            return;
        }

        // Ignore Play's test pings and one-time-product events.
        $subscriptionNotification = $notification['subscriptionNotification'] ?? null;
        if (! is_array($subscriptionNotification)) {
            return;
        }

        $purchaseToken = (string) ($subscriptionNotification['purchaseToken'] ?? '');
        $notificationType = $subscriptionNotification['notificationType'] ?? null;
        if ($purchaseToken === '') {
            return;
        }

        // The token is only linkable once verify-purchase has bound it to a
        // user. If we've never seen it, there's nothing to update — the app
        // will verify on next launch.
        $existing = Subscription::query()->where('purchase_token', $purchaseToken)->first();
        if ($existing === null) {
            Log::info('RTDN for an unknown purchase token; skipping.', [
                'notificationType' => $notificationType,
            ]);

            return;
        }

        $payload = $verifier->getSubscription($purchaseToken);

        $existing->fill(array_merge(
            $sync->attributesFromPayload($payload),
            ['latest_notification_type' => is_int($notificationType) ? $notificationType : null],
        ))->save();
    }
}
