<?php

namespace Tests\Feature\Admin;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TourCoverImageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_delete_a_tours_cover_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tours/cover.webp', 'not-really-an-image');

        $tour = Tour::create([
            'title' => 'Waterfalls Tour',
            'tour_type' => 'scenic',
            'cover_image' => 'tours/cover.webp',
        ]);

        $this->actingAs($this->admin())
            ->deleteJson(route('admin.tours.cover-image.delete', $tour))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull($tour->fresh()->cover_image);
        Storage::disk('public')->assertMissing('tours/cover.webp');
    }

    public function test_deleting_cover_image_is_a_no_op_when_tour_has_none(): void
    {
        Storage::fake('public');

        $tour = Tour::create([
            'title' => 'Heritage Tour',
            'tour_type' => 'heritage',
        ]);

        $this->actingAs($this->admin())
            ->deleteJson(route('admin.tours.cover-image.delete', $tour))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull($tour->fresh()->cover_image);
    }
}
