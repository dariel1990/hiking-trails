<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trail_feature_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_feature_id')->constrained()->onDelete('cascade');
            $table->foreignId('trail_media_id')->constrained('trail_media')->onDelete('cascade');
            
            // Is this the primary/featured media for this feature?
            $table->boolean('is_primary')->default(false);
            
            // Order of media for this feature
            $table->integer('sort_order')->default(0);
            
            // Caption override specific to this feature (optional)
            $table->text('caption_override')->nullable();
            
            $table->timestamps();
            
            // Ensure each media item is linked to a feature only once
            $table->unique(['trail_feature_id', 'trail_media_id']);
            
            // Indexes for performance
            $table->index('is_primary');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trail_feature_media');
    }
};