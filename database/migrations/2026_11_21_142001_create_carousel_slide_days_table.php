<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional per-day calendar schedule for a carousel slide (any dates, each with its
     * own hours), like event_days. The slide's calendar_* columns summarise the first/last day.
     */
    public function up(): void
    {
        Schema::create('carousel_slide_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carousel_slide_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('note', 100)->nullable();
            $table->timestamps();

            $table->index(['carousel_slide_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carousel_slide_days');
    }
};
