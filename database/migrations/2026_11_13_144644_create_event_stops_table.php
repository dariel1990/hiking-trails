<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_roaming')->default(false);
            $table->unsignedInteger('stop_order')->default(0);
            $table->string('stop_label');
            $table->string('time_label', 100)->nullable();
            $table->string('notes', 500)->nullable();
            $table->string('icon', 16)->nullable();
            $table->string('icon_image')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'stop_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_stops');
    }
};
