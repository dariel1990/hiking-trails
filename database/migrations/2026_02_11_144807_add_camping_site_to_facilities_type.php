<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ENUM/MODIFY is MySQL-only; SQLite (test DB) treats the column as text.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Modify the facility_type column to include camping_site
        // Using raw SQL because Laravel doesn't support modifying ENUM columns directly
        DB::statement("ALTER TABLE facilities MODIFY facility_type ENUM('parking', 'toilets', 'emergency_kit', 'lodge', 'viewpoint', 'info', 'picnic', 'water', 'shelter', 'camping_site') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Revert to original ENUM values
        DB::statement("ALTER TABLE facilities MODIFY facility_type ENUM('parking', 'toilets', 'emergency_kit', 'lodge', 'viewpoint', 'info', 'picnic', 'water', 'shelter') NOT NULL");
    }
};
