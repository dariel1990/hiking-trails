<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Add a URL slug to trails and backfill it from each trail's name.
     *
     * Slugs are only used by browser-facing trail URLs; the apps and the API keep
     * addressing trails by numeric ID. An all-digit slug would be mistaken for an
     * ID, so those get a "-trail" suffix.
     */
    public function up(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $taken = [];

        DB::table('trails')->orderBy('id')->select(['id', 'name'])->each(function ($trail) use (&$taken) {
            $base = Str::slug((string) $trail->name) ?: 'trail';

            if (ctype_digit($base)) {
                $base .= '-trail';
            }

            $slug = $base;
            $suffix = 1;

            while (isset($taken[$slug])) {
                $slug = $base.'-'.++$suffix;
            }

            $taken[$slug] = true;

            DB::table('trails')->where('id', $trail->id)->update(['slug' => $slug]);
        });

        Schema::table('trails', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
