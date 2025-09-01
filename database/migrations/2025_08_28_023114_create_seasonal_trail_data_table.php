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
        Schema::create('seasonal_trail_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained()->onDelete('cascade');
            $table->enum('season', ['spring', 'summer', 'fall', 'winter']);
            $table->json('trail_conditions')->nullable(); // snow, mud, ice, etc.
            $table->text('seasonal_notes')->nullable();
            $table->json('accessibility_changes')->nullable(); // road closures, etc.
            $table->json('seasonal_features')->nullable(); // waterfalls, wildlife, etc.
            $table->boolean('recommended')->default(true);
            $table->timestamps();
            
            $table->unique(['trail_id', 'season']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasonal_trail_data');
    }
};
