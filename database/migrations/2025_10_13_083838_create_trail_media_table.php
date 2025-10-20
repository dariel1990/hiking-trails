<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trail_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained()->onDelete('cascade');
            $table->enum('media_type', ['photo', 'video', 'video_url']);
            
            // File info
            $table->string('filename');
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('thumbnail_path')->nullable();
            
            // Video specific
            $table->text('video_url')->nullable();
            $table->enum('video_provider', ['local', 'youtube', 'vimeo', 'other'])->nullable();
            $table->integer('duration')->nullable(); // seconds
            
            // Common
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->json('coordinates')->nullable();
            $table->text('caption')->nullable();
            $table->text('description')->nullable();
            
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('taken_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['trail_id', 'media_type']);
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    public function down()
    {
        Schema::dropIfExists('trail_media');
    }
};