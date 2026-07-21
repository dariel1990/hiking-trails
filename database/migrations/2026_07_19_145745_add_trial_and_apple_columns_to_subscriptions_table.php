<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds free-trial tracking and the Apple linkage key.
     *
     * Trials previously had no representation: Stripe's "trialing" state was
     * collapsed to "active", so a trial start looked identical to a purchase and
     * a trial converting to paid produced no column change to react to.
     *
     * `trial_reminder_sent_at` is deliberately separate from
     * `expiry_reminder_sent_at`: both reminder flows previously shared the
     * latter, so sending one silently suppressed the other.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->boolean('is_trial')->default(false)->after('status');
            $table->timestamp('trial_ends_at')->nullable()->after('is_trial');
            $table->timestamp('trial_reminder_sent_at')->nullable()->after('trial_ends_at');
            $table->string('original_transaction_id')->nullable()->after('purchase_token');

            $table->index('trial_ends_at');
            $table->index('original_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropIndex(['trial_ends_at']);
            $table->dropIndex(['original_transaction_id']);

            $table->dropColumn([
                'is_trial',
                'trial_ends_at',
                'trial_reminder_sent_at',
                'original_transaction_id',
            ]);
        });
    }
};
