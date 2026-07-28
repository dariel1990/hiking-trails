<?php

namespace Tests\Feature\Admin;

use App\Models\Facility;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourStopFacilityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function facility(array $overrides = []): Facility
    {
        return Facility::create(array_merge([
            'facility_type' => 'first_nation',
            'name' => 'Witset First Nation',
            'latitude' => 54.90,
            'longitude' => -127.68,
            'is_active' => true,
        ], $overrides));
    }

    public function test_first_nation_is_a_selectable_facility_type(): void
    {
        $this->assertArrayHasKey('first_nation', Facility::getFacilityTypes());
    }

    public function test_admin_can_create_a_facility_of_type_first_nation(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.facilities.store'), [
                'facility_type' => 'first_nation',
                'name' => 'Witset First Nation',
                'latitude' => 54.90,
                'longitude' => -127.68,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.facilities.index'));
        $this->assertDatabaseHas('facilities', [
            'name' => 'Witset First Nation',
            'facility_type' => 'first_nation',
        ]);
    }

    public function test_admin_can_create_a_tour_with_a_facility_only_stop(): void
    {
        $facility = $this->facility();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.tours.store'), [
                'title' => 'Heritage Tour',
                'tour_type' => 'heritage',
                'is_active' => '1',
                'stops' => [
                    [
                        'facility_id' => $facility->id,
                        'stop_label' => 'Welcome Stop',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.tours.index'));

        $tour = Tour::where('title', 'Heritage Tour')->firstOrFail();
        $stop = $tour->stops->firstOrFail();

        $this->assertSame($facility->id, $stop->facility_id);
        $this->assertNull($stop->trail_id);
        $this->assertNull($stop->trail_feature_id);
        $this->assertSame('Welcome Stop', $stop->stop_label);
    }

    public function test_a_stop_without_a_trail_or_facility_is_rejected(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.tours.store'), [
                'title' => 'Empty Stop Tour',
                'tour_type' => 'heritage',
                'is_active' => '1',
                'stops' => [
                    ['stop_label' => 'Nowhere'],
                ],
            ]);

        $response->assertSessionHasErrors('stops.0');
        $this->assertDatabaseMissing('tours', ['title' => 'Empty Stop Tour']);
    }
}
