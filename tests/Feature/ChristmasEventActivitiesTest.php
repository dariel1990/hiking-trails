<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Event activities: icons, photos, roaming activities, and cleanup of their files.
 */
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

    private function makeEvent(): Event
    {
        return Event::factory()->christmas()->create(['title' => 'Christmas in the Valley']);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventPayload(array $stops, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Christmas in the Valley',
            'event_date' => now()->addDays(10)->toDateString(),
            'theme' => Event::THEME_CHRISTMAS,
            'is_active' => '1',
            'stops' => $stops,
        ], $overrides);
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
            'time_label' => '12:00 – 4:00 PM',
            'notes' => 'Starts at Bugwood Beans',
        ], $overrides);
    }

    public function test_admin_can_create_event_with_icons_roaming_activities_and_photo(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->eventPayload([
                $this->wagonRide(['image' => UploadedFile::fake()->image('wagon.jpg', 800, 600)]),
                ['is_roaming' => '1', 'stop_label' => 'Carollers', 'icon' => '🎶', 'description' => 'Carollers walking around.'],
            ], ['cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 800)]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.local-events.index'));

        $event = Event::where('title', 'Christmas in the Valley')->firstOrFail();
        [$wagon, $carollers] = $event->stops->all();

        $this->assertStringStartsWith('events/'.$event->id.'/', $event->cover_image);
        Storage::disk('public')->assertExists($event->cover_image);

        $this->assertSame('🐴', $wagon->icon);
        $this->assertSame('Horse-drawn wagon rides around downtown.', $wagon->description);
        $this->assertFalse($wagon->is_roaming);
        $this->assertStringStartsWith('events/'.$event->id.'/stops/', $wagon->image);
        Storage::disk('public')->assertExists($wagon->image);

        $this->assertTrue($carollers->is_roaming);
        $this->assertNull($carollers->latitude);
        $this->assertNull($carollers->coordinates());
        $this->assertSame('🎶', $carollers->icon);
    }

    public function test_existing_photo_is_kept_replaced_or_removed(): void
    {
        $admin = $this->makeAdmin();
        $event = $this->makeEvent();
        $oldPath = 'events/'.$event->id.'/stops/old.webp';
        Storage::disk('public')->put($oldPath, 'old');
        EventStop::factory()->for($event)->create(['stop_label' => 'Wagon Rides', 'image' => $oldPath]);

        // Kept: the current photo comes back as existing_image.
        $this->actingAs($admin)->put(route('admin.local-events.update', $event), $this->eventPayload([
            $this->wagonRide(['existing_image' => $oldPath]),
        ]))->assertRedirect(route('admin.local-events.index'));
        $this->assertSame($oldPath, $event->fresh()->stops->first()->image);
        Storage::disk('public')->assertExists($oldPath);

        // Replaced: a new upload wins and the old file is deleted.
        $this->actingAs($admin)->put(route('admin.local-events.update', $event), $this->eventPayload([
            $this->wagonRide(['existing_image' => $oldPath, 'image' => UploadedFile::fake()->image('new.jpg')]),
        ]))->assertRedirect(route('admin.local-events.index'));
        $newPath = $event->fresh()->stops->first()->image;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);

        // Removed: remove_image clears it and deletes the file.
        $this->actingAs($admin)->put(route('admin.local-events.update', $event), $this->eventPayload([
            $this->wagonRide(['existing_image' => $newPath, 'remove_image' => '1']),
        ]))->assertRedirect(route('admin.local-events.index'));
        $this->assertNull($event->fresh()->stops->first()->image);
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_photo_of_a_removed_activity_is_deleted(): void
    {
        $event = $this->makeEvent();
        $path = 'events/'.$event->id.'/stops/gone.webp';
        Storage::disk('public')->put($path, 'x');
        EventStop::factory()->for($event)->roaming()->create(['stop_label' => 'Popcorn', 'image' => $path]);

        $this->actingAs($this->makeAdmin())->put(route('admin.local-events.update', $event), $this->eventPayload([
            $this->wagonRide(),
        ]))->assertRedirect(route('admin.local-events.index'));

        Storage::disk('public')->assertMissing($path);
    }

    public function test_existing_image_pointing_outside_the_event_is_ignored(): void
    {
        $event = $this->makeEvent();
        Storage::disk('public')->put('feature-icons/someone-elses.png', 'x');

        $this->actingAs($this->makeAdmin())->put(route('admin.local-events.update', $event), $this->eventPayload([
            $this->wagonRide(['existing_image' => 'feature-icons/someone-elses.png']),
        ]))->assertRedirect(route('admin.local-events.index'));

        $this->assertNull($event->fresh()->stops->first()->image);
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

    public function test_icon_images_must_come_from_the_icon_gallery(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->eventPayload([
                $this->wagonRide(['icon_image' => '../../.env']),
            ], ['icon_image' => 'secrets/../../.env']))
            ->assertSessionHasErrors(['stops.0.icon_image', 'icon_image']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->eventPayload([
                $this->wagonRide(['icon_image' => 'feature-icons/horse.png']),
            ], ['icon_image' => 'feature-icons/tree.png']))
            ->assertSessionHasNoErrors();

        $this->assertSame('feature-icons/horse.png', EventStop::first()->icon_image);
        $this->assertSame('feature-icons/tree.png', Event::first()->icon_image);
    }

    public function test_public_page_lists_activities_with_icons_and_roaming_section(): void
    {
        $event = $this->makeEvent();
        EventStop::factory()->for($event)->create([
            'latitude' => 54.7809, 'longitude' => -127.1698, 'stop_order' => 0,
            'stop_label' => 'Wagon Rides', 'icon' => '🐴', 'description' => 'Horse-drawn wagon rides.', 'time_label' => '12–4 PM',
        ]);
        EventStop::factory()->for($event)->roaming()->create([
            'stop_order' => 1, 'stop_label' => 'Carollers', 'icon' => '🎶', 'description' => 'Carollers walking around.',
        ]);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('Activities')
            ->assertSee('2 activities')
            ->assertSee('Around downtown')
            ->assertSee('Wagon Rides')
            ->assertSee('Horse-drawn wagon rides.')
            ->assertSee('12–4 PM')
            ->assertSee('🐴')
            ->assertSee('Carollers walking around.')
            ->assertSee('stop-card-0', false)
            ->assertDontSee('stop-card-1', false);
    }

    public function test_event_with_only_roaming_activities_has_no_map(): void
    {
        $event = $this->makeEvent();
        EventStop::factory()->for($event)->roaming()->create(['stop_label' => 'Carollers']);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('Carollers')
            ->assertDontSee('id="tour-map"', false);
    }

    public function test_deleting_a_gallery_icon_clears_it_from_events_and_activities(): void
    {
        Storage::disk('public')->put('feature-icons/horse.png', 'x');
        $event = Event::factory()->create(['icon_image' => 'feature-icons/horse.png']);
        $stop = EventStop::factory()->for($event)->create(['icon_image' => 'feature-icons/horse.png']);

        $this->actingAs($this->makeAdmin())
            ->deleteJson(route('admin.trails.feature-icons.delete'), ['path' => 'feature-icons/horse.png'])
            ->assertJson(['deleted' => false, 'in_use' => 2]);

        $this->actingAs($this->makeAdmin())
            ->deleteJson(route('admin.trails.feature-icons.delete'), ['path' => 'feature-icons/horse.png', 'force' => true])
            ->assertJson(['deleted' => true]);

        $this->assertNull($stop->fresh()->icon_image);
        $this->assertNull($event->fresh()->icon_image);
    }

    public function test_deleting_the_event_deletes_its_photos_and_activities(): void
    {
        $event = $this->makeEvent();
        $stopPhoto = 'events/'.$event->id.'/stops/photo.webp';
        $cover = 'events/'.$event->id.'/cover.webp';
        Storage::disk('public')->put($stopPhoto, 'x');
        Storage::disk('public')->put($cover, 'x');
        $event->update(['cover_image' => $cover]);
        EventStop::factory()->for($event)->roaming()->create(['stop_label' => 'Popcorn', 'image' => $stopPhoto]);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.local-events.destroy', $event))
            ->assertRedirect(route('admin.local-events.index'));

        Storage::disk('public')->assertMissing($stopPhoto);
        Storage::disk('public')->assertMissing($cover);
        $this->assertDatabaseCount('event_stops', 0);
    }

    public function test_admin_can_delete_the_cover_image(): void
    {
        $event = $this->makeEvent();
        $cover = 'events/'.$event->id.'/cover.webp';
        Storage::disk('public')->put($cover, 'x');
        $event->update(['cover_image' => $cover]);

        $this->actingAs($this->makeAdmin())
            ->deleteJson(route('admin.local-events.cover-image.delete', $event))
            ->assertJson(['success' => true]);

        $this->assertNull($event->fresh()->cover_image);
        Storage::disk('public')->assertMissing($cover);
    }
}
