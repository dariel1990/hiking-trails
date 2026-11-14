<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photo and video gallery for events (photos uploaded to storage, videos as
 * YouTube/Vimeo links), shown on the event page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('media_type', 20);
            $table->string('file_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('video_provider', 20)->nullable();
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['event_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_media');
    }
};
