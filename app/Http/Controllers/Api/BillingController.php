<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\AppStoreSubscriptionVerifier;
use App\Services\GooglePlaySubscriptionVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class BillingController extends Controller
{
    public function verifyPurchase(
        Request $request,
        GooglePlaySubscriptionVerifier $verifier,
        AppStoreSubscriptionVerifier $appStoreVerifier,
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

        $state = (string) ($payload['subscriptionState'] ?? '');
        $status = Subscription::GOOGLE_STATE_MAP[$state] ?? 'expired';

        $expiryRaw = $payload['lineItems'][0]['expiryTime'] ?? null;
        $expiresAt = $expiryRaw ? Carbon::parse($expiryRaw) : null;
        $autoRenewing = (bool) ($payload['lineItems'][0]['autoRenewingPlan']['autoRenewEnabled'] ?? false);
        $acknowledgementState = (string) ($payload['acknowledgementState'] ?? '');

        $subscription = Subscription::updateOrCreate(
            ['purchase_token' => $purchaseToken],
            [
                'user_id' => $userId,
                'platform' => 'android',
                'product_id' => $productId,
                'status' => $status,
                'expires_at' => $expiresAt,
                'auto_renewing' => $autoRenewing,
                'raw_payload' => $payload,
            ],
        );

        $isActive = in_array($status, Subscription::ENTITLED_STATUSES, true)
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
     * Phase B (deferred): Google Real-Time Developer Notifications webhook.
     *
     * Public route (no Sanctum). Genuineness is enforced via a shared secret
     * configured on the Pub/Sub push subscription (?token=). The OIDC-JWT
     * verification path and the Play re-fetch/self-heal logic are TODO until
     * Phase B (spec §8). We still ack with 204 so Pub/Sub stops retrying.
     *
     * TODO(Phase B): decode message.data (base64) -> DeveloperNotification,
     * dispatch a queued job that re-fetches the subscription by purchaseToken
     * and re-maps status onto the subscriptions row.
     */
    public function rtdn(Request $request): Response
    {
        $expected = config('services.google_play.rtdn_token');

        if (! empty($expected) && ! hash_equals((string) $expected, (string) $request->query('token'))) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return response()->noContent();
    }
}
