<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->decimal('start_latitude', 10, 7)->nullable()->after('start_coordinates');
            $table->decimal('start_longitude', 10, 7)->nullable()->after('start_latitude');

            $table->index(['start_latitude', 'start_longitude']);
        });

        $this->backfillFromJson();
    }

    /**
     * Copy the existing JSON [lat, lng] start_coordinates into the new plain
     * columns. Done in PHP rather than SQL so it behaves the same on MySQL and
     * SQLite.
     */
    private function backfillFromJson(): void
    {
        DB::table('trails')
            ->select('id', 'start_coordinates')
            ->whereNotNull('start_coordinates')
            ->orderBy('id')
            ->chunk(200, function ($trails) {
                foreach ($trails as $trail) {
                    $coordinates = json_decode($trail->start_coordinates, true);

                    if (! is_array($coordinates) || ! isset($coordinates[0], $coordinates[1])) {
                        continue;
                    }

                    DB::table('trails')->where('id', $trail->id)->update([
                        'start_latitude' => (float) $coordinates[0],
                        'start_longitude' => (float) $coordinates[1],
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trails', function (Blueprint $table) {
            $table->dropIndex(['start_latitude', 'start_longitude']);
            $table->dropColumn(['start_latitude', 'start_longitude']);
        });
    }
};
