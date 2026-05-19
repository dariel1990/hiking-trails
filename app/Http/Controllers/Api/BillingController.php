<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BillingController extends Controller
{
    /**
     * Phase B (deferred): verify a Google Play purchase server-side.
     *
     * Wiring and the SKU allowlist are in place; the Google Play Developer
     * API call (purchases.subscriptionsv2.get) is not implemented yet because
     * no service-account credentials exist (spec §5, §7). Until then this
     * returns 503 so the Android app can detect "billing not yet live"
     * rather than mistaking it for a real verification failure (which is 422).
     *
     * TODO(Phase B): call androidpublisher subscriptionsv2.get with the
     * service-account JWT, map subscriptionState -> Subscription status,
     * upsert the subscriptions row keyed by purchase_token bound to
     * $request->user(), acknowledge if needed, return the entitlement payload.
     */
    public function verifyPurchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'in:android'],
            'productId' => ['required', 'string', 'in:'.implode(',', Subscription::OFFLINE_PRODUCT_IDS)],
            'purchaseToken' => ['required', 'string'],
        ]);

        return response()->json([
            'message' => 'Purchase verification is not yet available.',
            'reason' => 'phase_b_not_configured',
        ], Response::HTTP_SERVICE_UNAVAILABLE);
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
