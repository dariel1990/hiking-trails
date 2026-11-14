<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventStop;
use App\Models\Tour;
use App\Models\TourStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Christmas parade is an event with the Christmas theme, a drawn route
 * and pinned stops, reachable at /christmas-parade and embeddable on
 * xploresmithers.com.
 */
class ChristmasParadeTourTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeParade(array $overrides = []): Event
    {
        return Event::factory()->christmas()->create(array_merge([
            'title' => 'Christmas Truck Parade',
            'slug' => 'christmas-truck-parade',
            'route_coordinates' => [[-127.1700, 54.7800], [-127.1680, 54.7810], [-127.1660, 54.7822]],
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function paradePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Christmas Truck Parade',
            'event_date' => now()->addDays(10)->toDateString(),
            'event_time' => '18:00',
            'theme' => Event::THEME_CHRISTMAS,
            'is_active' => '1',
        ], $overrides);
    }

    public function test_christmas_parade_page_shows_the_active_parade_event(): void
    {
        $this->makeParade();

        $this->get(route('christmas-parade'))
            ->assertOk()
            ->assertSee('Christmas Truck Parade')
            ->assertSee('data-theme="christmas"', false)
            ->assertSee('event-title-festive', false)
            ->assertSee('id="tour-map"', false);
    }

    public function test_christmas_parade_page_returns_404_without_an_active_parade(): void
    {
        $this->makeParade(['is_active' => false]);

        $this->get(route('christmas-parade'))->assertNotFound();
        $this->get(route('christmas-parade.embed'))->assertNotFound();
    }

    public function test_christmas_parade_page_resolves_by_slug_not_by_newest_christmas_event(): void
    {
        $this->makeParade();
        Event::factory()->christmas()->create(['title' => 'Christmas in the Valley']);

        $this->get(route('christmas-parade'))
            ->assertOk()
            ->assertSee('Christmas Truck Parade')
            ->assertDontSee('Christmas in the Valley');
    }

    public function test_christmas_parade_slug_is_configurable(): void
    {
        config(['app.christmas_parade_slug' => 'santa-parade-2026']);
        $this->makeParade(['title' => 'Santa Parade 2026', 'slug' => 'santa-parade-2026']);

        $this->get(route('christmas-parade'))
            ->assertOk()
            ->assertSee('Santa Parade 2026');
    }

    public function test_embed_allows_framing_from_xploresmithers_only(): void
    {
        $this->makeParade();

        $this->get(route('christmas-parade.embed'))
            ->assertOk()
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self' https://xploresmithers.com https://www.xploresmithers.com")
            ->assertHeaderMissing('X-Frame-Options')
            ->assertSee('View full map')
            ->assertSee(route('christmas-parade'), false)
            ->assertDontSee('All Events');
    }

    public function test_embed_frame_ancestors_can_be_configured(): void
    {
        config(['app.embed_frame_ancestors' => 'https://staging.example.com']);
        $parade = $this->makeParade();

        $this->get(route('local-events.embed', $parade->slug))
            ->assertOk()
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self' https://staging.example.com")
            ->assertSee(route('local-events.show', $parade->slug), false);
    }

    public function test_standard_event_and_regular_tour_do_not_get_the_christmas_theme(): void
    {
        $event = Event::factory()->withRoute()->create();
        $tour = Tour::create(['title' => 'Scenic Drive', 'tour_type' => 'scenic', 'is_active' => true]);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('data-theme="standard"', false)
            ->assertDontSee('event-title-festive', false);
        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('Tour Stops')
            ->assertDontSee('christmas-snow', false);
    }

    public function test_admin_can_create_parade_with_pinned_stops_and_drawn_route(): void
    {
        $route = [[-127.1704, 54.7802], [-127.1698, 54.7809], [-127.1663, 54.7822]];

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload([
                'slug' => 'christmas-truck-parade',
                'route_coordinates' => json_encode($route),
                'route_distance_km' => '1.4',
                'stops' => [
                    ['latitude' => '54.7802017', 'longitude' => '-127.1704670', 'stop_label' => 'Main St & 3rd Ave', 'time_label' => '6:00 PM', 'notes' => 'Start line'],
                    ['latitude' => '54.7822196', 'longitude' => '-127.1662904', 'stop_label' => 'Central Park', 'time_label' => '6:20 PM'],
                ],
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.local-events.index'));

        $event = Event::where('slug', 'christmas-truck-parade')->firstOrFail();
        $this->assertTrue($event->isChristmasEvent());
        $this->assertEquals($route, $event->route_coordinates);
        $this->assertEquals(1.4, (float) $event->route_distance_km);

        $stops = $event->stops;
        $this->assertCount(2, $stops);
        $this->assertSame('Main St & 3rd Ave', $stops[0]->displayName());
        $this->assertEqualsWithDelta(54.7802017, $stops[0]->coordinates()[0], 0.0000001);
        $this->assertEqualsWithDelta(-127.1704670, $stops[0]->coordinates()[1], 0.0000001);
        $this->assertSame('6:00 PM', $stops[0]->time_label);
        $this->assertSame('Start line', $stops[0]->notes);
        $this->assertSame(1, $stops[1]->stop_order);

        $this->get(route('christmas-parade'))->assertOk()->assertSee('Central Park');
    }

    public function test_activity_requires_a_name(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload([
                'stops' => [['latitude' => '54.78', 'longitude' => '-127.17', 'stop_label' => '']],
            ]))
            ->assertSessionHasErrors('stops.0');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_activity_without_pin_or_roaming_flag_is_rejected(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload([
                'stops' => [['stop_label' => 'Nowhere']],
            ]))
            ->assertSessionHasErrors('stops.0');
    }

    public function test_pinned_activity_coordinates_must_be_valid(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload([
                'stops' => [['latitude' => '123', 'longitude' => '-127.17', 'stop_label' => 'Bad pin']],
            ]))
            ->assertSessionHasErrors('stops.0.latitude');
    }

    public function test_clearing_the_route_in_the_editor_removes_it(): void
    {
        $parade = $this->makeParade(['route_distance_km' => 1.2]);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $parade), $this->paradePayload(['route_coordinates' => '[]']))
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertNull($parade->fresh()->route_coordinates);
        $this->assertNull($parade->fresh()->route_distance_km);
    }

    public function test_blank_route_field_keeps_the_existing_route(): void
    {
        $parade = $this->makeParade();
        $original = $parade->route_coordinates;

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $parade), $this->paradePayload(['route_coordinates' => '']))
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertEquals($original, $parade->fresh()->route_coordinates);
        $this->assertSame('christmas-truck-parade', $parade->fresh()->slug, 'a blank slug keeps the existing one');
    }

    public function test_pinned_stops_render_on_the_public_page_with_directions_link(): void
    {
        $parade = $this->makeParade();
        EventStop::factory()->for($parade)->create([
            'latitude' => 54.7802017,
            'longitude' => -127.1704670,
            'stop_label' => 'Main St & 3rd Ave',
            'time_label' => '6:00 PM',
        ]);

        $this->get(route('christmas-parade'))
            ->assertOk()
            ->assertSee('Main St &amp; 3rd Ave', false)
            ->assertSee('6:00 PM')
            ->assertSee('https://www.google.com/maps/dir/?api=1&destination=54.7802017,-127.170467', false);
    }

    public function test_admin_edit_page_loads_with_pinned_stop(): void
    {
        $parade = $this->makeParade();
        EventStop::factory()->for($parade)->create(['stop_label' => 'Corner']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.edit', $parade))
            ->assertOk()
            ->assertSee('Pin activity on map')
            ->assertSee('&quot;item_type&quot;:&quot;pinned&quot;', false);
    }

    public function test_regular_tours_can_still_have_pinned_stops_and_a_drawn_route(): void
    {
        $route = [[-127.1704, 54.7802], [-127.1663, 54.7822]];

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.tours.store'), [
                'title' => 'Downtown Heritage Walk',
                'tour_type' => 'heritage',
                'driving_route_coordinates' => json_encode($route),
                'stops' => [['latitude' => '54.7802', 'longitude' => '-127.1704', 'stop_label' => 'Old Courthouse']],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.tours.index'));

        $tour = Tour::where('title', 'Downtown Heritage Walk')->firstOrFail();
        $this->assertEquals($route, $tour->driving_route_coordinates);
        $this->assertTrue($tour->stops->first()->isPinned());

        $this->get(route('tours.show', $tour))->assertOk()->assertSee('Old Courthouse');
        $this->get(route('tours.embed', $tour))->assertOk()->assertSee('Downtown Heritage Walk');
        $this->assertSame(1, TourStop::count());
    }
}
