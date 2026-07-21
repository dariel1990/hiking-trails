<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Apple echoes the `appAccountToken` set at purchase time back in every App
     * Store Server Notification, which is how a notification is matched to a
     * local user. Without it the only linkage is the original transaction id,
     * which requires the app to have posted its purchase first.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('app_account_token')->nullable()->unique()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['app_account_token']);
            $table->dropColumn('app_account_token');
        });
    }
};
