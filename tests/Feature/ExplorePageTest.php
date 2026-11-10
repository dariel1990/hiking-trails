<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Town;
use App\Models\Trail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExplorePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * The page is served from one cached payload, so a leftover entry from
         * a previous test would answer before the controller ever runs.
         */
        Cache::flush();
    }

    private function smithers(): Town
    {
        return Town::factory()->at(54.7824, -127.1686)->create([
            'name' => 'Smithers',
            'slug' => 'smithers-bc',
            'sort_order' => 1,
        ]);
    }

    private function makeTrail(Town $town, array $attributes = []): Trail
    {
        return Trail::create(array_merge([
            'town_id' => $town->id,
            'name' => 'Test Trail',
            'description' => 'A trail used in tests.',
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'status' => 'active',
            'difficulty_level' => 3,
            'distance_km' => 5,
            'elevation_gain_m' => 100,
            'estimated_time_hours' => 2,
            'trail_type' => 'loop',
            'start_coordinates' => [(float) $town->latitude, (float) $town->longitude],
        ], $attributes));
    }

    public function test_it_renders_the_road_trip_page(): void
    {
        $smithers = $this->smithers();
        $telkwa = Town::factory()->at(54.6939, -127.0522)->create(['name' => 'Telkwa', 'slug' => 'telkwa-bc', 'sort_order' => 2]);

        $this->makeTrail($telkwa, ['name' => 'Telkwa Bluff Trail']);

        $this->get(route('explore.index'))
            ->assertOk()
            ->assertSee('Explore Northern BC.')
            ->assertSee('Road trips from Smithers.')
            ->assertSee('Telkwa')
            ->assertSee('Telkwa Bluff Trail');
    }

    public function test_it_excludes_the_origin_town_from_the_road_trip_list(): void
    {
        $smithers = $this->smithers();
        $telkwa = Town::factory()->at(54.6939, -127.0522)->create(['name' => 'Telkwa', 'slug' => 'telkwa-bc', 'sort_order' => 2]);

        $this->makeTrail($smithers, ['name' => 'Perimeter Trail']);
        $this->makeTrail($telkwa, ['name' => 'Telkwa Bluff Trail']);

        $response = $this->get(route('explore.index'))->assertOk();

        // Smithers is the origin, so it never gets a card or a trail on the page.
        $response->assertDontSee('Plan a trip to Smithers');
        $response->assertSee('Plan a trip to Telkwa');
        $response->assertDontSee('Perimeter Trail');
    }

    public function test_it_shows_only_published_and_active_records(): void
    {
        $this->smithers();
        $houston = Town::factory()->at(54.3986, -126.6470)->create(['name' => 'Houston', 'slug' => 'houston-bc', 'sort_order' => 3]);
        Town::factory()->inactive()->at(55.2500, -127.5878)->create(['name' => 'Hazelton', 'slug' => 'hazelton-bc']);

        $this->makeTrail($houston, ['name' => 'Buck Flats Trail']);
        $this->makeTrail($houston, ['name' => 'Closed For Logging Trail', 'status' => 'closed']);

        Business::create([
            'town_id' => $houston->id,
            'name' => 'Open Roadside Diner',
            'slug' => 'open-roadside-diner',
            'description' => 'Coffee and pie.',
            'business_type' => 'restaurant',
            'latitude' => 54.3986,
            'longitude' => -126.6470,
            'is_active' => true,
        ]);

        Business::create([
            'town_id' => $houston->id,
            'name' => 'Closed Down Outfitter',
            'slug' => 'closed-down-outfitter',
            'description' => 'No longer trading.',
            'business_type' => 'gear_rental',
            'latitude' => 54.3986,
            'longitude' => -126.6470,
            'is_active' => false,
        ]);

        $this->get(route('explore.index'))
            ->assertOk()
            ->assertSee('Buck Flats Trail')
            ->assertDontSee('Closed For Logging Trail')
            ->assertSee('Open Roadside Diner')
            ->assertDontSee('Closed Down Outfitter')
            // An inactive town is not a destination, however far away it is.
            ->assertDontSee('Plan a trip to Hazelton');
    }

    public function test_each_town_shows_a_distance_and_drive_time_from_the_origin(): void
    {
        $this->smithers();
        Town::factory()->at(54.6939, -127.0522)->create(['name' => 'Telkwa', 'slug' => 'telkwa-bc', 'sort_order' => 2]);
        Town::factory()->at(55.9386, -129.9903)->create(['name' => 'Stewart', 'slug' => 'stewart-bc', 'sort_order' => 6]);

        $response = $this->get(route('explore.index'))->assertOk();

        // Telkwa is a 15 km hop down the valley; Stewart is the far end of 37A.
        $response->assertSeeInOrder(['Telkwa', '15 min', '15 km'], false);
        $response->assertSee('4 hr 30 min');
        $response->assertSee('354 km');

        // Which puts them in different bands of the page's spine.
        $response->assertSee('Day Trip');
        $response->assertSee('Weekend Escape');
    }
}
