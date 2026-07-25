<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AppStoreSubscriptionVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * App Store Server Notifications (V2) webhook.
 *
 * Apple pushes a `signedPayload` (JWS) here on every subscription lifecycle
 * change — renewal, cancellation, billing failure, trial conversion. Like the
 * Google RTDN webhook, the push is only a trigger: we read the originalTransactionId
 * out of it, then re-fetch authoritative state from Apple's App Store Server API,
 * so a forged push can at worst force a re-sync to the true state. Persisting
 * through updateOrCreate lets the SubscriptionObserver email on real transitions.
 *
 * Public route, exempt from VerifyAppKey (Apple sends no X-App-Key). Always
 * returns 200 after handling — Apple retries for up to three days on any non-2xx.
 */
class AppleNotificationController extends Controller
{
    public function handle(Request $request, AppStoreSubscriptionVerifier $verifier): Response
    {
        try {
            $this->process($request, $verifier);
        } catch (Throwable $e) {
            // Swallow — a 5xx just makes Apple retry. The subscription still
            // self-heals on the next app launch (verify-purchase).
            report($e);
        }

        return response('', Response::HTTP_OK);
    }

    private function process(Request $request, AppStoreSubscriptionVerifier $verifier): void
    {
        $signedPayload = (string) $request->input('signedPayload');

        if ($signedPayload === '') {
            return;
        }

        $notification = $verifier->decodeNotification($signedPayload);
        $originalTransactionId = $notification['originalTransactionId'];

        $user = $this->resolveUser($originalTransactionId, $notification['appAccountToken']);

        if ($user === null) {
            // No verify-purchase has bound this subscription yet, and no
            // appAccountToken match. Nothing to update — the app links it on
            // next launch.
            Log::info('Apple notification for an unlinkable subscription; skipping.', [
                'notificationType' => $notification['notificationType'],
            ]);

            return;
        }

        $state = $verifier->statusForOriginalTransactionId($originalTransactionId);

        Subscription::updateOrCreate(
            ['purchase_token' => $originalTransactionId],
            [
                'user_id' => $user->id,
                'platform' => 'ios',
                'product_id' => $state['productId'] ?? Subscription::OFFLINE_PRODUCT_IDS[0],
                'original_transaction_id' => $originalTransactionId,
                'status' => $state['status'],
                'is_trial' => $state['isTrial'],
                'expires_at' => $state['expiresAt'],
                'auto_renewing' => $state['autoRenewing'],
                'raw_payload' => $state['raw'],
            ],
        );
    }

    /**
     * Prefer the existing row (bound by a prior verify-purchase); fall back to
     * the appAccountToken the app set on the purchase.
     */
    private function resolveUser(string $originalTransactionId, ?string $appAccountToken): ?User
    {
        $existing = Subscription::query()
            ->where('purchase_token', $originalTransactionId)
            ->orWhere('original_transaction_id', $originalTransactionId)
            ->first();

        if ($existing?->user !== null) {
            return $existing->user;
        }

        if ($appAccountToken !== null && $appAccountToken !== '') {
            return User::query()->where('app_account_token', $appAccountToken)->first();
        }

        return null;
    }
}
