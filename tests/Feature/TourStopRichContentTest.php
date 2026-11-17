<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourStop;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Stop photo galleries and light-Markdown stop descriptions.
 */
class TourStopRichContentTest extends TestCase
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

    private function makeTour(): Tour
    {
        return Tour::create(['title' => 'Murals Walk', 'tour_type' => 'heritage', 'is_active' => true]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function pinnedStop(array $overrides = []): array
    {
        return array_merge([
            'stop_label' => 'Central Park Building',
            'latitude' => '54.78',
            'longitude' => '-127.17',
        ], $overrides);
    }

    /**
     * @param  list<array<string, mixed>>  $stops
     * @return array<string, mixed>
     */
    private function payload(Tour $tour, array $stops): array
    {
        return ['title' => $tour->title, 'tour_type' => $tour->tour_type, 'stops' => $stops];
    }

    // ── Rich text ────────────────────────────────────────────────────────────

    public function test_markdown_renders_bold_italic_links_and_lists(): void
    {
        $html = RichText::toHtml("Built in **1925** by *Henry Whittaker*.\n\n- Courtroom\n- Jail cells\n\n[Museum](https://example.org/museum)")->toHtml();

        $this->assertStringContainsString('<strong>1925</strong>', $html);
        $this->assertStringContainsString('<em>Henry Whittaker</em>', $html);
        $this->assertStringContainsString('<li>Courtroom</li>', $html);
        $this->assertStringContainsString('href="https://example.org/museum"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('noopener', $html);
    }

    public function test_markdown_keeps_single_line_breaks(): void
    {
        $html = RichText::toHtml("Line one\nLine two")->toHtml();

        $this->assertStringContainsString('Line one<br>', $html);
    }

    public function test_markdown_strips_html_and_unsafe_links(): void
    {
        $html = RichText::toHtml("<script>alert(1)</script>Hello <b onclick=\"x()\">there</b>\n\n[click](javascript:alert(1))")->toHtml();

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_markdown_handles_blank_input_and_plain_text_conversion(): void
    {
        $this->assertNull(RichText::toHtml(null));
        $this->assertNull(RichText::toHtml('   '));
        $this->assertSame('', RichText::toPlainText(null));
        $this->assertSame("Built in 1925.\n\nSee the museum & more.", RichText::toPlainText("Built in **1925**.\n\nSee [the museum](https://example.org) & more."));
    }

    public function test_map_payload_uses_plain_text_description(): void
    {
        $stop = new TourStop(['description' => 'A **bold** claim', 'stop_label' => 'X']);

        $this->assertSame('A bold claim', $stop->toMapStop(0, 1)['description']);
    }

    public function test_tour_page_renders_formatted_stop_description(): void
    {
        $tour = $this->makeTour();
        TourStop::create([
            'tour_id' => $tour->id,
            'stop_order' => 1,
            'stop_label' => 'Central Park Building',
            'latitude' => 54.78,
            'longitude' => -127.17,
            'description' => "Designed by **Henry Whittaker**.\n\n<script>alert(1)</script>",
        ]);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('<strong>Henry Whittaker</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_can_save_a_long_stop_description_but_not_over_the_limit(): void
    {
        $admin = $this->makeAdmin();
        $tour = $this->makeTour();
        $long = str_repeat('a', 9000);

        $this->actingAs($admin)
            ->put(route('admin.tours.update', $tour), $this->payload($tour, [$this->pinnedStop(['description' => $long])]))
            ->assertSessionHasNoErrors();
        $this->assertSame($long, $tour->fresh()->stops->first()->description);

        $this->actingAs($admin)
            ->put(route('admin.tours.update', $tour), $this->payload($tour, [$this->pinnedStop(['description' => str_repeat('a', 10001)])]))
            ->assertSessionHasErrors('stops.0.description');
    }

    // ── Gallery ──────────────────────────────────────────────────────────────

    public function test_admin_can_upload_gallery_photos_for_a_stop(): void
    {
        $tour = $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.tours.update', $tour), $this->payload($tour, [$this->pinnedStop([
                'image' => UploadedFile::fake()->image('main.jpg', 800, 600),
                'gallery' => [
                    UploadedFile::fake()->image('detail-1.jpg', 800, 600),
                    UploadedFile::fake()->image('detail-2.jpg', 800, 600),
                ],
            ])]))
            ->assertSessionHasNoErrors();

        $stop = $tour->fresh()->stops->first();

        $this->assertCount(2, $stop->gallery);
        foreach ($stop->gallery as $path) {
            $this->assertStringStartsWith('tours/'.$tour->id.'/stops/', $path);
            Storage::disk('public')->assertExists($path);
        }
        $this->assertCount(3, $stop->photoUrls());
        $this->assertSame(asset('storage/'.$stop->image), $stop->photoUrls()[0]);
    }

    public function test_gallery_photos_are_kept_removed_and_cleaned_up(): void
    {
        $admin = $this->makeAdmin();
        $tour = $this->makeTour();
        $keep = 'tours/'.$tour->id.'/stops/keep.webp';
        $drop = 'tours/'.$tour->id.'/stops/drop.webp';
        Storage::disk('public')->put($keep, 'x');
        Storage::disk('public')->put($drop, 'x');
        TourStop::create([
            'tour_id' => $tour->id, 'stop_order' => 0, 'stop_label' => 'A',
            'latitude' => 54.78, 'longitude' => -127.17, 'gallery' => [$keep, $drop],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.tours.update', $tour), $this->payload($tour, [$this->pinnedStop([
                'existing_gallery' => [$keep],
                'gallery' => [UploadedFile::fake()->image('new.jpg')],
            ])]))
            ->assertSessionHasNoErrors();

        $gallery = $tour->fresh()->stops->first()->gallery;

        $this->assertCount(2, $gallery);
        $this->assertSame($keep, $gallery[0]);
        Storage::disk('public')->assertExists($keep);
        Storage::disk('public')->assertMissing($drop);
    }

    public function test_existing_gallery_paths_outside_the_tour_are_ignored(): void
    {
        $tour = $this->makeTour();
        Storage::disk('public')->put('feature-icons/someone-elses.png', 'x');

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.tours.update', $tour), $this->payload($tour, [$this->pinnedStop([
                'existing_gallery' => ['feature-icons/someone-elses.png'],
            ])]));

        $this->assertNull($tour->fresh()->stops->first()->gallery);
        Storage::disk('public')->assertExists('feature-icons/someone-elses.png');
    }

    public function test_gallery_is_capped_and_rejects_non_images(): void
    {
        $admin = $this->makeAdmin();
        $tour = $this->makeTour();
        $tooMany = array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg"), range(1, TourStop::MAX_GALLERY_PHOTOS + 1));

        $this->actingAs($admin)
            ->put(route('admin.tours.update', $tour), $this->payload($tour, [$this->pinnedStop(['gallery' => $tooMany])]))
            ->assertSessionHasErrors('stops.0.gallery');

        $this->actingAs($admin)
            ->put(route('admin.tours.update', $tour), $this->payload($tour, [$this->pinnedStop([
                'gallery' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
            ])]))
            ->assertSessionHasErrors('stops.0.gallery.0');
    }

    public function test_kept_photos_count_toward_the_cap_so_extra_uploads_are_not_stored(): void
    {
        $tour = $this->makeTour();
        $existing = [];
        for ($i = 0; $i < TourStop::MAX_GALLERY_PHOTOS; $i++) {
            $existing[] = $path = 'tours/'.$tour->id."/stops/old-{$i}.webp";
            Storage::disk('public')->put($path, 'x');
        }
        TourStop::create([
            'tour_id' => $tour->id, 'stop_order' => 0, 'stop_label' => 'A',
            'latitude' => 54.78, 'longitude' => -127.17, 'gallery' => $existing,
        ]);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.tours.update', $tour), $this->payload($tour, [$this->pinnedStop([
                'existing_gallery' => $existing,
                'gallery' => [UploadedFile::fake()->image('extra.jpg')],
            ])]))
            ->assertSessionHasNoErrors();

        $this->assertSame($existing, $tour->fresh()->stops->first()->gallery);
        $this->assertCount(TourStop::MAX_GALLERY_PHOTOS, Storage::disk('public')->files('tours/'.$tour->id.'/stops'));
    }

    public function test_admin_edit_page_loads_existing_gallery_and_formatting_toolbar(): void
    {
        $tour = $this->makeTour();
        TourStop::create([
            'tour_id' => $tour->id, 'stop_order' => 0, 'stop_label' => 'A',
            'latitude' => 54.78, 'longitude' => -127.17, 'gallery' => ['tours/'.$tour->id.'/stops/g.webp'],
        ]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.edit', $tour))
            ->assertOk()
            ->assertSee('tours\/'.$tour->id.'\/stops\/g.webp', false)
            ->assertSee('addGalleryPhotos', false)
            ->assertSee('formatStopDescription', false)
            ->assertSee('maxlength="10000"', false);
    }

    public function test_deleting_the_tour_removes_gallery_photos(): void
    {
        $tour = $this->makeTour();
        $path = 'tours/'.$tour->id.'/stops/g.webp';
        Storage::disk('public')->put($path, 'x');
        TourStop::create([
            'tour_id' => $tour->id, 'stop_order' => 0, 'stop_label' => 'A',
            'latitude' => 54.78, 'longitude' => -127.17, 'gallery' => [$path],
        ]);

        $this->actingAs($this->makeAdmin())->delete(route('admin.tours.destroy', $tour));

        Storage::disk('public')->assertMissing($path);
    }

    public function test_tour_page_lists_every_stop_photo_for_the_viewer(): void
    {
        $tour = $this->makeTour();
        TourStop::create([
            'tour_id' => $tour->id, 'stop_order' => 0, 'stop_label' => 'Mural',
            'latitude' => 54.78, 'longitude' => -127.17,
            'image' => 'tours/main.webp', 'gallery' => ['tours/g1.webp', 'tours/g2.webp'],
        ]);

        $response = $this->get(route('tours.show', $tour))->assertOk();

        $expected = e(json_encode([
            asset('storage/tours/main.webp'),
            asset('storage/tours/g1.webp'),
            asset('storage/tours/g2.webp'),
        ]));
        $response->assertSee('data-photos="'.$expected.'"', false)
            ->assertSee('3 photos')
            ->assertSee('id="stop-image-viewer-next"', false);
    }

    public function test_stop_with_only_gallery_photos_uses_the_first_as_its_image(): void
    {
        $tour = $this->makeTour();
        TourStop::create([
            'tour_id' => $tour->id, 'stop_order' => 0, 'stop_label' => 'Mural',
            'latitude' => 54.78, 'longitude' => -127.17, 'gallery' => ['tours/g1.webp'],
        ]);

        $this->get(route('tours.show', $tour))
            ->assertOk()
            ->assertSee('src="'.asset('storage/tours/g1.webp').'"', false)
            ->assertSee('View photo');
    }
}
