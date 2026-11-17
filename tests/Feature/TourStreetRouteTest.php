<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourStop;
use App\Services\TourRouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Street-following routes drawn through a tour's stops when no route was saved.
 */
class TourStreetRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.mapbox.access_token' => 'pk.test-token']);
        Http::preventStrayRequests();
    }

    /**
     * @param  list<array{0: float, 1: float}>  $points  [lat, lng]
     */
    private function makeTour(array $points, array $overrides = []): Tour
    {
        $tour = Tour::create(array_merge([
            'title' => 'Murals Walk',
            'tour_type' => 'heritage',
            'is_active' => true,
        ], $overrides));

        foreach ($points as $order => [$lat, $lng]) {
            TourStop::create([
                'tour_id' => $tour->id,
                'stop_order' => $order,
                'stop_label' => 'Stop '.($order + 1),
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        }

        return $tour->load('stops');
    }

    private function fakeMapbox(int $legs = 2): void
    {
        Http::fake([
            'api.mapbox.com/directions/*' => Http::response([
                'code' => 'Ok',
                'routes' => [[
                    'distance' => 1465.2,
                    'geometry' => ['coordinates' => [[-127.17, 54.78], [-127.171, 54.781], [-127.172, 54.782]]],
                    'legs' => array_fill(0, $legs, ['distance' => 342.4, 'duration' => 252]),
                ]],
            ]),
        ]);
    }

    /**
     * @return list<array{0: float, 1: float}>
     */
    private function walkablePoints(): array
    {
        return [[54.7800, -127.1700], [54.7810, -127.1710], [54.7820, -127.1720]];
    }

    public function test_walkable_tour_gets_a_walking_route_from_mapbox(): void
    {
        $this->fakeMapbox();
        $tour = $this->makeTour($this->walkablePoints());

        $route = app(TourRouteService::class)->forTour($tour);

        $this->assertSame('walking', $route['profile']);
        $this->assertSame(1.47, $route['distance_km']);
        $this->assertCount(3, $route['coordinates']);
        $this->assertSame(['distance_km' => 0.3424, 'minutes' => 4], $route['legs'][0]);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/mapbox/walking/-127.17,54.78;-127.171,54.781;-127.172,54.782')
            && $request['geometries'] === 'geojson');
    }

    public function test_spread_out_tour_gets_a_driving_route(): void
    {
        $this->fakeMapbox(1);
        $tour = $this->makeTour([[54.78, -127.17], [54.90, -127.17]]);

        $this->assertSame('driving', app(TourRouteService::class)->forTour($tour)['profile']);
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/mapbox/driving/'));
    }

    public function test_route_is_cached_and_refetched_when_stops_move(): void
    {
        $this->fakeMapbox();
        $tour = $this->makeTour($this->walkablePoints());
        $service = app(TourRouteService::class);

        $service->forTour($tour);
        $service->forTour($tour);
        Http::assertSentCount(1);

        $tour->stops->first()->update(['latitude' => 54.7801]);
        $service->forTour($tour->fresh('stops'));
        Http::assertSentCount(2);
    }

    public function test_failed_lookup_returns_null_and_is_not_retried_immediately(): void
    {
        Http::fake(['api.mapbox.com/*' => Http::response(['code' => 'NoRoute'], 422)]);
        $tour = $this->makeTour($this->walkablePoints());
        $service = app(TourRouteService::class);

        $this->assertNull($service->forTour($tour));
        $this->assertNull($service->forTour($tour));
        Http::assertSentCount(1);
    }

    public function test_no_lookup_without_token_or_enough_stops(): void
    {
        Http::fake();
        $service = app(TourRouteService::class);

        $this->assertNull($service->forTour($this->makeTour([[54.78, -127.17]])));

        config(['services.mapbox.access_token' => null]);
        $this->assertNull($service->forTour($this->makeTour($this->walkablePoints())));

        Http::assertNothingSent();
    }

    public function test_too_many_stops_for_one_request_skips_the_lookup(): void
    {
        Http::fake();
        $points = array_map(fn ($i) => [54.78 + $i * 0.0005, -127.17], range(0, TourRouteService::MAX_WAYPOINTS));

        $this->assertNull(app(TourRouteService::class)->forTour($this->makeTour($points)));
        Http::assertNothingSent();
    }

    public function test_route_legs_replace_estimates_on_stop_legs(): void
    {
        $this->fakeMapbox();
        $tour = $this->makeTour($this->walkablePoints());

        $legs = $tour->stopLegs(app(TourRouteService::class)->forTour($tour));

        $this->assertSame(['distance_km' => 0.3424, 'minutes' => 4, 'mode' => 'walk'], $legs[0]);
        $this->assertCount(2, $legs);
    }

    public function test_tour_page_draws_the_street_route_and_shows_real_legs(): void
    {
        $this->fakeMapbox();
        $tour = $this->makeTour($this->walkablePoints());

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('const drivingRoute = [[-127.17,54.78],[-127.171,54.781],[-127.172,54.782]];', false)
            ->assertSee('340 m · 4 min walk to next stop')
            ->assertDontSee('~340 m', false)
            ->assertSee('1.5 km')
            ->assertDontSee('google.com/maps/dir/?api=1&amp;origin', false);
    }

    public function test_saved_admin_route_wins_and_skips_the_lookup(): void
    {
        Http::fake();
        $tour = $this->makeTour($this->walkablePoints(), [
            'driving_route_coordinates' => [[-127.0, 54.0], [-127.1, 54.1]],
        ]);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('const drivingRoute = [[-127,54],[-127.1,54.1]];', false);

        Http::assertNothingSent();
    }

    public function test_embed_draws_the_street_route(): void
    {
        $this->fakeMapbox();
        $tour = $this->makeTour($this->walkablePoints());

        $this->get(route('tours.embed', $tour))
            ->assertOk()
            ->assertSee('const drivingRoute = [[-127.17,54.78],', false);
    }
}
