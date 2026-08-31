<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Town;
use App\Models\Trail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TownAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function makeTrail(float $latitude, float $longitude, array $attributes = []): Trail
    {
        return Trail::create(array_merge([
            'name' => 'Assignment Trail',
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'status' => 'active',
            'trail_type' => 'loop',
            'start_coordinates' => [$latitude, $longitude],
        ], $attributes));
    }

    public function test_distance_to_matches_the_known_smithers_to_telkwa_gap(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686)->create();

        // Smithers to Telkwa is about 12.4 km in a straight line (the ~20 km
        // people quote is the drive along Highway 16).
        $this->assertEqualsWithDelta(12.4, $smithers->distanceTo(54.6939, -127.0522), 0.5);
    }

    public function test_it_assigns_a_trail_to_the_nearest_town(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686, 35)->create(['name' => 'Smithers']);
        $telkwa = Town::factory()->at(54.6939, -127.0522, 25)->create(['name' => 'Telkwa']);

        $nearTelkwa = $this->makeTrail(54.6950, -127.0530);
        $nearSmithers = $this->makeTrail(54.7820, -127.1690);

        $this->artisan('towns:assign')->assertSuccessful();

        $this->assertSame($telkwa->id, $nearTelkwa->fresh()->town_id);
        $this->assertSame($smithers->id, $nearSmithers->fresh()->town_id);
    }

    public function test_a_trail_outside_every_radius_stays_unassigned(): void
    {
        Town::factory()->at(54.7824, -127.1686, 35)->create();

        // Prince George, ~370 km east of Smithers.
        $remote = $this->makeTrail(53.9171, -122.7497);

        $this->artisan('towns:assign')->assertSuccessful();

        $this->assertNull($remote->fresh()->town_id);
    }

    public function test_it_leaves_an_existing_assignment_alone(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686, 35)->create();
        $telkwa = Town::factory()->at(54.6939, -127.0522, 25)->create();

        // Sits next to Telkwa but was hand-assigned to Smithers by an admin.
        $trail = $this->makeTrail(54.6950, -127.0530, ['town_id' => $smithers->id]);

        $this->artisan('towns:assign')->assertSuccessful();

        $this->assertSame($smithers->id, $trail->fresh()->town_id);
    }

    public function test_force_overrides_an_existing_assignment(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686, 35)->create();
        $telkwa = Town::factory()->at(54.6939, -127.0522, 25)->create();

        $trail = $this->makeTrail(54.6950, -127.0530, ['town_id' => $smithers->id]);

        $this->artisan('towns:assign', ['--force' => true])->assertSuccessful();

        $this->assertSame($telkwa->id, $trail->fresh()->town_id);
    }

    public function test_a_dry_run_writes_nothing(): void
    {
        Town::factory()->at(54.7824, -127.1686, 35)->create();
        $trail = $this->makeTrail(54.7820, -127.1690);

        $this->artisan('towns:assign', ['--dry-run' => true])->assertSuccessful();

        $this->assertNull($trail->fresh()->town_id);
    }

    public function test_it_assigns_businesses_by_their_own_coordinates(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686, 35)->create();

        $business = Business::create([
            'name' => 'Main Street Cafe',
            'business_type' => 'cafe',
            'latitude' => 54.7810,
            'longitude' => -127.1690,
            'is_active' => true,
        ]);

        $this->artisan('towns:assign')->assertSuccessful();

        $this->assertSame($smithers->id, $business->fresh()->town_id);
    }

    public function test_it_fails_cleanly_when_no_towns_exist(): void
    {
        $this->artisan('towns:assign')->assertFailed();
    }
}
