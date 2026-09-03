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

    public function test_the_town_option_writes_only_the_named_town(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686, 35)->create(['name' => 'Smithers', 'slug' => 'smithers-bc']);
        $telkwa = Town::factory()->at(54.6939, -127.0522, 25)->create(['name' => 'Telkwa', 'slug' => 'telkwa-bc']);

        $nearSmithers = $this->makeTrail(54.7820, -127.1690);
        $nearTelkwa = $this->makeTrail(54.6950, -127.0530);

        $this->artisan('towns:assign', ['--town' => ['telkwa-bc']])->assertSuccessful();

        $this->assertSame($telkwa->id, $nearTelkwa->fresh()->town_id);
        $this->assertNull($nearSmithers->fresh()->town_id, 'A scoped run must not touch another town.');
    }

    public function test_a_scoped_run_cannot_claim_a_neighbours_trail(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686, 35)->create(['slug' => 'smithers-bc']);
        $telkwa = Town::factory()->at(54.6939, -127.0522, 25)->create(['slug' => 'telkwa-bc']);

        // Sits inside Smithers' 35 km radius but is nearer to Telkwa. Scoping to
        // Smithers must not hand it over, because matching still runs against
        // every town.
        $trail = $this->makeTrail(54.6950, -127.0530);

        $this->artisan('towns:assign', ['--town' => ['smithers-bc']])->assertSuccessful();

        $this->assertNull($trail->fresh()->town_id);

        $this->artisan('towns:assign', ['--town' => ['telkwa-bc']])->assertSuccessful();

        $this->assertSame($telkwa->id, $trail->fresh()->town_id);
    }

    public function test_the_town_option_accepts_a_name_as_well_as_a_slug(): void
    {
        $town = Town::factory()->at(54.2286, -125.7594, 45)->create(['name' => 'Burns Lake', 'slug' => 'burns-lake-bc']);
        $trail = $this->makeTrail(54.2300, -125.7600);

        $this->artisan('towns:assign', ['--town' => ['Burns Lake']])->assertSuccessful();

        $this->assertSame($town->id, $trail->fresh()->town_id);
    }

    public function test_several_towns_can_be_scoped_at_once(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686, 35)->create(['slug' => 'smithers-bc']);
        $telkwa = Town::factory()->at(54.6939, -127.0522, 25)->create(['slug' => 'telkwa-bc']);
        $houston = Town::factory()->at(54.3986, -126.6470, 40)->create(['slug' => 'houston-bc']);

        $s = $this->makeTrail(54.7820, -127.1690);
        $t = $this->makeTrail(54.6950, -127.0530);
        $h = $this->makeTrail(54.3990, -126.6480);

        $this->artisan('towns:assign', ['--town' => ['smithers-bc', 'houston-bc']])->assertSuccessful();

        $this->assertSame($smithers->id, $s->fresh()->town_id);
        $this->assertSame($houston->id, $h->fresh()->town_id);
        $this->assertNull($t->fresh()->town_id);
    }

    public function test_an_unknown_town_stops_the_run(): void
    {
        Town::factory()->at(54.7824, -127.1686, 35)->create(['slug' => 'smithers-bc']);
        $trail = $this->makeTrail(54.7820, -127.1690);

        $this->artisan('towns:assign', ['--town' => ['nowhere-bc']])->assertFailed();

        $this->assertNull($trail->fresh()->town_id, 'A typo must not silently assign nothing and report success.');
    }

    public function test_a_scoped_dry_run_writes_nothing(): void
    {
        Town::factory()->at(54.7824, -127.1686, 35)->create(['slug' => 'smithers-bc']);
        $trail = $this->makeTrail(54.7820, -127.1690);

        $this->artisan('towns:assign', ['--town' => ['smithers-bc'], '--dry-run' => true])->assertSuccessful();

        $this->assertNull($trail->fresh()->town_id);
    }

    public function test_scoped_force_moves_a_record_onto_the_named_town(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686, 35)->create(['slug' => 'smithers-bc']);
        $telkwa = Town::factory()->at(54.6939, -127.0522, 25)->create(['slug' => 'telkwa-bc']);

        // Hand-assigned to the wrong town; nearest is really Telkwa.
        $trail = $this->makeTrail(54.6950, -127.0530, ['town_id' => $smithers->id]);

        $this->artisan('towns:assign', ['--town' => ['telkwa-bc'], '--force' => true])->assertSuccessful();

        $this->assertSame($telkwa->id, $trail->fresh()->town_id);
    }
}
