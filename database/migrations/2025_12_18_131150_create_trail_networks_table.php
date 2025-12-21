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
        Schema::create('trail_networks', function (Blueprint $table) {
            $table->id();
            $table->string('network_name');
            $table->string('slug')->unique();
            $table->enum('type', ['nordic_skiing', 'downhill_skiing', 'hiking', 'mountain_biking'])->default('hiking');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('address')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('is_always_visible')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trail_networks');
    }
};
