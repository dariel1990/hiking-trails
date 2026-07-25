<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Carbon;

/**
 * Turns a Google Play subscriptionsv2 payload into subscription-row attributes.
 *
 * The mapping used to be copied in three places (verify-purchase, the hourly
 * expire-lapsed reconcile, and the RTDN webhook). Centralising it means trial
 * detection and status mapping are defined once.
 */
class GooglePlaySubscriptionSyncService
{
    /**
     * @param  array<string, mixed>  $payload  A subscriptionsv2.get response.
     * @return array<string, mixed> Attributes for Subscription::updateOrCreate.
     */
    public function attributesFromPayload(array $payload): array
    {
        $state = (string) ($payload['subscriptionState'] ?? '');
        $line = (array) ($payload['lineItems'][0] ?? []);
        $expiryRaw = $line['expiryTime'] ?? null;

        return [
            'status' => Subscription::GOOGLE_STATE_MAP[$state] ?? 'expired',
            'expires_at' => $expiryRaw ? Carbon::parse($expiryRaw) : null,
            'auto_renewing' => (bool) ($line['autoRenewingPlan']['autoRenewEnabled'] ?? false),
            'is_trial' => $this->isTrial($line),
            'raw_payload' => $payload,
        ];
    }

    /**
     * Play has no "is trial" flag. A free trial is an *offer* on the line item,
     * so the current offer id is matched against the trial offer ids configured
     * in Play Console. An explicit allowlist beats guessing from price.
     *
     * @param  array<string, mixed>  $line
     */
    private function isTrial(array $line): bool
    {
        $offerId = (string) ($line['offerDetails']['offerId'] ?? '');

        if ($offerId === '') {
            return false;
        }

        $trialOfferIds = array_filter(array_map(
            'trim',
            explode(',', (string) config('services.google_play.trial_offer_ids', '')),
        ));

        return in_array($offerId, $trialOfferIds, true);
    }
}
