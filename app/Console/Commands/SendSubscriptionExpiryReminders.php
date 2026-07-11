<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringSoonNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class SendSubscriptionExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-expiry-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email subscribers whose non-renewing Pro subscription expires within 7 days';

    /**
     * Auto-renewing subscriptions are skipped (they will simply renew), and each
     * subscription is reminded at most once per billing period: the stamp check
     * against expires_at allows a fresh reminder if a renewal later pushes the
     * expiry date forward. The stamp is written only after a successful send, so
     * a failed send is retried on the next daily run.
     */
    public function handle(): int
    {
        $subscriptions = Subscription::query()
            ->with('user')
            ->whereIn('status', Subscription::ENTITLED_STATUSES)
            ->where('auto_renewing', false)
            ->whereBetween('expires_at', [now(), now()->addDays(7)])
            ->where(function (Builder $query): void {
                $query->whereNull('expiry_reminder_sent_at')
                    ->orWhereColumn('expiry_reminder_sent_at', '<', 'expires_at');
            })
            ->get()
            ->filter(fn (Subscription $subscription): bool => $subscription->expiry_reminder_sent_at === null
                || $subscription->expiry_reminder_sent_at->lt($subscription->expires_at->copy()->subDays(8)));

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            if ($subscription->user === null) {
                continue;
            }

            try {
                $subscription->user->notify(new SubscriptionExpiringSoonNotification($subscription));
                $subscription->forceFill(['expiry_reminder_sent_at' => now()])->save();
                $sent++;
            } catch (Throwable $e) {
                report($e);
            }
        }

        $this->info("Sent {$sent} expiry reminder(s).");

        return self::SUCCESS;
    }
}
