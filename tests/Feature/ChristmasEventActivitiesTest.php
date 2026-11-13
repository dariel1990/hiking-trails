<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChristmasEventActivitiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeEvent(): Tour
    {
        return Tour::create([
            'title' => 'Christmas in the Valley',
            'tour_type' => 'christmas',
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventPayload(array $stops): array
    {
        return [
            'title' => 'Christmas in the Valley',
            'tour_type' => 'christmas',
            'stops' => $stops,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function wagonRide(array $overrides = []): array
    {
        return array_merge([
            'latitude' => '54.7809',
            'longitude' => '-127.1698',
            'stop_label' => 'Wagon Rides',
            'icon' => '🐴',
            'description' => 'Horse-drawn wagon rides around downtown.',
            'estimated_visit_time' => '12:00 – 4:00 PM',
            'driving_notes' => 'Starts at Bugwood Beans',
        ], $overrides);
    }

    public function test_admin_can_create_event_with_icons_roaming_activities_and_photo(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->eventPayload([
                $this->wagonRide(['image' => UploadedFile::fake()->image('wagon.jpg', 800, 600)]),
                ['is_roaming' => '1', 'stop_label' => 'Carollers', 'icon' => '🎶', 'description' => 'Carollers walking around.'],
            ]))
            ->assertRedirect(route('admin.local-events.index'));

        $tour = Tour::where('title', 'Christmas in the Valley')->firstOrFail();
        [$wagon, $carollers] = $tour->stops->all();

        $this->assertSame('🐴', $wagon->icon);
        $this->assertSame('Horse-drawn wagon rides around downtown.', $wagon->description);
        $this->assertFalse($wagon->is_roaming);
        $this->assertNotNull($wagon->image);
        $this->assertStringStartsWith('tours/'.$tour->id.'/stops/', $wagon->image);
        Storage::disk('public')->assertExists($wagon->image);

        $this->assertTrue($carollers->is_roaming);
        $this->assertNull($carollers->latitude);
        $this->assertNull($carollers->coordinates());
        $this->assertSame('🎶', $carollers->icon);
    }

    public function test_existing_photo_is_kept_replaced_or_removed(): void
    {
        $admin = $this->makeAdmin();
        $tour = $this->makeEvent();
        Storage::disk('public')->put('tours/'.$tour->id.'/stops/old.webp', 'old');
        TourStop::create([
            'tour_id' => $tour->id, 'latitude' => 54.78, 'longitude' => -127.17,
            'stop_order' => 0, 'stop_label' => 'Wagon Rides', 'image' => 'tours/'.$tour->id.'/stops/old.webp',
        ]);
        $oldPath = 'tours/'.$tour->id.'/stops/old.webp';

        // Kept: the current photo comes back as existing_image.
        $this->actingAs($admin)->put(route('admin.local-events.update', $tour), $this->eventPayload([
            $this->wagonRide(['existing_image' => $oldPath]),
        ]))->assertRedirect(route('admin.local-events.index'));
        $this->assertSame($oldPath, $tour->fresh()->stops->first()->image);
        Storage::disk('public')->assertExists($oldPath);

        // Replaced: a new upload wins and the old file is deleted.
        $this->actingAs($admin)->put(route('admin.local-events.update', $tour), $this->eventPayload([
            $this->wagonRide(['existing_image' => $oldPath, 'image' => UploadedFile::fake()->image('new.jpg')]),
        ]))->assertRedirect(route('admin.local-events.index'));
        $newPath = $tour->fresh()->stops->first()->image;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);

        // Removed: remove_image clears it and deletes the file.
        $this->actingAs($admin)->put(route('admin.local-events.update', $tour), $this->eventPayload([
            $this->wagonRide(['existing_image' => $newPath, 'remove_image' => '1']),
        ]))->assertRedirect(route('admin.local-events.index'));
        $this->assertNull($tour->fresh()->stops->first()->image);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_photo_of_a_removed_stop_is_deleted(): void
    {
        $tour = $this->makeEvent();
        $path = 'tours/'.$tour->id.'/stops/gone.webp';
        Storage::disk('public')->put($path, 'x');
        TourStop::create(['tour_id' => $tour->id, 'is_roaming' => true, 'stop_order' => 0, 'stop_label' => 'Popcorn', 'image' => $path]);

        $this->actingAs($this->makeAdmin())->put(route('admin.local-events.update', $tour), $this->eventPayload([
            $this->wagonRide(),
        ]))->assertRedirect(route('admin.local-events.index'));

        Storage::disk('public')->assertMissing($path);
    }

    public function test_existing_image_pointing_outside_the_tour_is_ignored(): void
    {
        $tour = $this->makeEvent();
        Storage::disk('public')->put('feature-icons/someone-elses.png', 'x');

        $this->actingAs($this->makeAdmin())->put(route('admin.local-events.update', $tour), $this->eventPayload([
            $this->wagonRide(['existing_image' => 'feature-icons/someone-elses.png']),
        ]))->assertRedirect(route('admin.local-events.index'));

        $this->assertNull($tour->fresh()->stops->first()->image);
        Storage::disk('public')->assertExists('feature-icons/someone-elses.png');
    }

    public function test_roaming_activity_needs_a_name(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->eventPayload([
                ['is_roaming' => '1', 'stop_label' => '', 'icon' => '🍿'],
            ]))
            ->assertSessionHasErrors('stops.0');
    }

    public function test_icon_image_must_come_from_the_icon_gallery(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->eventPayload([
                $this->wagonRide(['icon_image' => '../../.env']),
            ]))
            ->assertSessionHasErrors('stops.0.icon_image');

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->eventPayload([
                $this->wagonRide(['icon_image' => 'feature-icons/horse.png']),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('feature-icons/horse.png', TourStop::first()->icon_image);
    }

    public function test_public_page_lists_activities_with_icons_and_roaming_section(): void
    {
        $tour = $this->makeEvent();
        TourStop::create([
            'tour_id' => $tour->id, 'latitude' => 54.7809, 'longitude' => -127.1698, 'stop_order' => 0,
            'stop_label' => 'Wagon Rides', 'icon' => '🐴', 'description' => 'Horse-drawn wagon rides.', 'estimated_visit_time' => '12–4 PM',
        ]);
        TourStop::create([
            'tour_id' => $tour->id, 'is_roaming' => true, 'stop_order' => 1,
            'stop_label' => 'Carollers', 'icon' => '🎶', 'description' => 'Carollers walking around.',
        ]);

        $this->get(route('local-events.show', $tour))
            ->assertOk()
            ->assertSee('Activities')
            ->assertSee('<strong class="text-white">2</strong> activities', false)
            ->assertSee('Around downtown')
            ->assertSee('Wagon Rides')
            ->assertSee('Horse-drawn wagon rides.')
            ->assertSee('🐴')
            ->assertSee('Carollers walking around.')
            ->assertSee('stop-card-0', false)
            ->assertDontSee('stop-card-1', false);
    }

    public function test_embed_of_event_keeps_the_frame_header(): void
    {
        $tour = $this->makeEvent();

        $this->get(route('local-events.embed', $tour))
            ->assertOk()
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self' https://xploresmithers.com https://www.xploresmithers.com");
    }

    public function test_deleting_a_gallery_icon_clears_it_from_stops(): void
    {
        Storage::disk('public')->put('feature-icons/horse.png', 'x');
        $tour = $this->makeEvent();
        $stop = TourStop::create([
            'tour_id' => $tour->id, 'latitude' => 54.78, 'longitude' => -127.17, 'stop_order' => 0,
            'stop_label' => 'Wagon Rides', 'icon_image' => 'feature-icons/horse.png',
        ]);

        $this->actingAs($this->makeAdmin())
            ->deleteJson(route('admin.trails.feature-icons.delete'), ['path' => 'feature-icons/horse.png'])
            ->assertJson(['deleted' => false, 'in_use' => 1]);

        $this->actingAs($this->makeAdmin())
            ->deleteJson(route('admin.trails.feature-icons.delete'), ['path' => 'feature-icons/horse.png', 'force' => true])
            ->assertJson(['deleted' => true]);

        $this->assertNull($stop->fresh()->icon_image);
    }

    public function test_deleting_the_tour_deletes_stop_photos(): void
    {
        $tour = $this->makeEvent();
        $path = 'tours/'.$tour->id.'/stops/photo.webp';
        Storage::disk('public')->put($path, 'x');
        TourStop::create(['tour_id' => $tour->id, 'is_roaming' => true, 'stop_order' => 0, 'stop_label' => 'Popcorn', 'image' => $path]);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.local-events.destroy', $tour))
            ->assertRedirect(route('admin.local-events.index'));

        Storage::disk('public')->assertMissing($path);
    }
}
