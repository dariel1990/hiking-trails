<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When an event (or a slide's calendar entry) was switched off automatically after
     * its last day. Each row is switched off only once, so an admin who switches it back
     * on is not overruled the next night. Cleared when the dates move into the future.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('auto_deactivated_at')->nullable()->after('is_featured');
        });

        Schema::table('carousel_slides', function (Blueprint $table) {
            $table->timestamp('calendar_ended_at')->nullable()->after('calendar_url');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('auto_deactivated_at');
        });

        Schema::table('carousel_slides', function (Blueprint $table) {
            $table->dropColumn('calendar_ended_at');
        });
    }
};
