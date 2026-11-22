<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Category of a slide's /events calendar entry: shown as a badge on its card and
     * popup, and used by the calendar's category filter like event categories.
     */
    public function up(): void
    {
        Schema::table('carousel_slides', function (Blueprint $table) {
            $table->string('calendar_category', 100)->nullable()->after('calendar_title');
        });
    }

    public function down(): void
    {
        Schema::table('carousel_slides', function (Blueprint $table) {
            $table->dropColumn('calendar_category');
        });
    }
};
