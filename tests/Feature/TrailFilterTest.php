<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Town;
use App\Models\Trail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrailFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makeTrail(?Town $town, array $attributes = []): Trail
    {
        return Trail::create(array_merge([
            'town_id' => $town?->id,
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
            'start_coordinates' => [54.0, -127.0],
        ], $attributes));
    }

    private function makeBusiness(?Town $town, array $attributes = []): Business
    {
        return Business::create(array_merge([
            'town_id' => $town?->id,
            'name' => 'Test Business',
            'business_type' => 'cafe',
            'latitude' => 54.0,
            'longitude' => -127.0,
            'is_active' => true,
        ], $attributes));
    }

    // ---------------------------------------------------------------- town

    public function test_the_town_filter_narrows_the_trail_listing(): void
    {
        $houston = Town::factory()->create(['name' => 'Houston', 'slug' => 'houston-bc']);
        $telkwa = Town::factory()->create(['name' => 'Telkwa', 'slug' => 'telkwa-bc']);

        $this->makeTrail($houston, ['name' => 'Houston Trail']);
        $this->makeTrail($telkwa, ['name' => 'Telkwa Trail']);

        $this->get(route('trails.index', ['town' => 'houston-bc']))
            ->assertOk()
            ->assertSee('Houston Trail')
            ->assertDontSee('Telkwa Trail');
    }

    public function test_the_town_filter_narrows_the_lake_listing(): void
    {
        $houston = Town::factory()->create(['slug' => 'houston-bc']);
        $telkwa = Town::factory()->create(['slug' => 'telkwa-bc']);

        $lake = ['location_type' => 'fishing_lake', 'geometry_type' => 'point'];
        $this->makeTrail($houston, $lake + ['name' => 'Houston Lake']);
        $this->makeTrail($telkwa, $lake + ['name' => 'Telkwa Lake']);

        $this->get(route('fishing-lakes.index', ['town' => 'houston-bc']))
            ->assertOk()
            ->assertSee('Houston Lake')
            ->assertDontSee('Telkwa Lake');
    }

    public function test_the_town_filter_narrows_the_business_listing(): void
    {
        $houston = Town::factory()->create(['slug' => 'houston-bc']);
        $telkwa = Town::factory()->create(['slug' => 'telkwa-bc']);

        $this->makeBusiness($houston, ['name' => 'Houston Cafe', 'slug' => 'houston-cafe']);
        $this->makeBusiness($telkwa, ['name' => 'Telkwa Cafe', 'slug' => 'telkwa-cafe']);

        $this->get(route('businesses.public.index', ['town' => 'houston-bc']))
            ->assertOk()
            ->assertSee('Houston Cafe')
            ->assertDontSee('Telkwa Cafe');
    }

    /**
     * The bug that prompted this work: with no town field in the form, applying
     * any other filter on a town-scoped page silently dropped the town.
     */
    public function test_applying_another_filter_keeps_the_town(): void
    {
        $houston = Town::factory()->create(['slug' => 'houston-bc']);
        $telkwa = Town::factory()->create(['slug' => 'telkwa-bc']);

        $this->makeTrail($houston, ['name' => 'Houston Moderate', 'difficulty_level' => 3]);
        $this->makeTrail($houston, ['name' => 'Houston Hard', 'difficulty_level' => 5]);
        $this->makeTrail($telkwa, ['name' => 'Telkwa Moderate', 'difficulty_level' => 3]);

        $this->get(route('trails.index', ['town' => 'houston-bc', 'difficulty' => 3]))
            ->assertOk()
            ->assertSee('Houston Moderate')
            ->assertDontSee('Houston Hard')
            ->assertDontSee('Telkwa Moderate');
    }

    public function test_the_town_select_marks_the_current_town(): void
    {
        Town::factory()->create(['name' => 'Houston', 'slug' => 'houston-bc']);

        $this->get(route('trails.index', ['town' => 'houston-bc']))
            ->assertOk()
            ->assertSee('name="town"', false)
            ->assertSee('value="houston-bc" selected', false);
    }

    // ---------------------------------------------------------------- panel

    public function test_the_panel_renders_on_all_three_listings(): void
    {
        Town::factory()->create();

        foreach (['trails.index', 'fishing-lakes.index', 'businesses.public.index'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('filter-open-btn', false)
                ->assertSee('filter-sheet-dialog', false)
                ->assertSee('name="town"', false);
        }
    }

    public function test_the_badge_counts_only_active_filters(): void
    {
        Town::factory()->create(['slug' => 'houston-bc']);
        $this->makeTrail(null);

        // The badge markup only appears when something is filtering.
        $this->get(route('trails.index'))->assertOk()->assertDontSee('tabular-nums">1<', false);

        $this->get(route('trails.index', ['town' => 'houston-bc']))
            ->assertOk()
            ->assertSee('tabular-nums">1<', false);

        $this->get(route('trails.index', ['town' => 'houston-bc', 'difficulty' => 3]))
            ->assertOk()
            ->assertSee('tabular-nums">2<', false);
    }

    public function test_each_chip_removes_only_its_own_filter(): void
    {
        Town::factory()->create(['name' => 'Houston', 'slug' => 'houston-bc']);

        $html = $this->get(route('trails.index', ['town' => 'houston-bc', 'difficulty' => 3]))
            ->assertOk()
            ->getContent();

        preg_match_all('/<a href="([^"]+)"\s+class="filter-chip group"/', $html, $matches);
        $links = array_map('html_entity_decode', $matches[1]);

        $this->assertCount(2, $links, 'One chip per active filter.');

        $queries = array_map(fn ($link) => parse_url($link, PHP_URL_QUERY), $links);

        // Removing the town keeps the difficulty and vice versa.
        $this->assertContains('difficulty=3', $queries);
        $this->assertContains('town=houston-bc', $queries);
    }

    public function test_a_chip_link_also_drops_the_pagination_cursor(): void
    {
        Town::factory()->create(['slug' => 'houston-bc']);

        $html = $this->get(route('trails.index', ['town' => 'houston-bc', 'difficulty' => 3, 'hiking_page' => 4]))
            ->assertOk()
            ->getContent();

        preg_match('/<a href="([^"]+)"\s+class="filter-chip group"/', $html, $matches);

        $this->assertStringNotContainsString('hiking_page', html_entity_decode($matches[1]),
            'Removing a filter must return to page one.');
    }

    public function test_the_search_term_gets_its_own_chip(): void
    {
        Town::factory()->create();

        $this->get(route('trails.index', ['search' => 'waterfall']))
            ->assertOk()
            ->assertSee('Remove search term waterfall', false);
    }

    // ---------------------------------------------------------------- options

    public function test_the_filter_option_keys_match_the_listing_query(): void
    {
        $town = Town::factory()->create();

        // Distance bands are shared between the select and the query switch;
        // if either side is renamed, this catches it.
        $this->makeTrail($town, ['name' => 'Short Walk', 'distance_km' => 3]);
        $this->makeTrail($town, ['name' => 'Long Slog', 'distance_km' => 25]);

        $this->assertSame(['0-5', '5-10', '10-20', '20+'], array_keys(Trail::getDistanceRanges()));

        $this->get(route('trails.index', ['distance' => '0-5']))
            ->assertOk()
            ->assertSee('Short Walk')
            ->assertDontSee('Long Slog');

        $this->get(route('trails.index', ['distance' => '20+']))
            ->assertOk()
            ->assertSee('Long Slog')
            ->assertDontSee('Short Walk');
    }

    public function test_season_keys_stay_lowercase_for_the_json_query(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Summer Route', 'best_seasons' => ['Summer']]);
        $this->makeTrail($town, ['name' => 'Winter Route', 'best_seasons' => ['Winter']]);

        $this->assertSame(['spring', 'summer', 'fall', 'winter'], array_keys(Trail::getSeasons()));

        $this->get(route('trails.index', ['season' => 'summer']))
            ->assertOk()
            ->assertSee('Summer Route')
            ->assertDontSee('Winter Route');
    }

    // ---------------------------------------------------------------- ajax

    public function test_load_more_still_honours_the_town_filter(): void
    {
        $houston = Town::factory()->create(['slug' => 'houston-bc']);
        $telkwa = Town::factory()->create(['slug' => 'telkwa-bc']);

        $this->makeTrail($houston, ['name' => 'Houston Paged']);
        $this->makeTrail($telkwa, ['name' => 'Telkwa Paged']);

        $response = $this->getJson(route('trails.index', [
            'town' => 'houston-bc',
            'ajax_type' => 'hiking',
            'hiking_page' => 1,
        ]))->assertOk();

        $this->assertStringContainsString('Houston Paged', $response->json('html'));
        $this->assertStringNotContainsString('Telkwa Paged', $response->json('html'));
    }

    // ---------------------------------------------------------------- home

    public function test_the_home_page_uses_the_shared_filter_panel(): void
    {
        Town::factory()->create(['name' => 'Houston', 'slug' => 'houston-bc']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('filter-open-btn', false)
            ->assertSee('filter-sheet-dialog', false)
            ->assertSee('name="town"', false)
            // It is a launcher, so it posts to the trails listing.
            ->assertSee('action="'.route('trails.index').'"', false);
    }

    /**
     * The old home panel rendered {{ $trails->total() }} inside a
     * request()->hasAny() guard, but home() never passed $trails - so any visit
     * carrying one of those params returned a 500.
     */
    public function test_the_home_page_survives_filter_params_in_the_url(): void
    {
        Town::factory()->create(['slug' => 'houston-bc']);

        foreach (['search' => 'falls', 'difficulty' => '3', 'distance' => '0-5', 'season' => 'summer', 'town' => 'houston-bc'] as $key => $value) {
            $this->get(route('home', [$key => $value]))
                ->assertOk("Home 500s when the URL carries ?{$key}=");
        }
    }

    public function test_the_home_panel_shows_no_result_count(): void
    {
        Town::factory()->create();

        // Home renders no listing, so a count would be meaningless.
        $this->get(route('home', ['difficulty' => 3]))
            ->assertOk()
            ->assertDontSee('trails found');
    }

    // ---------------------------------------------------------------- promo

    public function test_the_app_promo_sits_above_the_filter_panel(): void
    {
        Town::factory()->create();

        foreach (['home', 'trails.index', 'fishing-lakes.index', 'businesses.public.index'] as $route) {
            $html = $this->get(route($route))->assertOk()->getContent();

            // The banner only renders when a store URL is configured.
            if (! str_contains($html, 'filter-open-btn')) {
                continue;
            }

            if (str_contains($html, 'Now available')) {
                $this->assertLessThan(
                    strpos($html, 'filter-open-btn'),
                    strpos($html, 'Now available'),
                    "The app promo must come before the filter panel on [{$route}]."
                );
                $this->assertSame(1, substr_count($html, 'Now available'),
                    "The app promo is duplicated on [{$route}].");
            }
        }
    }
}
