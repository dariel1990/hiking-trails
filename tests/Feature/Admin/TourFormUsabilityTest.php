<?php

namespace Tests\Feature\Admin;

use App\Models\Tour;
use App\Models\TourStop;
use App\Models\Trail;
use App\Models\User;
use App\Support\TourStopFormData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin tour create/edit form: save-and-continue, keeping edits after a failed
 * save, the error summary, and the toolbar/checklist chrome.
 */
class TourFormUsabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeTour(array $overrides = []): Tour
    {
        return Tour::create(array_merge([
            'title' => 'Murals Walk',
            'tour_type' => 'heritage',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function pinnedStop(array $overrides = []): array
    {
        return array_merge([
            'stop_label' => 'Alex Cuba Mural',
            'latitude' => '54.78',
            'longitude' => '-127.17',
            'description' => 'Painted in **2019**.',
        ], $overrides);
    }

    // ── Save & keep editing ──────────────────────────────────────────────────

    public function test_update_returns_to_the_list_by_default(): void
    {
        $tour = $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.tours.update', $tour), ['title' => 'Murals Walk', 'tour_type' => 'heritage'])
            ->assertRedirect(route('admin.tours.index'));
    }

    public function test_update_can_keep_editing(): void
    {
        $tour = $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.tours.update', $tour), ['title' => 'Murals Walk', 'tour_type' => 'heritage', 'after_save' => 'edit'])
            ->assertRedirect(route('admin.tours.edit', $tour))
            ->assertSessionHas('success');
    }

    public function test_create_can_keep_editing_the_new_tour(): void
    {
        $response = $this->actingAs($this->makeAdmin())
            ->post(route('admin.tours.store'), ['title' => 'Brand New Tour', 'tour_type' => 'scenic', 'after_save' => 'edit']);

        $tour = Tour::where('title', 'Brand New Tour')->firstOrFail();
        $response->assertRedirect(route('admin.tours.edit', $tour));
    }

    public function test_unknown_after_save_value_falls_back_to_the_list(): void
    {
        $tour = $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.tours.update', $tour), ['title' => 'Murals Walk', 'tour_type' => 'heritage', 'after_save' => 'https://evil.example'])
            ->assertRedirect(route('admin.tours.index'));
    }

    // ── Failed saves keep the admin's edits ──────────────────────────────────

    public function test_failed_save_keeps_stop_edits_and_lists_errors_by_stop(): void
    {
        $admin = $this->makeAdmin();
        $tour = $this->makeTour();
        TourStop::create(['tour_id' => $tour->id, 'stop_order' => 0, 'stop_label' => 'Saved stop', 'latitude' => 54.7, 'longitude' => -127.1]);

        $this->actingAs($admin)
            ->from(route('admin.tours.edit', $tour))
            ->put(route('admin.tours.update', $tour), [
                'title' => 'Murals Walk',
                'tour_type' => 'heritage',
                'stops' => [
                    $this->pinnedStop(['stop_label' => 'Unsaved new mural']),
                    $this->pinnedStop(['stop_label' => '', 'description' => 'Typed but not saved']),
                ],
            ])
            ->assertRedirect(route('admin.tours.edit', $tour))
            ->assertSessionHasErrors('stops.1');

        // Nothing was saved...
        $this->assertSame(['Saved stop'], $tour->stops()->pluck('stop_label')->all());

        // ...but the form comes back with what the admin typed, and says which stop is wrong.
        $this->actingAs($admin)
            ->get(route('admin.tours.edit', $tour))
            ->assertOk()
            ->assertSee('Unsaved new mural')
            ->assertSee('Typed but not saved')
            ->assertDontSee('&quot;stop_label&quot;:&quot;Saved stop&quot;', false)
            ->assertSee('Please fix this problem and save again')
            ->assertSee('Stop 2')
            ->assertSee('Pinned and roaming stops need a name.')
            ->assertSee('&quot;has_error&quot;:true', false);
    }

    public function test_stop_field_errors_use_readable_names(): void
    {
        $tour = $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.tours.update', $tour), [
                'title' => 'Murals Walk',
                'tour_type' => 'heritage',
                'stops' => [$this->pinnedStop(['stop_label' => str_repeat('x', 300)])],
            ])
            ->assertSessionHasErrors(['stops.0.stop_label' => 'The stop name field must not be greater than 255 characters.']);
    }

    public function test_form_data_rebuilds_every_stop_type_from_old_input(): void
    {
        $trail = (new Trail)->forceFill(['id' => 42, 'name' => 'Glacier Gulch', 'start_coordinates' => [54.8, -127.2]]);

        $stops = TourStopFormData::fromOldInput([
            ['trail_id' => (string) $trail->id, 'stop_label' => 'Waterfall', 'description' => 'Wow'],
            ['latitude' => '54.78', 'longitude' => '-127.17', 'stop_label' => 'Mural', 'existing_gallery' => ['tours/1/stops/a.webp']],
            ['is_roaming' => '1', 'stop_label' => 'Carollers'],
            ['trail_id' => '999999'],
            'not-an-array',
        ], collect([$trail]), collect(), collect(), [1]);

        $this->assertCount(3, $stops);

        $this->assertSame('trail', $stops[0]['item_type']);
        $this->assertSame('Glacier Gulch', $stops[0]['name']);
        $this->assertSame([54.8, -127.2], $stops[0]['start_coordinates']);
        $this->assertSame('Wow', $stops[0]['description']);
        $this->assertFalse($stops[0]['has_error']);

        $this->assertSame('pinned', $stops[1]['item_type']);
        $this->assertSame([54.78, -127.17], $stops[1]['start_coordinates']);
        $this->assertSame('tours/1/stops/a.webp', $stops[1]['gallery'][0]['path']);
        $this->assertTrue($stops[1]['has_error']);

        $this->assertSame('roaming', $stops[2]['item_type']);
        $this->assertNull($stops[2]['start_coordinates']);
    }

    public function test_positions_with_errors_reads_stop_indexes(): void
    {
        $this->assertSame([0, 3], TourStopFormData::positionsWithErrors([
            'title' => ['x'],
            'stops.0.stop_label' => ['x'],
            'stops.3' => ['x'],
            'stops.0.description' => ['x'],
        ]));
    }

    // ── Toolbar & checklist ──────────────────────────────────────────────────

    public function test_edit_page_shows_toolbar_checklist_and_view_on_site_link(): void
    {
        $tour = $this->makeTour();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.edit', $tour))
            ->assertOk()
            ->assertSee('href="#tour-stops"', false)
            ->assertSee('id="tour-route"', false)
            ->assertSee('Save &amp; keep editing', false)
            ->assertSee('Ready to publish?')
            ->assertSee('href="'.route('tours.show', $tour).'"', false)
            ->assertSee('Drawing a route is optional.');
    }

    public function test_create_page_has_no_view_on_site_link(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.create'))
            ->assertOk()
            ->assertSee('Create &amp; keep editing', false)
            ->assertDontSee('View on site');
    }

    public function test_inactive_tour_has_no_view_on_site_link(): void
    {
        $tour = $this->makeTour(['is_active' => false]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.tours.edit', $tour))
            ->assertOk()
            ->assertDontSee('View on site');
    }
}
