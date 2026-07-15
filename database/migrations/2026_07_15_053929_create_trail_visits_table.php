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
        Schema::create('trail_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('device_type');
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['trail_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trail_visits');
    }
};
