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
        Schema::create('trail_network_sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_network_id')
                ->constrained('trail_networks')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->string('welcome_message')->nullable();
            $table->string('banner_text')->nullable();
            $table->string('cta_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['trail_network_id', 'is_active', 'sort_order'], 'tns_network_active_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trail_network_sponsors');
    }
};
