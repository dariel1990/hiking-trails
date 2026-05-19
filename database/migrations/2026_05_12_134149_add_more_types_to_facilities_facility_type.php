<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ENUM/MODIFY is MySQL-only; SQLite (test DB) treats the column as text.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE facilities MODIFY facility_type ENUM('parking', 'toilets', 'emergency_kit', 'lodge', 'viewpoint', 'info', 'picnic', 'water', 'shelter', 'camping_site', 'point_of_interest', 'aid_station', 'bridge', 'trail_sign', 'ttf') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE facilities MODIFY facility_type ENUM('parking', 'toilets', 'emergency_kit', 'lodge', 'viewpoint', 'info', 'picnic', 'water', 'shelter', 'camping_site') NOT NULL");
    }
};
