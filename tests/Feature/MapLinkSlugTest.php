<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\Tour;
use App\Models\TourStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "View on Interactive Map" links name businesses and events by slug in
 * browsers, like trails do, while the apps keep getting the ID.
 */
class MapLinkSlugTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 Safari/605.1.15';

    private const APP_UA = 'Mozilla/5.0 (Linux; Android 14; Pixel 8; wv) AppleWebKit/537.36 Chrome/120.0 Mobile Safari/537.36 XploreSmithersApp/1.0';

    private function makeBusiness(): Business
    {
        return Business::create([
            'name' => 'Bugwood Bean',
            'business_type' => 'cafe',
            'latitude' => 54.7824,
            'longitude' => -127.1686,
            'is_active' => true,
        ]);
    }

    private function makeTour(bool $withStop = true): Tour
    {
        $tour = Tour::create([
            'title' => 'Heritage Tour',
            'tour_type' => 'scenic',
            'is_active' => true,
        ]);

        if ($withStop) {
            TourStop::create([
                'tour_id' => $tour->id,
                'stop_order' => 1,
                'stop_label' => 'Central Park Building',
                'latitude' => 54.78,
                'longitude' => -127.17,
            ]);
        }

        return $tour;
    }

    public function test_business_pages_link_to_the_map_by_slug(): void
    {
        $business = $this->makeBusiness();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get(route('businesses.public.show', $business))
            ->assertOk()
            ->assertSee(route('map').'?business=bugwood-bean', false)
            ->assertDontSee(route('map').'?business='.$business->id.'"', false);
    }

    public function test_business_pages_in_the_apps_link_to_the_map_by_id(): void
    {
        $business = $this->makeBusiness();

        $this->withHeader('User-Agent', self::APP_UA)
            ->get(route('businesses.public.show', $business))
            ->assertOk()
            ->assertSee(route('map').'?business='.$business->id.'"', false);
    }

    public function test_event_pages_link_to_the_map_by_slug(): void
    {
        $event = Event::factory()->withRoute()->create(['title' => 'Light Up Night']);

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee(route('map', ['event' => 'light-up-night']), false)
            ->assertDontSee(route('map', ['event' => $event->id]).'"', false);
    }

    public function test_event_pages_in_the_apps_link_to_the_map_by_id(): void
    {
        $event = Event::factory()->withRoute()->create(['title' => 'Light Up Night']);

        $this->withHeader('User-Agent', self::APP_UA)
            ->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee(route('map', ['event' => $event->id]).'"', false);
    }

    public function test_the_map_accepts_a_slug_or_an_id_for_businesses_and_events(): void
    {
        $this->get(route('map'))
            ->assertOk()
            ->assertSee('this.businessData.find(b => window.trailMatchesKey(b, businessKey))', false)
            ->assertSee('events.find(e => window.trailMatchesKey(e, eventKey))', false);
    }

    public function test_the_map_data_includes_slugs_to_match_against(): void
    {
        $this->makeBusiness();
        Event::factory()->withRoute()->create(['title' => 'Light Up Night']);

        $this->getJson('/api/businesses')->assertOk()->assertJsonFragment(['slug' => 'bugwood-bean']);
        $this->getJson('/api/events')->assertOk()->assertJsonFragment(['slug' => 'light-up-night']);
    }

    public function test_tour_pages_link_to_the_map_by_slug_below_the_map(): void
    {
        $tour = $this->makeTour();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSeeInOrder(['id="tour-map"', 'View on Interactive Map', '<!-- Stop list'], false)
            ->assertSee(route('map', ['tour' => 'heritage-tour']), false)
            ->assertDontSee(route('map', ['tour' => $tour->id]).'"', false);
    }

    public function test_tour_pages_in_the_apps_link_to_the_map_by_id(): void
    {
        $tour = $this->makeTour();

        $this->withHeader('User-Agent', self::APP_UA)
            ->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee(route('map', ['tour' => $tour->id]).'"', false);
    }

    public function test_tour_pages_without_mapped_stops_have_no_interactive_map_link(): void
    {
        $tour = $this->makeTour(withStop: false);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertDontSee('View on Interactive Map');
    }

    public function test_the_map_opens_a_tour_from_a_slug_or_an_id(): void
    {
        $this->get(route('map'))
            ->assertOk()
            ->assertSee('this.openTourFromUrl();', false)
            ->assertSee('tours.find(t => window.trailMatchesKey(t, tourKey))', false);
    }

    public function test_premium_event_pages_show_the_interactive_map_link_below_the_map(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = Event::factory()->withRoute()->create(['title' => 'Light Up Night']);

        $this->actingAs($admin)->get(route('local-events.show', $event->slug).'?layout=premium')
            ->assertOk()
            ->assertSeeInOrder(['event-map-frame', 'View on Interactive Map'], false);
    }
}
