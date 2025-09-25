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
        Schema::create('trail_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // viewpoint, waterfall, bridge, summit, wildlife, lake, etc.
            $table->json('coordinates'); // [lat, lng]
            $table->string('icon')->nullable(); // emoji or icon class
            $table->string('color')->default('#10B981'); // hex color for marker
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trail_highlights');
    }
};
