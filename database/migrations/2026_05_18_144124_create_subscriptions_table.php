<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // An empty, migration-untracked `subscriptions` table was left behind by
        // a prior incomplete attempt at this feature. Dropping the empty leftover
        // brings the table cleanly under migration control.
        Schema::dropIfExists('subscriptions');

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform')->default('android');
            $table->string('product_id');
            // Spec §4 suggests TEXT, but §9 requires a real unique constraint on
            // the purchase token (one token -> one user). MySQL cannot unique-
            // index TEXT without a prefix length; Play tokens fit well within 512.
            $table->string('purchase_token', 512)->unique();
            $table->string('status')->default('expired');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('auto_renewing')->default(false);
            $table->integer('latest_notification_type')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
