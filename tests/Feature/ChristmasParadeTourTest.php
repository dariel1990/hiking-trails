<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChristmasParadeTourTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeParade(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'title' => 'Christmas Truck Parade',
            'tour_type' => 'christmas',
            'is_active' => true,
            'driving_route_coordinates' => [[-127.1700, 54.7800], [-127.1680, 54.7810], [-127.1660, 54.7822]],
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function paradePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Christmas Truck Parade',
            'tour_type' => 'christmas',
        ], $overrides);
    }

    public function test_christmas_parade_page_shows_the_active_christmas_tour(): void
    {
        $this->makeParade();

        $this->get(route('christmas-parade'))
            ->assertOk()
            ->assertSee('Christmas Truck Parade')
            ->assertSee('Route Stops')
            ->assertSee('christmas-snow', false);
    }

    public function test_christmas_parade_page_returns_404_without_an_active_christmas_tour(): void
    {
        $this->makeParade(['is_active' => false]);
        Tour::create(['title' => 'Scenic Drive', 'tour_type' => 'scenic', 'is_active' => true]);

        $this->get(route('christmas-parade'))->assertNotFound();
        $this->get(route('christmas-parade.embed'))->assertNotFound();
    }

    public function test_christmas_parade_page_resolves_by_slug_not_by_newest_christmas_tour(): void
    {
        $this->makeParade();
        $this->makeParade(['title' => 'Christmas in the Valley', 'driving_route_coordinates' => null]);

        $this->get(route('christmas-parade'))
            ->assertOk()
            ->assertSee('Christmas Truck Parade')
            ->assertDontSee('Christmas in the Valley');
    }

    public function test_christmas_parade_slug_is_configurable(): void
    {
        config(['app.christmas_parade_slug' => 'santa-parade-2026']);
        $this->makeParade(['title' => 'Santa Parade 2026']);

        $this->get(route('christmas-parade'))
            ->assertOk()
            ->assertSee('Santa Parade 2026');
    }

    public function test_embed_allows_framing_from_xploresmithers_only(): void
    {
        $this->makeParade();

        $response = $this->get(route('christmas-parade.embed'));

        $response->assertOk()
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self' https://xploresmithers.com https://www.xploresmithers.com")
            ->assertHeaderMissing('X-Frame-Options')
            ->assertSee('View full map')
            ->assertSee(route('christmas-parade'), false)
            ->assertDontSee('All Tours');
    }

    public function test_embed_frame_ancestors_can_be_configured(): void
    {
        config(['app.embed_frame_ancestors' => 'https://staging.example.com']);
        $tour = $this->makeParade();

        $this->get(route('local-events.embed', $tour))
            ->assertOk()
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self' https://staging.example.com")
            ->assertSee(route('local-events.show', $tour), false);
    }

    public function test_embed_of_inactive_tour_returns_404(): void
    {
        $tour = $this->makeParade(['is_active' => false]);

        $this->get(route('local-events.embed', $tour))->assertNotFound();
    }

    public function test_regular_tour_page_does_not_get_the_christmas_theme(): void
    {
        $tour = Tour::create(['title' => 'Scenic Drive', 'tour_type' => 'scenic', 'is_active' => true]);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('Tour Stops')
            ->assertDontSee('christmas-snow', false);
    }

    public function test_admin_can_create_christmas_tour_with_pinned_stops_and_drawn_route(): void
    {
        $route = [[-127.1704, 54.7802], [-127.1698, 54.7809], [-127.1663, 54.7822]];

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload([
                'driving_route_coordinates' => json_encode($route),
                'stops' => [
                    ['latitude' => '54.7802017', 'longitude' => '-127.1704670', 'stop_label' => 'Main St & 3rd Ave', 'estimated_visit_time' => '6:00 PM', 'driving_notes' => 'Start line'],
                    ['latitude' => '54.7822196', 'longitude' => '-127.1662904', 'stop_label' => 'Central Park', 'estimated_visit_time' => '6:20 PM'],
                ],
            ]))
            ->assertRedirect(route('admin.local-events.index'));

        $tour = Tour::where('title', 'Christmas Truck Parade')->firstOrFail();
        $this->assertSame('christmas', $tour->tour_type);
        $this->assertEquals($route, $tour->driving_route_coordinates);

        $stops = $tour->stops;
        $this->assertCount(2, $stops);
        $this->assertTrue($stops[0]->isPinned());
        $this->assertSame('Main St & 3rd Ave', $stops[0]->displayName());
        $this->assertEqualsWithDelta(54.7802017, $stops[0]->coordinates()[0], 0.0000001);
        $this->assertEqualsWithDelta(-127.1704670, $stops[0]->coordinates()[1], 0.0000001);
        $this->assertSame('6:00 PM', $stops[0]->estimated_visit_time);
        $this->assertSame(1, $stops[1]->stop_order);
    }

    public function test_pinned_stop_requires_a_name(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload([
                'stops' => [
                    ['latitude' => '54.78', 'longitude' => '-127.17', 'stop_label' => ''],
                ],
            ]))
            ->assertSessionHasErrors('stops.0');

        $this->assertDatabaseCount('tours', 0);
    }

    public function test_stop_without_link_or_pin_is_rejected(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload([
                'stops' => [['stop_label' => 'Nowhere']],
            ]))
            ->assertSessionHasErrors('stops.0');
    }

    public function test_pinned_stop_coordinates_must_be_valid(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload([
                'stops' => [
                    ['latitude' => '123', 'longitude' => '-127.17', 'stop_label' => 'Bad pin'],
                ],
            ]))
            ->assertSessionHasErrors('stops.0.latitude');
    }

    public function test_unknown_tour_type_is_rejected(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->paradePayload(['tour_type' => 'easter']))
            ->assertSessionHasErrors('tour_type');
    }

    public function test_clearing_the_route_in_the_editor_removes_it(): void
    {
        $tour = $this->makeParade();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $tour), $this->paradePayload([
                'driving_route_coordinates' => '[]',
            ]))
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertNull($tour->fresh()->driving_route_coordinates);
    }

    public function test_blank_route_field_keeps_the_existing_route(): void
    {
        $tour = $this->makeParade();
        $original = $tour->driving_route_coordinates;

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $tour), $this->paradePayload([
                'driving_route_coordinates' => '',
            ]))
            ->assertRedirect(route('admin.local-events.index'));

        $this->assertEquals($original, $tour->fresh()->driving_route_coordinates);
    }

    public function test_pinned_stops_render_on_the_public_page_with_directions_link(): void
    {
        $tour = $this->makeParade();
        TourStop::create([
            'tour_id' => $tour->id,
            'latitude' => 54.7802017,
            'longitude' => -127.1704670,
            'stop_order' => 0,
            'stop_label' => 'Main St & 3rd Ave',
            'estimated_visit_time' => '6:00 PM',
        ]);

        $this->get(route('christmas-parade'))
            ->assertOk()
            ->assertSee('Main St &amp; 3rd Ave', false)
            ->assertSee('6:00 PM')
            ->assertSee('https://www.google.com/maps/dir/?api=1&destination=54.7802017,-127.170467', false);
    }

    public function test_admin_edit_page_loads_with_pinned_stop(): void
    {
        $tour = $this->makeParade();
        TourStop::create([
            'tour_id' => $tour->id,
            'latitude' => 54.78,
            'longitude' => -127.17,
            'stop_order' => 0,
            'stop_label' => 'Corner',
        ]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.edit', $tour))
            ->assertOk()
            ->assertSee('Pin stop on map')
            ->assertSee('&quot;item_type&quot;:&quot;pinned&quot;', false);
    }
}
