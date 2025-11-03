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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->string('venue')->nullable();
            $table->string('organizer')->nullable();
            $table->string('category')->nullable();
            $table->string('image_url')->nullable();
            $table->string('external_url')->nullable();
            $table->string('source_id')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('event_date');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};