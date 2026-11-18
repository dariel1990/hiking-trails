<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional per-day schedule for an event (any dates, each with its own hours).
     * The event's own date/time columns stay as a summary of the first/last day.
     */
    public function up(): void
    {
        Schema::create('event_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('note', 100)->nullable();
            $table->timestamps();

            $table->index(['event_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_days');
    }
};
