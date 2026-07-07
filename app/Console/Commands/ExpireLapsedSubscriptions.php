<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

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
    protected $description = 'Mark entitled subscriptions whose expiry date has passed as expired';

    /**
     * Flip any subscription that still looks entitled (active / in grace period)
     * but whose expires_at is in the past to the expired status. This keeps the
     * stored status column in sync with Subscription::scopeEntitled() so the admin
     * UI and reporting no longer show a stale "Active" badge on lapsed rows.
     *
     * A late renewal webhook from Stripe or Google Play will re-sync the row back
     * to active via updateOrCreate, so this reconciliation is safely reversible.
     */
    public function handle(): int
    {
        $expired = Subscription::query()
            ->whereIn('status', Subscription::ENTITLED_STATUSES)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Marked {$expired} lapsed subscription(s) as expired.");

        return self::SUCCESS;
    }
}
