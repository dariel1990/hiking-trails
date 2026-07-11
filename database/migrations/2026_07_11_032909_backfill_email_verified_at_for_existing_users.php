<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Grandfather accounts created before email verification was enforced:
     * they registered without a verification flow, so treat them as verified
     * rather than locking them out of verified-only pages.
     */
    public function up(): void
    {
        User::query()->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Irreversible data backfill — intentionally a no-op.
    }
};
