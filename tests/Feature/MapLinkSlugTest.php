<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
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
}
