<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\GooglePlaySubscriptionVerifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class ExpireLapsedSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:expire-lapsed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile entitled subscriptions whose expiry date has passed (re-checking Google Play first)';

    /**
     * Flip any subscription that still looks entitled (active / in grace period)
     * but whose expires_at is in the past to the expired status, so the stored
     * status stays in sync with Subscription::scopeEntitled().
     *
     * Android rows are re-verified against Google Play first: RTDN is not wired
     * up yet, so a renewal (e.g. a trial converting to paid) only reaches the
     * database when the app next calls /api/billing/verify. Without this
     * re-check, a renewed subscriber whose app hasn't synced would be flagged
     * expired and emailed "your subscription has ended" in error.
     *
     * Auto-renewing rows that cannot be re-verified get a 24h buffer for the
     * store's renewal signal (Stripe webhook / app sync) to arrive. A genuinely
     * failed payment shows up as a grace-period status long before that.
     */
    public function handle(): int
    {
        $expired = 0;
        $refreshed = 0;

        Subscription::query()
            ->whereIn('status', Subscription::ENTITLED_STATUSES)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->with('user')
            ->chunkById(100, function ($subscriptions) use (&$expired, &$refreshed): void {
                foreach ($subscriptions as $subscription) {
                    if ($this->refreshFromGooglePlay($subscription)) {
                        $refreshed++;
                        if ($subscription->status === 'expired') {
                            $expired++;
                        }

                        continue;
                    }

                    if ($subscription->auto_renewing && $subscription->expires_at->gt(now()->subDay())) {
                        continue;
                    }

                    $subscription->status = 'expired';
                    $subscription->save();
                    $expired++;
                }
            });

        $this->info("Re-synced {$refreshed} subscription(s) from Google Play; marked {$expired} as expired.");

        return self::SUCCESS;
    }

    /**
     * Pull the current state for a lapsed Android subscription straight from
     * Google Play and save it. Returns true when the row was refreshed (the
     * SubscriptionObserver then emails only on real status transitions);
     * false when this row isn't eligible or Google could not be reached.
     */
    private function refreshFromGooglePlay(Subscription $subscription): bool
    {
        if ($subscription->platform !== 'android'
            || ! $subscription->auto_renewing
            || blank($subscription->purchase_token)) {
            return false;
        }

        try {
            $payload = app(GooglePlaySubscriptionVerifier::class)
                ->getSubscription($subscription->purchase_token);

            $state = (string) ($payload['subscriptionState'] ?? '');
            $expiryRaw = $payload['lineItems'][0]['expiryTime'] ?? null;

            $subscription->fill([
                'status' => Subscription::GOOGLE_STATE_MAP[$state] ?? 'expired',
                'expires_at' => $expiryRaw ? Carbon::parse($expiryRaw) : null,
                'auto_renewing' => (bool) ($payload['lineItems'][0]['autoRenewingPlan']['autoRenewEnabled'] ?? false),
                'raw_payload' => $payload,
            ])->save();

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
