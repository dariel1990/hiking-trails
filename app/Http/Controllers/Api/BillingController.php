<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use App\Services\GooglePlaySubscriptionVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class BillingController extends Controller
{
    public function verifyPurchase(Request $request, GooglePlaySubscriptionVerifier $verifier): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'in:android'],
            'productId' => ['required', 'string', 'in:'.implode(',', Subscription::OFFLINE_PRODUCT_IDS)],
            'purchaseToken' => ['required', 'string'],
        ]);

        $userId = $request->user()->id;
        $productId = $validated['productId'];
        $purchaseToken = $validated['purchaseToken'];

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

        if ($isActive && $subscription->wasRecentlyCreated) {
            User::where('is_admin', true)->each(function (User $admin) use ($subscription): void {
                try {
                    $admin->notify(new NewSubscriptionNotification($subscription));
                } catch (Throwable $e) {
                    report($e);
                }
            });
        }

        if ($isActive && $acknowledgementState === 'ACKNOWLEDGEMENT_STATE_PENDING') {
            try {
                $verifier->acknowledge($productId, $purchaseToken);
            } catch (Throwable $e) {
                Log::warning('Google Play acknowledge failed', ['error' => $e->getMessage()]);
            }
        }

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
