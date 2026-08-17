<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateReviewAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:review-account
        {--email=appreview@xploresmithers.com : Demo account email for App Store / Play review}
        {--password= : Password to set (a strong one is generated when omitted)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or reset a working email/password demo account for App Store / Play Store reviewers';

    /**
     * Idempotent: re-running updates the same account and resets its password,
     * so a reviewer credential can always be regenerated on demand.
     */
    public function handle(): int
    {
        $email = (string) $this->option('email');
        $password = (string) ($this->option('password') ?: Str::password(16));

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'App Reviewer',
                'first_name' => 'App',
                'last_name' => 'Reviewer',
                'password' => $password,
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->info($user->wasRecentlyCreated ? 'Review account created.' : 'Review account updated (password reset).');
        $this->newLine();
        $this->line("  Email:    {$email}");
        $this->line("  Password: {$password}");
        $this->newLine();
        $this->warn('Copy the password now — it is hashed in the database and cannot be shown again.');

        return self::SUCCESS;
    }
}
