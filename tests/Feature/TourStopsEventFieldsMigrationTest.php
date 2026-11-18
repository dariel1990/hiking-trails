<?php

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TourStopsEventFieldsMigrationTest extends TestCase
{
    use RefreshDatabase;

    private const EXPECTED_COLUMNS = ['latitude', 'longitude', 'icon', 'icon_image', 'description', 'image', 'is_roaming'];

    private function migration(): Migration
    {
        return require database_path('migrations/2026_11_13_011942_add_event_fields_to_tour_stops_table.php');
    }

    public function test_it_recovers_from_a_partial_run_with_missing_coordinates(): void
    {
        Schema::table('tour_stops', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'is_roaming']);
        });

        $this->assertTrue(Schema::hasColumn('tour_stops', 'image'));
        $this->assertFalse(Schema::hasColumn('tour_stops', 'longitude'));

        $this->migration()->up();

        $this->assertTrue(Schema::hasColumns('tour_stops', self::EXPECTED_COLUMNS));
    }

    public function test_it_adds_all_columns_when_none_exist(): void
    {
        Schema::table('tour_stops', function (Blueprint $table) {
            $table->dropColumn(self::EXPECTED_COLUMNS);
        });

        $this->migration()->up();

        $this->assertTrue(Schema::hasColumns('tour_stops', self::EXPECTED_COLUMNS));
    }

    public function test_it_is_a_no_op_when_every_column_already_exists(): void
    {
        $this->migration()->up();

        $this->assertTrue(Schema::hasColumns('tour_stops', self::EXPECTED_COLUMNS));
    }
}
