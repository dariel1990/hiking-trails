<?php

namespace Tests\Feature;

use App\Models\ActivityType;
use App\Models\Town;
use App\Models\Trail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Summer / Winter filter on /fishing-lakes, using the interactive map's rule.
 */
class FishingLakeSeasonFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeLake(string $name, ?ActivityType $activity = null, array $attributes = []): Trail
    {
        $lake = Trail::create(array_merge([
            'name' => $name,
            'description' => 'A lake used in tests.',
            'location_type' => 'fishing_lake',
            'geometry_type' => 'point',
            'status' => 'active',
            'difficulty_level' => 1,
            'distance_km' => 0,
            'elevation_gain_m' => 0,
            'estimated_time_hours' => 1,
            'trail_type' => 'out-and-back',
            'start_coordinates' => [54.78, -127.17],
        ], $attributes));

        if ($activity) {
            $lake->activities()->attach($activity->id);
        }

        return $lake;
    }

    private function activity(string $slug, string $season): ActivityType
    {
        return ActivityType::create([
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'icon' => '🎣',
            'color' => '#3b82f6',
            'is_active' => true,
            'season_applicable' => $season,
        ]);
    }

    /**
     * Open water, ice fishing, year-round, best-season-only and no-info lakes.
     */
    private function makeLakes(): void
    {
        $this->makeLake('Open Water Lake', $this->activity('fishing', 'summer'));
        $this->makeLake('Ice Lake', $this->activity('ice-fishing', 'winter'));
        $this->makeLake('Year Round Lake', $this->activity('shore-angling', 'both'));
        $this->makeLake('Winter Pick Lake', null, ['best_fishing_season' => 'winter']);
        $this->makeLake('Mystery Lake');
    }

    public function test_summer_lists_open_water_lakes_but_not_ice_fishing_ones(): void
    {
        $this->makeLakes();

        $this->get(route('fishing-lakes.index', ['season' => 'summer']))
            ->assertOk()
            ->assertSee('Open Water Lake')
            ->assertSee('Year Round Lake')
            ->assertSee('Mystery Lake')
            ->assertDontSee('Ice Lake')
            ->assertDontSee('Winter Pick Lake');
    }

    public function test_winter_lists_ice_fishing_lakes_but_not_open_water_ones(): void
    {
        $this->makeLakes();

        $this->get(route('fishing-lakes.index', ['season' => 'winter']))
            ->assertOk()
            ->assertSee('Ice Lake')
            ->assertSee('Year Round Lake')
            ->assertSee('Winter Pick Lake')
            ->assertSee('Mystery Lake')
            ->assertDontSee('Open Water Lake');
    }

    public function test_summer_is_the_default_also_for_old_season_links(): void
    {
        $this->makeLakes();

        foreach ([[], ['season' => 'spring']] as $query) {
            $html = $this->get(route('fishing-lakes.index', $query))
                ->assertOk()
                ->assertSee('Open Water Lake')
                ->assertSee('Mystery Lake')
                ->assertDontSee('Ice Lake')
                ->assertDontSee('Winter Pick Lake')
                ->getContent();

            $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*☀️ Summer/u', $html);
        }
    }

    public function test_the_switch_marks_the_active_season_and_keeps_other_filters(): void
    {
        Town::factory()->create(['slug' => 'houston-bc']);
        $this->makeLake('Open Water Lake', $this->activity('fishing', 'summer'));

        $html = $this->get(route('fishing-lakes.index', ['season' => 'winter', 'town' => 'houston-bc', 'search' => 'lake', 'lake_page' => 2]))
            ->assertOk()
            ->assertSee('aria-label="Fishing season"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*❄️ Winter/u', $html);
        $this->assertStringContainsString('href="'.e(route('fishing-lakes.index').'?town=houston-bc&search=lake&season=summer').'"', $html);
        $this->assertStringContainsString('href="'.e(route('fishing-lakes.index').'?town=houston-bc&search=lake&season=winter').'"', $html, 'The selected tab stays selected.');
    }

    public function test_the_tabs_have_only_summer_and_winter_and_switch_without_reloading(): void
    {
        $html = $this->get(route('fishing-lakes.index'))->assertOk()->getContent();

        preg_match('/<nav[^>]*aria-label="Fishing season".*?<\/nav>/su', $html, $switch);
        $this->assertStringContainsString('☀️ Summer', $switch[0]);
        $this->assertStringContainsString('❄️ Winter', $switch[0]);
        $this->assertStringNotContainsString('All', $switch[0]);
        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*☀️ Summer/u', $switch[0], 'Summer is selected by default.');
        $this->assertSame(1, substr_count($switch[0], 'aria-current="page"'));
        $this->assertStringContainsString("@click.prevent=\"setSeason('summer')\"", $switch[0]);
        $this->assertStringContainsString('fishingLakesPage(', $html);
    }

    public function test_the_season_rides_along_with_the_filters_form_without_a_duplicate_dropdown(): void
    {
        $html = $this->get(route('fishing-lakes.index', ['season' => 'winter']))->assertOk()->getContent();

        $this->assertStringContainsString('<input type="hidden" name="season" id="filter-season" value="winter"', $html);
        $this->assertStringNotContainsString('<select name="season"', $html);
        $this->assertStringNotContainsString('Remove Season filter', $html, 'The tabs replace the season chip.');

        $this->assertMatchesRegularExpression(
            '/<input type="hidden" name="season" id="filter-season" value=""\s+disabled/',
            $this->get(route('fishing-lakes.index'))->getContent(),
        );
    }

    public function test_the_list_endpoint_returns_totals_for_the_tabs(): void
    {
        $this->makeLakes();

        $this->getJson(route('fishing-lakes.index', ['season' => 'winter', 'ajax_type' => 'lakes', 'lake_page' => 1]))
            ->assertOk()
            ->assertJson(['total' => 4, 'count' => 4, 'has_more' => false]);
    }

    public function test_load_more_keeps_the_season(): void
    {
        $this->makeLakes();

        $html = $this->getJson(route('fishing-lakes.index', ['season' => 'winter', 'ajax_type' => 'lakes']))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('Ice Lake', $html);
        $this->assertStringNotContainsString('Open Water Lake', $html);
    }

    public function test_lake_cards_show_the_seasons_they_are_fishable_in(): void
    {
        $this->makeLake('Ice Lake', $this->activity('ice-fishing', 'winter'));

        $this->get(route('fishing-lakes.index', ['season' => 'winter']))
            ->assertOk()
            ->assertSee('aria-label="Fishing seasons"', false)
            ->assertSeeInOrder(['Ice Lake', '❄️ Winter']);

        $this->assertSame(['winter'], Trail::where('name', 'Ice Lake')->first()->fishingSeasons());
        $this->assertSame(['summer', 'winter'], $this->makeLake('Mystery Lake')->fishingSeasons());
    }

    public function test_empty_season_explains_and_offers_the_other_season(): void
    {
        $this->makeLake('Open Water Lake', $this->activity('fishing', 'summer'));

        $this->get(route('fishing-lakes.index', ['season' => 'winter']))
            ->assertOk()
            ->assertSee('No lakes are listed for winter fishing yet.')
            ->assertSee('See summer lakes');
    }
}
