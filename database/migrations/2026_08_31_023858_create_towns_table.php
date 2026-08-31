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
        Schema::create('towns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('province')->default('British Columbia');
            $table->string('province_code', 8)->default('BC');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedSmallInteger('radius_km')->default(40);
            $table->unsignedTinyInteger('map_zoom')->default(11);

            $table->string('tagline')->nullable();
            $table->longText('intro')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('hero_image')->nullable();
            $table->string('website_url')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            /**
             * Null means "decide automatically": index only once the town has
             * at least one trail, so empty towns never ship as thin pages.
             */
            $table->boolean('is_indexable')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('towns');
    }
};
