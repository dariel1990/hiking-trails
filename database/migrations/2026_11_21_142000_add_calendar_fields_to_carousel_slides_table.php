<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a slide shows on the /events calendar. calendar_title is null until an admin
     * changes it, so the title follows the caption. The dates are separate from starts_at/ends_at, which
     * only say when the slide is in the slideshow (usually before the event itself).
     */
    public function up(): void
    {
        Schema::table('carousel_slides', function (Blueprint $table) {
            $table->boolean('show_on_calendar')->default(false)->after('ends_at');
            $table->string('calendar_title')->nullable()->after('show_on_calendar');
            $table->date('calendar_date')->nullable()->after('calendar_title');
            $table->date('calendar_end_date')->nullable()->after('calendar_date');
            $table->time('calendar_start_time')->nullable()->after('calendar_end_date');
            $table->time('calendar_end_time')->nullable()->after('calendar_start_time');
            $table->string('calendar_location')->nullable()->after('calendar_end_time');
            $table->text('calendar_description')->nullable()->after('calendar_location');
            $table->string('calendar_url', 500)->nullable()->after('calendar_description');

            $table->index(['show_on_calendar', 'calendar_date']);
        });
    }

    public function down(): void
    {
        Schema::table('carousel_slides', function (Blueprint $table) {
            $table->dropIndex(['show_on_calendar', 'calendar_date']);
            $table->dropColumn([
                'show_on_calendar',
                'calendar_title',
                'calendar_date',
                'calendar_end_date',
                'calendar_start_time',
                'calendar_end_time',
                'calendar_location',
                'calendar_description',
                'calendar_url',
            ]);
        });
    }
};
