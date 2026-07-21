<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionEventNotifier;
use App\Support\SubscriptionEvent;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class SendTrialEndingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-trial-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email subscribers whose free trial is about to end and convert to a paid subscription';

    /**
     * None of the three stores emit a usable "trial about to end" event — Apple
     * and Google emit nothing at all, and Stripe's trial_will_end only covers
     * its own customers — so the reminder is derived from trial_ends_at here.
     * That keeps one code path for all platforms.
     *
     * Each trial is reminded at most once: the stamp is compared against
     * trial_ends_at so a re-trial later still gets its own reminder, and it is
     * written only after a successful send so a failure retries tomorrow.
     */
    public function handle(SubscriptionEventNotifier $notifier): int
    {
        $leadDays = (int) setting('trial_reminder_days', 3);

        $subscriptions = Subscription::query()
            ->with('user')
            ->where('is_trial', true)
            ->whereIn('status', Subscription::ENTITLED_STATUSES)
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays($leadDays)])
            ->where(function (Builder $query): void {
                $query->whereNull('trial_reminder_sent_at')
                    ->orWhereColumn('trial_reminder_sent_at', '<', 'trial_ends_at');
            })
            ->get()
            // A stamp written during the current reminder window belongs to this
            // trial, so skip it. Comparing against trial_ends_at alone is always
            // true inside the window and would re-send on every daily run; an
            // older stamp means a previous trial and may be reminded again.
            ->filter(fn (Subscription $subscription): bool => $subscription->trial_reminder_sent_at === null
                || $subscription->trial_reminder_sent_at->lte(
                    $subscription->trial_ends_at->copy()->subDays($leadDays)
                ));

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            if ($subscription->user === null) {
                continue;
            }

            try {
                $notifier->dispatch(SubscriptionEvent::TrialEndingSoon, $subscription);
                // Writing the stamp re-enters the observer, but it changes
                // neither status nor is_trial, so no further email is sent.
                $subscription->forceFill(['trial_reminder_sent_at' => now()])->save();
                $sent++;
            } catch (Throwable $e) {
                report($e);
            }
        }

        $this->info("Sent {$sent} trial reminder(s).");

        return self::SUCCESS;
    }
}
