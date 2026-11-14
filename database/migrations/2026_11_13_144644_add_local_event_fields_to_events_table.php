<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Events become their own module (managed in the admin, shown on /local-events
 * with a map), so the events table gains the page and map fields that tours
 * have. Manually added events have no scraper source_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->foreignId('town_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('tagline')->nullable()->after('slug');
            $table->string('cover_image')->nullable()->after('image_url');
            $table->string('video_url', 500)->nullable()->after('cover_image');
            $table->string('theme')->default('standard')->after('category');
            $table->string('icon', 16)->nullable()->after('theme');
            $table->string('icon_image')->nullable()->after('icon');
            $table->json('route_coordinates')->nullable()->after('icon_image');
            $table->decimal('route_distance_km', 8, 2)->nullable()->after('route_coordinates');
            $table->boolean('is_featured')->default(false)->after('is_active');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('source_id')->nullable()->change();
        });

        $this->backfillSlugs();

        Schema::table('events', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropConstrainedForeignId('town_id');
            $table->dropColumn([
                'slug',
                'tagline',
                'cover_image',
                'video_url',
                'theme',
                'icon',
                'icon_image',
                'route_coordinates',
                'route_distance_km',
                'is_featured',
            ]);
        });
    }

    private function backfillSlugs(): void
    {
        $taken = [];

        DB::table('events')->orderBy('id')->select(['id', 'title'])->chunk(200, function ($events) use (&$taken) {
            foreach ($events as $event) {
                $base = Str::slug($event->title) ?: 'event';
                $slug = isset($taken[$base]) ? $base.'-'.$event->id : $base;
                $taken[$slug] = true;

                DB::table('events')->where('id', $event->id)->update(['slug' => $slug]);
            }
        });
    }
};
