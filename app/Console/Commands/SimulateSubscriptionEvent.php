<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SimulateSubscriptionEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:simulate
        {event : trial-started|trial-ending|trial-converted|payment-failed|canceled|purchased}
        {--user= : Email of the subscriber to use; defaults to the newest user}
        {--platform=android : ios|android|web}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger a subscription lifecycle event locally so the customer and owner emails can be checked without a real purchase';

    /**
     * Each case performs the same column transition the real platform
     * integrations perform, so the production observer path is what gets
     * exercised — not a shortcut that emails directly.
     */
    public function handle(): int
    {
        $event = (string) $this->argument('event');
        $platform = (string) $this->option('platform');

        $user = $this->resolveUser();

        if ($user === null) {
            $this->error('No user found. Pass --user=email@example.com.');

            return self::FAILURE;
        }

        $owner = setting('owner_notification_email');

        if (blank($owner)) {
            $this->warn('owner_notification_email is not set — only the subscriber will be emailed.');
        }

        match ($event) {
            'trial-started' => $this->trialStarted($user, $platform),
            'trial-ending' => $this->trialEnding($user, $platform),
            'trial-converted' => $this->trialConverted($user, $platform),
            'payment-failed' => $this->paymentFailed($user, $platform),
            'canceled' => $this->canceled($user, $platform),
            'purchased' => $this->purchased($user, $platform),
            default => null,
        };

        if (! in_array($event, ['trial-started', 'trial-ending', 'trial-converted', 'payment-failed', 'canceled', 'purchased'], true)) {
            $this->error("Unknown event [{$event}].");

            return self::FAILURE;
        }

        $this->info("Simulated [{$event}] for {$user->email}.");
        $this->line('Emails are queued — run: php artisan queue:work --stop-when-empty');
        $this->line('Then check /admin/email-logs.');

        return self::SUCCESS;
    }

    private function trialStarted(User $user, string $platform): void
    {
        $this->newTrial($user, $platform);
    }

    private function trialEnding(User $user, string $platform): void
    {
        $subscription = $this->newTrial($user, $platform);
        $subscription->forceFill([
            'trial_ends_at' => now()->addDays(2),
            'expires_at' => now()->addDays(2),
        ])->save();

        $this->call('subscriptions:send-trial-reminders');
    }

    private function trialConverted(User $user, string $platform): void
    {
        $subscription = $this->newTrial($user, $platform);

        // The real conversion signal: the trial flag drops while the row stays
        // active. No status change happens, which is why the observer keys on this.
        $subscription->update([
            'is_trial' => false,
            'expires_at' => now()->addMonth(),
        ]);
    }

    private function paymentFailed(User $user, string $platform): void
    {
        $this->newPaid($user, $platform)->update(['status' => 'in_grace_period']);
    }

    private function canceled(User $user, string $platform): void
    {
        $this->newPaid($user, $platform)->update(['auto_renewing' => false]);
    }

    private function purchased(User $user, string $platform): void
    {
        $this->newPaid($user, $platform);
    }

    private function newTrial(User $user, string $platform): Subscription
    {
        return Subscription::create($this->attributes($user, $platform, [
            'is_trial' => true,
            'trial_ends_at' => now()->addDays(7),
            'expires_at' => now()->addDays(7),
        ]));
    }

    /**
     * An existing paid subscription, created quietly so the setup itself does
     * not send a purchase email before the event under test.
     */
    private function newPaid(User $user, string $platform): Subscription
    {
        $subscription = new Subscription($this->attributes($user, $platform));
        $subscription->saveQuietly();

        return $subscription;
    }

    /**
     * Base attributes for a simulated subscription.
     *
     * Built by hand rather than with a model factory: factories live under
     * composer's autoload-dev, so they don't exist on a production install
     * (`composer install --no-dev`), which is exactly where this command is
     * most useful.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function attributes(User $user, string $platform, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $user->id,
            'platform' => $platform,
            'product_id' => Subscription::OFFLINE_PRODUCT_IDS[0],
            // Prefixed so simulated rows are obvious in the admin panel and
            // can never collide with a real store token.
            'purchase_token' => 'simulated_'.Str::random(24),
            'status' => 'active',
            'is_trial' => false,
            'trial_ends_at' => null,
            'expires_at' => now()->addMonth(),
            'auto_renewing' => true,
            'raw_payload' => ['simulated' => true],
        ], $overrides);
    }

    private function resolveUser(): ?User
    {
        $email = $this->option('user');

        if ($email !== null) {
            return User::where('email', $email)->first();
        }

        return User::query()->latest('id')->first();
    }
}
