<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Event photo & video gallery: temporary photo uploads, saving the gallery
 * with the event (order, captions, removal) and showing it on the event page.
 */
class EventGalleryTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function payload(array $media, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Christmas Truck Parade',
            'event_date' => now()->addDays(10)->toDateString(),
            'theme' => Event::THEME_CHRISTMAS,
            'is_active' => '1',
            'media_sync' => '1',
            'media' => $media,
        ], $overrides);
    }

    /**
     * @return array{path: string, thumbnail_path: string}
     */
    private function uploadTempPhoto(User $admin): array
    {
        return $this->actingAs($admin)
            ->postJson(route('admin.local-events.media.upload'), ['photo' => UploadedFile::fake()->image('truck.jpg', 1200, 800)])
            ->assertOk()
            ->json();
    }

    public function test_photo_upload_goes_to_temporary_storage(): void
    {
        $data = $this->uploadTempPhoto($this->makeAdmin());

        $this->assertStringStartsWith('events/tmp/photos/', $data['path']);
        $this->assertStringStartsWith('events/tmp/photos/thumbs/', $data['thumbnail_path']);
        Storage::disk('public')->assertExists([$data['path'], $data['thumbnail_path']]);
    }

    public function test_upload_rejects_non_images_and_guests(): void
    {
        $this->actingAs($this->makeAdmin())
            ->postJson(route('admin.local-events.media.upload'), ['photo' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('photo');

        auth()->logout();
        $this->post(route('admin.local-events.media.upload'), ['photo' => UploadedFile::fake()->image('a.jpg')])
            ->assertRedirect();
    }

    public function test_removed_temporary_photo_is_deleted(): void
    {
        $admin = $this->makeAdmin();
        $data = $this->uploadTempPhoto($admin);

        $this->actingAs($admin)
            ->deleteJson(route('admin.local-events.media.upload.delete'), $data)
            ->assertOk();

        Storage::disk('public')->assertMissing([$data['path'], $data['thumbnail_path']]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.local-events.media.upload.delete'), ['path' => 'feature-icons/horse.png'])
            ->assertUnprocessable();
    }

    public function test_admin_can_create_an_event_with_photos_and_videos_in_order(): void
    {
        $admin = $this->makeAdmin();
        $photo = $this->uploadTempPhoto($admin);

        $this->actingAs($admin)
            ->post(route('admin.local-events.store'), $this->payload([
                ['video_url' => 'https://youtu.be/UAg9ijcBJWY', 'caption' => 'Highlights'],
                [...$photo, 'caption' => 'Trucks on Main Street'],
                ['video_url' => 'https://vimeo.com/76979871'],
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.local-events.index'));

        $event = Event::firstOrFail();
        [$video, $savedPhoto, $vimeo] = $event->media->all();

        $this->assertTrue($video->isVideo());
        $this->assertSame('youtube', $video->video_provider);
        $this->assertSame('https://www.youtube-nocookie.com/embed/UAg9ijcBJWY', $video->embed_url);
        $this->assertSame('https://img.youtube.com/vi/UAg9ijcBJWY/hqdefault.jpg', $video->thumbnail_url);
        $this->assertSame('Highlights', $video->caption);

        $this->assertTrue($savedPhoto->isPhoto());
        $this->assertSame(1, $savedPhoto->sort_order);
        $this->assertStringStartsWith('events/'.$event->id.'/gallery/', $savedPhoto->file_path);
        $this->assertStringStartsWith('events/'.$event->id.'/gallery/thumbs/', $savedPhoto->thumbnail_path);
        Storage::disk('public')->assertExists([$savedPhoto->file_path, $savedPhoto->thumbnail_path]);
        Storage::disk('public')->assertMissing($photo['path']);

        $this->assertSame('vimeo', $vimeo->video_provider);
        $this->assertSame('https://player.vimeo.com/video/76979871', $vimeo->embed_url);
    }

    public function test_updating_reorders_recaptions_and_removes_items(): void
    {
        $event = Event::factory()->create();
        Storage::disk('public')->put('events/'.$event->id.'/gallery/a.webp', 'a');
        Storage::disk('public')->put('events/'.$event->id.'/gallery/b.webp', 'b');
        $first = EventMedia::factory()->for($event)->create(['file_path' => 'events/'.$event->id.'/gallery/a.webp', 'sort_order' => 0]);
        $second = EventMedia::factory()->for($event)->create(['file_path' => 'events/'.$event->id.'/gallery/b.webp', 'sort_order' => 1]);
        $video = EventMedia::factory()->for($event)->youtube()->create(['sort_order' => 2]);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $event), $this->payload([
                ['id' => $video->id, 'caption' => 'Now first'],
                ['id' => $second->id, 'caption' => ''],
            ], ['title' => $event->title]))
            ->assertSessionHasNoErrors();

        $this->assertSame([$video->id, $second->id], $event->media()->pluck('id')->all());
        $this->assertSame('Now first', $video->fresh()->caption);
        $this->assertNull($second->fresh()->caption);
        $this->assertModelMissing($first);
        Storage::disk('public')->assertMissing('events/'.$event->id.'/gallery/a.webp');
        Storage::disk('public')->assertExists('events/'.$event->id.'/gallery/b.webp');
    }

    public function test_gallery_is_untouched_when_the_editor_did_not_load(): void
    {
        $event = Event::factory()->create();
        $media = EventMedia::factory()->for($event)->create();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $event), $this->payload([], ['media_sync' => null, 'title' => $event->title]))
            ->assertSessionHasNoErrors();

        $this->assertModelExists($media);
    }

    public function test_media_of_another_event_cannot_be_claimed(): void
    {
        $other = EventMedia::factory()->create(['caption' => 'Not yours']);
        $event = Event::factory()->create();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.local-events.update', $event), $this->payload([
                ['id' => $other->id, 'caption' => 'Hijacked'],
            ], ['title' => $event->title]));

        $this->assertSame('Not yours', $other->fresh()->caption);
        $this->assertSame($other->event_id, $other->fresh()->event_id);
        $this->assertSame(0, $event->media()->count());
    }

    public function test_invalid_gallery_items_are_rejected(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.local-events.store'), $this->payload([
                ['video_url' => 'https://example.com/video.mp4'],
                ['path' => 'feature-icons/../../.env'],
                ['caption' => 'Nothing else'],
            ]))
            ->assertSessionHasErrors(['media.0.video_url', 'media.1.path', 'media.2']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_deleting_the_event_deletes_its_gallery_files(): void
    {
        $event = Event::factory()->create();
        $path = 'events/'.$event->id.'/gallery/photo.webp';
        $thumb = 'events/'.$event->id.'/gallery/thumbs/photo.webp';
        Storage::disk('public')->put($path, 'x');
        Storage::disk('public')->put($thumb, 'x');
        EventMedia::factory()->for($event)->create(['file_path' => $path, 'thumbnail_path' => $thumb]);

        $this->actingAs($this->makeAdmin())->delete(route('admin.local-events.destroy', $event));

        Storage::disk('public')->assertMissing([$path, $thumb]);
        $this->assertDatabaseCount('event_media', 0);
    }

    public function test_event_page_shows_the_gallery_with_photos_and_videos(): void
    {
        $event = Event::factory()->create();
        EventMedia::factory()->for($event)->create(['caption' => 'Lights on Main Street', 'thumbnail_path' => 'events/1/gallery/thumbs/x.webp']);
        EventMedia::factory()->for($event)->youtube('UAg9ijcBJWY')->create(['caption' => 'Highlights', 'sort_order' => 1]);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('Photos &amp; Videos', false)
            ->assertSee('1 photo · 1 video')
            ->assertSee('Lights on Main Street')
            ->assertSee('storage/events/1/gallery/thumbs/x.webp', false)
            ->assertSee('https://img.youtube.com/vi/UAg9ijcBJWY/hqdefault.jpg', false)
            ->assertSee('youtube-nocookie.com', false)
            ->assertSee('eventLightbox(', false);
    }

    public function test_a_gallery_of_only_photos_is_titled_photos(): void
    {
        $event = Event::factory()->create();
        EventMedia::factory()->for($event)->count(4)->create();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('>Photos</h2>', false)
            ->assertSee('4 photos')
            ->assertDontSee('Photos &amp; Videos', false);
    }

    public function test_a_gallery_of_only_videos_is_titled_videos(): void
    {
        $event = Event::factory()->create(['video_url' => null]);
        EventMedia::factory()->for($event)->youtube('mvczV0VFUK4')->create(['sort_order' => 0]);
        EventMedia::factory()->for($event)->youtube('UAg9ijcBJWY')->create(['sort_order' => 1]);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('>Videos</h2>', false)
            ->assertSee('2 videos')
            ->assertDontSee('Photos &amp; Videos', false);
    }

    public function test_event_page_without_media_has_no_gallery(): void
    {
        $event = Event::factory()->create();

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertDontSee('id="gallery"', false);
    }

    public function test_admin_edit_form_lists_existing_gallery_items(): void
    {
        $event = Event::factory()->create();
        EventMedia::factory()->for($event)->youtube()->create(['caption' => 'Highlights reel']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.local-events.edit', $event))
            ->assertOk()
            ->assertSee('Photos &amp; Videos', false)
            ->assertSee('eventGallery(', false)
            ->assertSee('Highlights reel');
    }

    /**
     * @return array<string, array{0: string, 1: array{provider: string, id: string}|null}>
     */
    public static function videoLinks(): array
    {
        return [
            'youtube watch' => ['https://www.youtube.com/watch?v=mvczV0VFUK4', ['provider' => 'youtube', 'id' => 'mvczV0VFUK4']],
            'youtube watch with extra params' => ['https://www.youtube.com/watch?feature=share&v=mvczV0VFUK4&t=10', ['provider' => 'youtube', 'id' => 'mvczV0VFUK4']],
            'youtu.be' => ['https://youtu.be/UAg9ijcBJWY?si=abc', ['provider' => 'youtube', 'id' => 'UAg9ijcBJWY']],
            'shorts' => ['https://youtube.com/shorts/UAg9ijcBJWY', ['provider' => 'youtube', 'id' => 'UAg9ijcBJWY']],
            'vimeo' => ['https://vimeo.com/76979871', ['provider' => 'vimeo', 'id' => '76979871']],
            'other site' => ['https://example.com/watch?v=mvczV0VFUK4', null],
        ];
    }

    /**
     * @param  array{provider: string, id: string}|null  $expected
     */
    #[DataProvider('videoLinks')]
    public function test_video_links_are_recognised(string $url, ?array $expected): void
    {
        $this->assertSame($expected, EventMedia::parseVideo($url));
    }
}
