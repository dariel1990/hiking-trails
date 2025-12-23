<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('network_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_network_id')->constrained('trail_networks')->onDelete('cascade');
            $table->enum('facility_type', [
                'parking',
                'toilets',
                'emergency_kit',
                'lodge',
                'viewpoint',
                'info',
                'picnic',
                'water',
                'shelter'
            ]);
            $table->string('name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('description')->nullable();
            $table->string('icon', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('network_facilities');
    }
};