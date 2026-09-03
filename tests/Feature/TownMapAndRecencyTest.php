<?php

namespace Tests\Feature;

use App\Models\Town;
use App\Models\Trail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TownMapAndRecencyTest extends TestCase
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

    // ------------------------------------------------------------ town colour

    public function test_the_api_exposes_the_town_and_its_colour(): void
    {
        $town = Town::factory()->create(['name' => 'Houston', 'slug' => 'houston-bc', 'color' => '#C2410C']);
        $this->makeTrail($town, ['name' => 'Coloured Trail']);

        $trail = $this->getJson('/api/trails')->assertOk()->json()[0];

        $this->assertSame($town->id, $trail['town_id']);
        $this->assertSame('Houston', $trail['town']['name']);
        $this->assertSame('#C2410C', $trail['town']['color']);
    }

    public function test_a_town_with_no_colour_falls_back_to_grey(): void
    {
        $town = Town::factory()->create(['color' => null]);
        $this->makeTrail($town);

        $trail = $this->getJson('/api/trails')->assertOk()->json()[0];

        $this->assertSame(Town::DEFAULT_COLOR, $trail['town']['color']);
        $this->assertSame(Town::DEFAULT_COLOR, $town->colorOrDefault());
    }

    public function test_a_trail_with_no_town_reports_null_rather_than_breaking(): void
    {
        $this->makeTrail(null, ['name' => 'Orphan Trail']);

        $trail = $this->getJson('/api/trails')->assertOk()->json()[0];

        $this->assertNull($trail['town']);
        $this->assertNull($trail['town_id']);
    }

    public function test_the_map_renders_a_legend_row_per_town_plus_unassigned(): void
    {
        Town::factory()->create(['name' => 'Houston', 'color' => '#C2410C']);
        Town::factory()->create(['name' => 'Telkwa', 'color' => '#0E7490']);

        $html = $this->get(route('map'))->assertOk()->getContent();

        $this->assertStringContainsString('id="town-legend"', $html);
        $this->assertStringContainsString('data-town-id="none"', $html);
        $this->assertStringContainsString('Unassigned', $html);
        $this->assertStringContainsString('#C2410C', $html);

        // One row per town plus the unassigned bucket.
        $this->assertSame(3, substr_count($html, 'class="town-legend-row"'));
    }

    public function test_the_map_offers_a_town_checkbox_per_town_plus_unassigned(): void
    {
        Town::factory()->count(2)->create();

        $html = $this->get(route('map'))->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, 'town-checkbox w-5'));
        $this->assertStringContainsString('value="none" class="town-checkbox', $html);
    }

    /**
     * matchesAdvancedFilters() bails out early for fishing lakes because the
     * other advanced filters are trail-only. The town test has to sit above
     * that bail-out, or lakes would ignore it.
     */
    public function test_the_town_check_precedes_the_fishing_lake_early_return(): void
    {
        $js = file_get_contents(resource_path('views/map.blade.php'));

        $townCheck = strpos($js, 'advancedFilters.towns.length > 0');
        $earlyReturn = strpos($js, "trail.location_type === 'fishing_lake') {\n                return true;");

        $this->assertNotFalse($townCheck, 'The town filter is missing from matchesAdvancedFilters().');
        $this->assertNotFalse($earlyReturn, 'The fishing-lake early return moved; re-check the ordering.');
        $this->assertLessThan($earlyReturn, $townCheck,
            'The town check must come before the fishing-lake early return, or lakes ignore the town filter.');
    }

    public function test_the_marker_colour_constant_matches_the_model(): void
    {
        $html = $this->get(route('map'))->assertOk()->getContent();

        $this->assertStringContainsString('UNASSIGNED_TOWN_COLOR = "'.Town::DEFAULT_COLOR.'"', $html);
    }

    public function test_the_spotlight_palette_covers_every_town_plus_unassigned(): void
    {
        $a = Town::factory()->create(['color' => '#C2410C']);
        $b = Town::factory()->create(['color' => null]);

        $html = $this->get(route('map'))->assertOk()->getContent();

        preg_match('/TOWN_COLORS = Object\.assign\(\s*(\{.*?\}),/s', $html, $matches);
        $palette = json_decode($matches[1], true);

        $this->assertSame('#C2410C', $palette[$a->id]);
        $this->assertSame(Town::DEFAULT_COLOR, $palette[$b->id], 'A colourless town still needs a usable swatch.');
        $this->assertStringContainsString('none: UNASSIGNED_TOWN_COLOR', $html);
    }

    /**
     * A 32px pin minus its 2px border and 22px icon leaves 3px of rim, which
     * cannot carry a hue over satellite terrain. Colour is spent by the
     * spotlight instead, so nothing may tint a marker at creation time.
     */
    public function test_trail_markers_are_not_tinted_at_rest(): void
    {
        $source = file_get_contents(resource_path('views/map.blade.php'));

        $this->assertStringNotContainsString(
            '_createMarkerEl(emoji, iconImageUrl, this.getTownColor(trail))',
            $source,
            'Trail pins must be created untinted; the spotlight applies colour on demand.'
        );

        // Matched loosely: the signature gained a camera option and should be
        // free to gain more without breaking this.
        $this->assertStringContainsString('spotlightTown(townId', $source);
        $this->assertStringContainsString('is-dimmed', $source);
    }

    public function test_the_spotlight_is_reapplied_after_a_rerender(): void
    {
        $source = file_get_contents(resource_path('views/map.blade.php'));

        $legend = strpos($source, 'this.updateTownLegend(allFilteredTrails);');
        $reapply = strpos($source, 'this.spotlightTown(spotlitTownId);');

        $this->assertNotFalse($reapply,
            'applyFilters() rebuilds every marker, so the spotlight must be reasserted there.');
        $this->assertGreaterThan($legend, $reapply);
    }

    public function test_selecting_a_town_moves_the_camera_to_it(): void
    {
        $source = file_get_contents(resource_path('views/map.blade.php'));

        $this->assertStringContainsString('frameTown(townId) {', $source);

        // The legend click is the one caller that asks for the camera.
        $this->assertStringContainsString('{ moveCamera: !clearing }', $source);
    }

    /**
     * spotlightTown() is re-run after every applyFilters() to reassert the
     * highlight on rebuilt markers. If the camera moved there too, the view
     * would jump on every season or activity change.
     */
    public function test_the_camera_does_not_move_when_the_spotlight_is_reasserted(): void
    {
        $source = file_get_contents(resource_path('views/map.blade.php'));

        $this->assertStringContainsString('spotlightTown(townId, { moveCamera = false } = {})', $source,
            'The camera must be opt-in, so the re-assert path leaves the view alone.');

        $this->assertStringContainsString('this.spotlightTown(spotlitTownId);', $source,
            'The re-assert call must not pass moveCamera.');
    }

    public function test_the_camera_payload_covers_every_town(): void
    {
        $a = Town::factory()->at(54.3986, -126.6470)->create(['map_zoom' => 10]);
        $b = Town::factory()->at(54.7824, -127.1686)->create(['map_zoom' => 11]);

        $html = $this->get(route('map'))->assertOk()->getContent();

        preg_match('/TOWN_VIEWS = (\{.*?\});/s', $html, $matches);
        $views = json_decode($matches[1], true);

        $this->assertSame(10, $views[$a->id]['zoom']);
        $this->assertSame(11, $views[$b->id]['zoom']);

        // GeoJSON order: [lng, lat].
        $this->assertEqualsWithDelta(-126.6470, $views[$a->id]['center'][0], 0.0001);
        $this->assertEqualsWithDelta(54.3986, $views[$a->id]['center'][1], 0.0001);
    }

    public function test_the_region_highlight_experiment_is_fully_removed(): void
    {
        $source = file_get_contents(resource_path('views/map.blade.php'));

        foreach (['TOWN_REGIONS', 'town-region-fill', 'town-region-outline', '_initTownRegionLayers'] as $leftover) {
            $this->assertStringNotContainsString($leftover, $source,
                "Region-highlight code [{$leftover}] survived the revert.");
        }
    }

    // --------------------------------------------------------------- recency

    public function test_the_api_exposes_created_at(): void
    {
        $this->makeTrail(Town::factory()->create());

        $trail = $this->getJson('/api/trails')->assertOk()->json()[0];

        $this->assertNotNull($trail['created_at']);
    }

    public function test_sort_newest_orders_by_creation(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Oldest'])->forceFill(['created_at' => now()->subYear()])->save();
        $this->makeTrail($town, ['name' => 'Newest'])->forceFill(['created_at' => now()])->save();

        $names = array_column($this->getJson('/api/trails?sort=newest')->assertOk()->json(), 'name');

        $this->assertSame(['Newest', 'Oldest'], $names);
    }

    public function test_since_filters_by_creation_date(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Old'])->forceFill(['created_at' => now()->subMonths(6)])->save();
        $this->makeTrail($town, ['name' => 'Fresh'])->forceFill(['created_at' => now()->subDay()])->save();

        $names = array_column(
            $this->getJson('/api/trails?since='.urlencode(now()->subWeek()->toIso8601String()))->assertOk()->json(),
            'name'
        );

        $this->assertSame(['Fresh'], $names);
    }

    /**
     * An unencoded "+" in an ISO 8601 offset arrives as a space. Left
     * unhandled, Carbon throws, the filter is dropped, and the caller gets the
     * whole catalogue back believing it asked for a slice.
     */
    public function test_since_survives_an_unencoded_plus_in_the_offset(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Old'])->forceFill(['created_at' => now()->subMonths(6)])->save();
        $this->makeTrail($town, ['name' => 'Fresh'])->forceFill(['created_at' => now()->subDay()])->save();

        // Exactly what a naive client produces: the raw string, "+" intact.
        $mangled = str_replace('+', ' ', now()->subWeek()->toIso8601String());

        $names = array_column($this->getJson('/api/trails?since='.$mangled)->assertOk()->json(), 'name');

        $this->assertSame(['Fresh'], $names, 'A space-for-plus offset must still filter.');
    }

    public function test_an_unparseable_since_is_rejected_rather_than_silently_ignored(): void
    {
        $this->makeTrail(Town::factory()->create());

        $this->getJson('/api/trails?since=banana')
            ->assertStatus(422)
            ->assertJsonPath('errors.since.0', 'Could not parse [banana].');
    }

    public function test_a_plain_date_is_accepted_for_since(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Old'])->forceFill(['created_at' => now()->subMonths(6)])->save();
        $this->makeTrail($town, ['name' => 'Fresh'])->forceFill(['created_at' => now()->subDay()])->save();

        $names = array_column(
            $this->getJson('/api/trails?since='.now()->subWeek()->toDateString())->assertOk()->json(),
            'name'
        );

        $this->assertSame(['Fresh'], $names);
    }

    public function test_omitting_the_new_params_leaves_the_response_unchanged(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'A']);
        $this->makeTrail($town, ['name' => 'B']);

        $plain = $this->getJson('/api/trails')->assertOk()->json();
        $again = $this->getJson('/api/trails?sort=&since=')->assertOk()->json();

        $this->assertSame($plain, $again);
    }

    // ------------------------------------------------------------ home page

    public function test_the_home_page_shows_recently_added_newest_first(): void
    {
        $town = Town::factory()->create(['name' => 'Burns Lake']);

        foreach (['Oldest', 'Middle', 'Newest'] as $index => $name) {
            $this->makeTrail($town, ['name' => $name])
                ->forceFill(['created_at' => now()->subDays(10 - $index)])->save();
        }

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('Recently added', $html);

        $band = substr($html, strpos($html, 'Recently added'));
        $this->assertLessThan(strpos($band, 'Oldest'), strpos($band, 'Newest'));
    }

    public function test_the_recent_row_is_capped_by_the_setting(): void
    {
        $town = Town::factory()->create();

        for ($i = 1; $i <= 10; $i++) {
            $this->makeTrail($town, ['name' => "Trail {$i}"]);
        }

        $this->assertSame(6, (int) setting('recent_trail_count'));

        $html = $this->get(route('home'))->assertOk()->getContent();
        $band = substr($html, strpos($html, 'Recently added'), strpos($html, 'Adventure by the Numbers') - strpos($html, 'Recently added'));

        $this->assertSame(6, substr_count($band, 'trail-card group'));
    }

    public function test_the_new_badge_marks_only_recent_trails(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Fresh Trail']);
        $this->makeTrail($town, ['name' => 'Stale Trail'])->forceFill(['created_at' => now()->subYear()])->save();

        $html = $this->get(route('home'))->assertOk()->getContent();
        $band = substr($html, strpos($html, 'Recently added'), strpos($html, 'Adventure by the Numbers') - strpos($html, 'Recently added'));

        // Both cards render; only the recent one carries the badge.
        $this->assertStringContainsString('Fresh Trail', $band);
        $this->assertStringContainsString('Stale Trail', $band);
        $this->assertSame(1, substr_count($band, 'badge-new'));
    }

    public function test_the_new_badge_is_opt_in_elsewhere(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'Fresh Trail']);

        // The trails listing does not pass $showNew, so no badge there.
        $this->get(route('trails.index'))->assertOk()->assertDontSee('badge-new', false);
    }

    // ------------------------------------------------------- shared partial

    public function test_the_card_falls_back_to_the_town_when_location_is_blank(): void
    {
        $town = Town::factory()->create(['name' => 'Burns Lake']);
        $this->makeTrail($town, ['name' => 'Unlabelled', 'location' => null]);

        $this->get(route('trails.index'))->assertOk()->assertSee('Burns Lake');
    }

    public function test_the_card_prefers_an_explicit_location_over_the_town(): void
    {
        $town = Town::factory()->create(['name' => 'Burns Lake']);
        $this->makeTrail($town, ['name' => 'Labelled', 'location' => 'Boer Mountain']);

        $this->get(route('trails.index'))->assertOk()->assertSee('Boer Mountain');
    }

    public function test_a_mixed_collection_labels_each_card_by_its_own_type(): void
    {
        $town = Town::factory()->create();
        $this->makeTrail($town, ['name' => 'A Trail']);
        $this->makeTrail($town, [
            'name' => 'A Lake',
            'location_type' => 'fishing_lake',
            'geometry_type' => 'point',
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $band = substr($html, strpos($html, 'Recently added'), strpos($html, 'Adventure by the Numbers') - strpos($html, 'Recently added'));

        // The row is passed type=hiking, but the lake must still be badged as one.
        $this->assertSame(1, substr_count($band, '🐟 Fishing'),
            'A mixed row must label each card from its own location_type, not the loop flag.');
    }
}
