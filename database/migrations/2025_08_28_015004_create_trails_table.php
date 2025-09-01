<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trails', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('location')->nullable();
            $table->decimal('difficulty_level', 2, 1); // 1.0 to 5.0
            $table->decimal('distance_km', 8, 2);
            $table->integer('elevation_gain_m');
            $table->decimal('estimated_time_hours', 4, 2);
            $table->string('trail_type'); // loop, out-and-back, point-to-point
            $table->json('start_coordinates'); // [lat, lng]
            $table->json('end_coordinates')->nullable(); // [lat, lng]
            $table->json('route_coordinates')->nullable(); // Array of [lat, lng] points
            $table->text('gpx_file_path')->nullable();
            $table->string('status')->default('active'); // active, closed, seasonal
            $table->json('best_seasons')->nullable(); // Array of seasons
            $table->text('directions')->nullable();
            $table->text('parking_info')->nullable();
            $table->text('safety_notes')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trails');
    }
};