<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventStop;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapEventsTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_map_has_an_events_tab_after_recreational_sites(): void
    {
        $html = $this->get(route('map'))->assertOk()->getContent();

        $this->assertStringContainsString('data-location-filter="event"', $html);
        $this->assertStringContainsString('data-mobile-location-filter="event"', $html);
        $this->assertGreaterThan(
            strpos($html, 'data-location-filter="recreational_site"'),
            strpos($html, 'data-location-filter="event"'),
        );
    }

    public function test_it_lists_an_upcoming_event_with_its_stops(): void
    {
        $event = Event::factory()->christmas()->create(['title' => 'Christmas Truck Parade']);
        EventStop::factory()->for($event)->create(['stop_label' => 'Main Street', 'stop_order' => 0]);
        EventStop::factory()->for($event)->roaming()->create(['stop_label' => 'Carollers', 'stop_order' => 1]);

        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Christmas Truck Parade')
            ->assertJsonPath('0.theme', Event::THEME_CHRISTMAS)
            ->assertJsonPath('0.icon', '🎄')
            ->assertJsonPath('0.url', route('local-events.show', $event))
            ->assertJsonPath('0.map.stops.0.label', 'Main Street')
            ->assertJsonPath('0.map.stops.0.number', 1)
            ->assertJsonPath('0.map.stops.0.coords', [54.7804, -127.1745])
            ->assertJsonPath('0.map.stops.1.label', 'Carollers')
            ->assertJsonPath('0.map.stops.1.coords', null);
    }

    public function test_pinned_stops_are_numbered_in_order_skipping_roaming_activities(): void
    {
        $event = Event::factory()->create();
        EventStop::factory()->for($event)->create(['stop_order' => 0]);
        EventStop::factory()->for($event)->roaming()->create(['stop_order' => 1]);
        EventStop::factory()->for($event)->create(['stop_order' => 2]);
        EventStop::factory()->for($event)->create(['stop_order' => 3]);

        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonPath('0.map.stops.0.number', 1)
            ->assertJsonPath('0.map.stops.1.number', null)
            ->assertJsonPath('0.map.stops.2.number', 2)
            ->assertJsonPath('0.map.stops.3.number', 3);
    }

    public function test_the_map_colour_follows_the_event_accent_settings(): void
    {
        Setting::set('event_accent_color', '#1D4ED8');
        Setting::set('event_christmas_accent_color', '#047857');
        Event::factory()->withRoute()->create(['event_date' => now()->addDays(2)->toDateString()]);
        Event::factory()->christmas()->withRoute()->create(['event_date' => now()->addDays(3)->toDateString()]);

        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonPath('0.accent_color', '#1D4ED8')
            ->assertJsonPath('1.accent_color', '#047857');
    }

    public function test_the_map_colour_falls_back_to_the_default_accent(): void
    {
        Event::factory()->withRoute()->create();

        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonPath('0.accent_color', config('settings.definitions.event_accent_color.default'));
    }

    public function test_it_lists_an_event_that_only_has_a_route(): void
    {
        Event::factory()->withRoute()->create(['description' => "Floats leave Main Street.\nBring a toy."]);

        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.map.route.0', [-127.1745, 54.7804])
            ->assertJsonPath('0.description', "Floats leave Main Street.\nBring a toy.")
            ->assertJsonPath('0.route_distance_km', 1.2);
    }

    public function test_it_leaves_out_events_with_nothing_to_show_on_the_map(): void
    {
        Event::factory()->imported()->create();
        $roamingOnly = Event::factory()->create();
        EventStop::factory()->for($roamingOnly)->roaming()->create();

        $this->getJson('/api/events')->assertOk()->assertJsonCount(0);
    }

    public function test_it_leaves_out_inactive_and_past_events(): void
    {
        Event::factory()->inactive()->withRoute()->create();
        Event::factory()->past()->withRoute()->create();

        $this->getJson('/api/events')->assertOk()->assertJsonCount(0);
    }

    public function test_a_multi_day_event_stays_listed_until_its_end_date(): void
    {
        Event::factory()->withRoute()->create([
            'event_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
        ]);

        $this->getJson('/api/events')->assertOk()->assertJsonCount(1);
    }

    public function test_events_are_ordered_by_date(): void
    {
        Event::factory()->withRoute()->create(['title' => 'Later', 'event_date' => now()->addDays(20)->toDateString()]);
        Event::factory()->withRoute()->create(['title' => 'Sooner', 'event_date' => now()->addDays(2)->toDateString()]);

        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonPath('0.title', 'Sooner')
            ->assertJsonPath('1.title', 'Later');
    }
}
