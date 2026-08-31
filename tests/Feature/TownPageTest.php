<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Town;
use App\Models\Trail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TownPageTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_it_renders_a_published_town_page(): void
    {
        $town = Town::factory()->at(54.3986, -126.6470)->create(['name' => 'Houston', 'slug' => 'houston-bc']);
        $this->makeTrail($town, ['name' => 'Buck Flats Trail']);

        $this->get(route('towns.show', $town))
            ->assertOk()
            // The heading splits the town name onto its own gradient-filled
            // line, so assert the two halves rather than the <title> string.
            ->assertSee('Hiking Trails in')
            ->assertSeeInOrder(['<h1', 'Houston', '</h1>'], false)
            ->assertSee('Buck Flats Trail');
    }

    public function test_it_shows_only_its_own_towns_trails(): void
    {
        $houston = Town::factory()->at(54.3986, -126.6470)->create(['name' => 'Houston', 'slug' => 'houston-bc']);
        $telkwa = Town::factory()->at(54.6939, -127.0522)->create(['name' => 'Telkwa', 'slug' => 'telkwa-bc']);

        $this->makeTrail($houston, ['name' => 'Houston Only Trail']);
        $this->makeTrail($telkwa, ['name' => 'Telkwa Only Trail']);

        $this->get(route('towns.show', $houston))
            ->assertOk()
            ->assertSee('Houston Only Trail')
            ->assertDontSee('Telkwa Only Trail');
    }

    public function test_it_excludes_closed_trails(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Open Trail']);
        $this->makeTrail($town, ['name' => 'Closed Trail', 'status' => 'closed']);

        $this->get(route('towns.show', $town))
            ->assertOk()
            ->assertSee('Open Trail')
            ->assertDontSee('Closed Trail');
    }

    public function test_an_unknown_slug_is_a_404(): void
    {
        $this->get('/hiking-trails/nowhere-bc')->assertNotFound();
    }

    public function test_an_inactive_town_is_a_404(): void
    {
        $town = Town::factory()->inactive()->create();

        $this->get(route('towns.show', $town))->assertNotFound();
    }

    public function test_a_town_without_trails_renders_the_empty_state_and_is_noindexed(): void
    {
        $town = Town::factory()->create(['name' => 'Stewart', 'slug' => 'stewart-bc']);

        $this->get(route('towns.show', $town))
            ->assertOk()
            ->assertSee('No trails mapped here yet')
            ->assertSee('name="robots" content="noindex,follow"', false);
    }

    public function test_a_town_with_trails_is_indexable(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town);

        $this->get(route('towns.show', $town))
            ->assertOk()
            ->assertDontSee('noindex', false);
    }

    public function test_the_admin_indexing_override_beats_the_automatic_rule(): void
    {
        $town = Town::factory()->create(['is_indexable' => false]);
        $this->makeTrail($town);

        $this->get(route('towns.show', $town))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,follow"', false);
    }

    public function test_it_emits_a_canonical_url_and_page_specific_description(): void
    {
        $town = Town::factory()->create(['name' => 'Houston', 'slug' => 'houston-bc', 'meta_description' => 'Trails around Houston BC.']);

        $response = $this->get(route('towns.show', $town));

        $response->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('towns.show', $town).'">', false)
            ->assertSee('content="Trails around Houston BC."', false);
    }

    public function test_it_emits_valid_structured_data(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Structured Trail']);

        $html = $this->get(route('towns.show', $town))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<script type="application/ld\+json">#', $html);

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $data = json_decode($matches[1], true);

        $this->assertIsArray($data, 'The JSON-LD block is not valid JSON.');
        $this->assertSame('https://schema.org', $data['@context']);

        $types = array_column($data['@graph'], '@type');
        $this->assertContains('BreadcrumbList', $types);
        $this->assertContains('TouristDestination', $types);
        $this->assertContains('ItemList', $types);
    }

    public function test_the_nearby_business_fallback_reaches_a_neighbouring_town(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686)->create(['name' => 'Smithers', 'slug' => 'smithers-bc']);
        $telkwa = Town::factory()->at(54.6939, -127.0522, 25)->create(['name' => 'Telkwa', 'slug' => 'telkwa-bc']);

        Business::create([
            'town_id' => $smithers->id,
            'name' => 'Smithers Cafe',
            'business_type' => 'cafe',
            'latitude' => 54.7824,
            'longitude' => -127.1686,
            'is_active' => true,
        ]);

        // Telkwa has no businesses of its own, so the fallback should surface
        // the Smithers one 15 km away.
        $this->assertSame(0, $telkwa->businesses()->count());
        $this->assertSame('Smithers Cafe', $telkwa->nearbyBusinesses()->first()->name);
    }

    public function test_the_business_fallback_gives_up_for_a_remote_town(): void
    {
        $smithers = Town::factory()->at(54.7824, -127.1686)->create();
        $stewart = Town::factory()->at(55.9386, -129.9903, 50)->create();

        Business::create([
            'town_id' => $smithers->id,
            'name' => 'Smithers Cafe',
            'business_type' => 'cafe',
            'latitude' => 54.7824,
            'longitude' => -127.1686,
            'is_active' => true,
        ]);

        $this->assertCount(0, $stewart->nearbyBusinesses());
    }

    public function test_the_town_filter_scopes_the_trail_listing(): void
    {
        $houston = Town::factory()->create(['slug' => 'houston-bc']);
        $telkwa = Town::factory()->create(['slug' => 'telkwa-bc']);

        $this->makeTrail($houston, ['name' => 'Houston Listing Trail']);
        $this->makeTrail($telkwa, ['name' => 'Telkwa Listing Trail']);

        $this->get(route('trails.index', ['town' => 'houston-bc']))
            ->assertOk()
            ->assertSee('Houston Listing Trail')
            ->assertDontSee('Telkwa Listing Trail');
    }

    public function test_the_town_filter_scopes_the_map_api(): void
    {
        $houston = Town::factory()->create(['slug' => 'houston-bc']);
        $telkwa = Town::factory()->create(['slug' => 'telkwa-bc']);

        $this->makeTrail($houston, ['name' => 'Houston Api Trail']);
        $this->makeTrail($telkwa, ['name' => 'Telkwa Api Trail']);

        $response = $this->getJson('/api/trails?town=houston-bc')->assertOk();

        $names = collect($response->json('trails') ?? $response->json())->pluck('name');

        $this->assertContains('Houston Api Trail', $names);
        $this->assertNotContains('Telkwa Api Trail', $names);
    }

    public function test_the_hub_page_lists_published_towns_only(): void
    {
        Town::factory()->create(['name' => 'Houston']);
        Town::factory()->inactive()->create(['name' => 'Hiddenville']);

        $this->get(route('towns.index'))
            ->assertOk()
            ->assertSee('Houston')
            ->assertDontSee('Hiddenville');
    }

    public function test_saving_a_trail_syncs_its_indexed_coordinates(): void
    {
        $town = Town::factory()->create();
        $trail = $this->makeTrail($town, ['start_coordinates' => [54.5, -126.5]]);

        $this->assertSame('54.5000000', $trail->fresh()->start_latitude);
        $this->assertSame('-126.5000000', $trail->fresh()->start_longitude);
    }

    public function test_the_full_map_link_centres_on_the_town(): void
    {
        $town = Town::factory()->at(54.3986, -126.6470)->create(['slug' => 'houston-bc']);

        $this->get(route('map', ['town' => $town->slug]))
            ->assertOk()
            ->assertSee('[54.3986,-126.647]', false);
    }

    public function test_it_uses_the_shared_hero_and_section_design_language(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town);

        $this->get(route('towns.show', $town))
            ->assertOk()
            // Guards against a bespoke off-palette hero being reintroduced.
            ->assertSee('hero-gradient', false)
            ->assertSee('bg-pattern-trees', false)
            ->assertSee('section-title text-forest-600', false)
            ->assertSee('cta-section', false)
            ->assertDontSee('town-hero', false);
    }

    public function test_the_hub_falls_back_to_texture_when_no_terrain_image_is_available(): void
    {
        config(['services.mapbox.access_token' => null]);
        $town = Town::factory()->create(['name' => 'Houston', 'hero_image' => null]);

        $this->assertNull($town->staticMapUrl());

        $this->get(route('towns.index'))
            ->assertOk()
            ->assertSee('Houston')
            // No <img>, but the tile still has a textured surface rather than
            // a flat empty block.
            ->assertSee('bg-pattern-contour', false)
            ->assertDontSee('api.mapbox.com/styles', false);
    }

    public function test_the_hub_uses_a_terrain_map_when_a_town_has_no_photo(): void
    {
        config(['services.mapbox.access_token' => 'pk.test-token']);
        Town::factory()->at(54.7824, -127.1686)->create(['name' => 'Smithers', 'hero_image' => null]);

        $this->get(route('towns.index'))
            ->assertOk()
            ->assertSee('api.mapbox.com/styles/v1/mapbox/outdoors-v12/static', false)
            ->assertSee('Terrain around Smithers, British Columbia', false);
    }

    public function test_an_uploaded_hero_image_wins_over_the_terrain_map(): void
    {
        config(['services.mapbox.access_token' => 'pk.test-token']);
        Town::factory()->create(['name' => 'Houston', 'hero_image' => 'towns/1/houston.jpg']);

        $this->get(route('towns.index'))
            ->assertOk()
            ->assertSee('storage/towns/1/houston.jpg', false);
    }
}
