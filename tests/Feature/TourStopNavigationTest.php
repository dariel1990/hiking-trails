<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourStop;
use App\Support\GeoDistance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourStopNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<array<string, mixed>>  $stops
     */
    private function makeTour(array $stops): Tour
    {
        $tour = Tour::create([
            'title' => 'Murals Walk',
            'tour_type' => 'heritage',
            'is_active' => true,
        ]);

        foreach ($stops as $order => $stop) {
            TourStop::create(array_merge([
                'tour_id' => $tour->id,
                'stop_order' => $order + 1,
                'stop_label' => 'Stop '.($order + 1),
            ], $stop));
        }

        return $tour->load('stops');
    }

    public function test_geo_distance_matches_known_distance(): void
    {
        // ~1.11 km per 0.01° of latitude
        $km = GeoDistance::kilometersBetween([54.78, -127.17], [54.79, -127.17]);

        $this->assertEqualsWithDelta(1.112, $km, 0.01);
        $this->assertSame(0.0, GeoDistance::kilometersBetween([54.78, -127.17], [54.78, -127.17]));
    }

    public function test_short_legs_are_walks_and_long_legs_are_drives(): void
    {
        $tour = $this->makeTour([
            ['latitude' => 54.7800, 'longitude' => -127.1700],
            ['latitude' => 54.7820, 'longitude' => -127.1700],
            ['latitude' => 54.8800, 'longitude' => -127.1700],
        ]);

        $legs = array_values($tour->stopLegs());

        $this->assertCount(2, $legs);
        $this->assertSame('walk', $legs[0]['mode']);
        $this->assertEqualsWithDelta(0.278, $legs[0]['distance_km'], 0.01);
        $this->assertSame(3, $legs[0]['minutes']);
        $this->assertSame('drive', $legs[1]['mode']);
        $this->assertFalse($tour->isWalkable());
    }

    public function test_roaming_and_unlocated_stops_are_skipped_in_legs(): void
    {
        $tour = $this->makeTour([
            ['latitude' => 54.7800, 'longitude' => -127.1700],
            ['is_roaming' => true],
            ['latitude' => 54.7810, 'longitude' => -127.1700],
        ]);

        $legs = $tour->stopLegs();

        $this->assertCount(1, $legs);
        $this->assertArrayHasKey(0, $legs);
        $this->assertTrue($tour->isWalkable());
    }

    public function test_fewer_than_two_located_stops_have_no_legs(): void
    {
        $tour = $this->makeTour([
            ['latitude' => 54.78, 'longitude' => -127.17],
            ['is_roaming' => true],
        ]);

        $this->assertSame([], $tour->stopLegs());
        $this->assertFalse($tour->isWalkable());
    }

    public function test_tour_page_shows_estimated_legs_and_steppers_without_a_street_route(): void
    {
        $tour = $this->makeTour([
            ['latitude' => 54.7800, 'longitude' => -127.1700],
            ['latitude' => 54.7820, 'longitude' => -127.1700],
        ]);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('~280 m · 3 min walk to next stop')
            ->assertDontSee('Open full route in Google Maps')
            ->assertSee('id="tour-stepper"', false)
            ->assertSee('Next stop')
            ->assertDontSee('stepTourStop(1, 0)', false);
    }

    public function test_single_stop_tour_hides_map_stepper(): void
    {
        $tour = $this->makeTour([
            ['latitude' => 54.78, 'longitude' => -127.17],
        ]);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertDontSee('id="tour-stepper"', false)
            ->assertDontSee('to next stop');
    }
}
