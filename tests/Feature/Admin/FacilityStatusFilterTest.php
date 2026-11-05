<?php

namespace Tests\Feature\Admin;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function facility(array $overrides = []): Facility
    {
        return Facility::create(array_merge([
            'facility_type' => 'recreational_site',
            'name' => 'Tyhee Lake Rec Site',
            'latitude' => 54.71,
            'longitude' => -127.03,
            'is_active' => true,
        ], $overrides));
    }

    public function test_recreational_site_is_a_selectable_facility_type(): void
    {
        $this->assertArrayHasKey('recreational_site', Facility::getFacilityTypes());
        $this->assertSame('🏞️', $this->facility()->getDefaultIcon());
    }

    public function test_admin_can_create_a_facility_of_type_recreational_site(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.facilities.store'), [
                'facility_type' => 'recreational_site',
                'name' => 'Tyhee Lake Rec Site',
                'latitude' => 54.71,
                'longitude' => -127.03,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.facilities.index'));
        $this->assertDatabaseHas('facilities', [
            'name' => 'Tyhee Lake Rec Site',
            'facility_type' => 'recreational_site',
        ]);
    }

    public function test_the_status_filter_separates_active_from_inactive(): void
    {
        $this->facility(['name' => 'Open Rec Site']);
        $this->facility(['name' => 'Closed Rec Site', 'is_active' => false]);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.facilities.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Closed Rec Site')
            ->assertDontSee('Open Rec Site')
            ->assertSee('Activate selected');

        $this->actingAs($admin)->get(route('admin.facilities.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('Open Rec Site')
            ->assertDontSee('Closed Rec Site');

        $this->actingAs($admin)->get(route('admin.facilities.index'))
            ->assertOk()
            ->assertSee('Open Rec Site')
            ->assertSee('Closed Rec Site');
    }

    public function test_the_status_filter_combines_with_the_type_filter(): void
    {
        $this->facility(['name' => 'Closed Rec Site', 'is_active' => false]);
        $this->facility(['name' => 'Closed Parking Lot', 'facility_type' => 'parking', 'is_active' => false]);

        $this->actingAs($this->admin())
            ->get(route('admin.facilities.index', ['status' => 'inactive', 'type' => 'recreational_site']))
            ->assertOk()
            ->assertSee('Closed Rec Site')
            ->assertDontSee('Closed Parking Lot');
    }

    public function test_an_unknown_status_shows_every_facility(): void
    {
        $this->facility(['name' => 'Open Rec Site']);
        $this->facility(['name' => 'Closed Rec Site', 'is_active' => false]);

        $this->actingAs($this->admin())
            ->get(route('admin.facilities.index', ['status' => 'bogus']))
            ->assertOk()
            ->assertSee('Open Rec Site')
            ->assertSee('Closed Rec Site');
    }

    public function test_admin_can_toggle_a_facility_active_state(): void
    {
        $facility = $this->facility(['is_active' => false]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.facilities.index', ['status' => 'inactive']))
            ->patch(route('admin.facilities.toggle-active', $facility))
            ->assertRedirect(route('admin.facilities.index', ['status' => 'inactive']))
            ->assertSessionHas('success');

        $this->assertTrue($facility->fresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.facilities.toggle-active', $facility));

        $this->assertFalse($facility->fresh()->is_active);
    }

    public function test_non_admins_cannot_toggle_or_bulk_activate(): void
    {
        $facility = $this->facility(['is_active' => false]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->patch(route('admin.facilities.toggle-active', $facility))->assertRedirect(route('admin.login'));
        $this->actingAs($user)->post(route('admin.facilities.bulk-activate'), ['ids' => [$facility->id]])->assertRedirect(route('admin.login'));

        $this->assertFalse($facility->fresh()->is_active);
    }

    public function test_admin_can_bulk_activate_only_the_selected_facilities(): void
    {
        $first = $this->facility(['name' => 'First', 'is_active' => false]);
        $second = $this->facility(['name' => 'Second', 'is_active' => false]);
        $untouched = $this->facility(['name' => 'Untouched', 'is_active' => false]);

        $this->actingAs($this->admin())
            ->post(route('admin.facilities.bulk-activate'), ['ids' => [$first->id, $second->id]])
            ->assertRedirect()
            ->assertSessionHas('success', '2 facilities activated.');

        $this->assertTrue($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);
        $this->assertFalse($untouched->fresh()->is_active);
    }

    public function test_bulk_activate_rejects_missing_or_unknown_ids(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.facilities.bulk-activate'), [])
            ->assertSessionHasErrors('ids');

        $this->actingAs($admin)
            ->post(route('admin.facilities.bulk-activate'), ['ids' => [999999]])
            ->assertSessionHasErrors('ids.0');
    }

    public function test_the_map_has_a_recreational_sites_tab_outside_the_all_filters_modal(): void
    {
        $response = $this->get(route('map'))->assertOk();

        $response->assertSee('data-location-filter="recreational_site"', false);
        $response->assertDontSee('value="recreational_site" class="facility-type-checkbox', false);
    }
}
