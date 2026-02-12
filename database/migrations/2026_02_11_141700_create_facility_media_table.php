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
        Schema::create('facility_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->onDelete('cascade');
            $table->string('media_type'); // 'photo' or 'video_url'
            $table->string('file_path')->nullable(); // For uploaded photos
            $table->string('url')->nullable(); // For video URLs or photo URLs
            $table->string('caption')->nullable();
            $table->string('video_provider')->nullable(); // 'youtube' or 'vimeo' for videos
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['facility_id', 'media_type']);
            $table->index(['facility_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_media');
    }
};
