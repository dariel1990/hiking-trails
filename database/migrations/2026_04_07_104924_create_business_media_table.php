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
        Schema::create('business_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('media_type'); // 'photo' or 'video_url'
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->string('caption')->nullable();
            $table->string('video_provider')->nullable(); // 'youtube' or 'vimeo'
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['business_id', 'media_type']);
            $table->index(['business_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_media');
    }
};
