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
        Schema::table('tour_stops', function (Blueprint $table) {
            $table->string('icon', 16)->nullable()->after('stop_label');
            $table->string('icon_image')->nullable()->after('icon');
            $table->text('description')->nullable()->after('icon_image');
            $table->string('image')->nullable()->after('description');
            $table->boolean('is_roaming')->default(false)->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_stops', function (Blueprint $table) {
            $table->dropColumn(['icon', 'icon_image', 'description', 'image', 'is_roaming']);
        });
    }
};
