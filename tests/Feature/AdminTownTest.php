<?php

namespace Tests\Feature;

use App\Models\Town;
use App\Models\Trail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTownTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Houston',
            'province' => 'British Columbia',
            'province_code' => 'BC',
            'latitude' => 54.3986,
            'longitude' => -126.6470,
            'radius_km' => 40,
            'map_zoom' => 11,
            'sort_order' => 3,
            'is_active' => '1',
            'is_indexable' => 'auto',
            'color' => '#2C5F5D',
        ], $overrides);
    }

    public function test_a_guest_cannot_reach_the_town_admin(): void
    {
        $this->get(route('admin.towns.index'))->assertRedirect();
    }

    public function test_an_admin_can_list_towns(): void
    {
        Town::factory()->create(['name' => 'Houston']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.towns.index'))
            ->assertOk()
            ->assertSee('Houston');
    }

    public function test_an_admin_can_open_the_create_form(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.towns.create'))
            ->assertOk()
            ->assertSee('Add a town');
    }

    public function test_an_admin_can_create_a_town_and_the_slug_is_generated(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.towns.store'), $this->payload())
            ->assertRedirect(route('admin.towns.index'));

        $town = Town::firstWhere('name', 'Houston');

        $this->assertNotNull($town);
        $this->assertSame('houston-bc', $town->slug);
        $this->assertNull($town->is_indexable, 'The "auto" choice should store null.');
    }

    public function test_the_indexing_choice_maps_onto_the_nullable_column(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('admin.towns.store'), $this->payload([
            'name' => 'Always', 'is_indexable' => 'index',
        ]));
        $this->assertTrue(Town::firstWhere('name', 'Always')->is_indexable);

        $this->actingAs($admin)->post(route('admin.towns.store'), $this->payload([
            'name' => 'Never', 'is_indexable' => 'noindex',
        ]));
        $this->assertFalse(Town::firstWhere('name', 'Never')->is_indexable);
    }

    public function test_an_admin_can_edit_a_town(): void
    {
        $town = Town::factory()->create(['name' => 'Houston', 'slug' => 'houston-bc']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.towns.edit', $town))
            ->assertOk()
            ->assertSee('/hiking-trails/houston-bc');

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.towns.update', $town), $this->payload([
                'name' => 'Houston',
                'slug' => 'houston-bc',
                'tagline' => 'Waterfall country.',
            ]))
            ->assertRedirect(route('admin.towns.index'));

        $this->assertSame('Waterfall country.', $town->fresh()->tagline);
    }

    public function test_a_duplicate_slug_is_rejected(): void
    {
        Town::factory()->create(['slug' => 'houston-bc']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.towns.store'), $this->payload(['slug' => 'houston-bc']))
            ->assertSessionHasErrors('slug');
    }

    public function test_an_admin_can_toggle_and_delete_a_town(): void
    {
        $town = Town::factory()->create();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->patch(route('admin.towns.toggle-active', $town));
        $this->assertFalse($town->fresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.towns.destroy', $town));
        $this->assertModelMissing($town);
    }

    public function test_deleting_a_town_leaves_its_trails_in_place(): void
    {
        $town = Town::factory()->create();

        $trail = Trail::create([
            'town_id' => $town->id,
            'name' => 'Orphan Trail',
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'status' => 'active',
            'trail_type' => 'loop',
            'start_coordinates' => [54.0, -127.0],
        ]);

        $this->actingAs($this->makeAdmin())->delete(route('admin.towns.destroy', $town));

        $this->assertModelExists($trail);
        $this->assertNull($trail->fresh()->town_id);
    }

    public function test_the_town_select_appears_on_the_other_admin_forms(): void
    {
        Town::factory()->create(['name' => 'Houston']);
        $admin = $this->makeAdmin();

        foreach (['admin.trails.create', 'admin.facilities.create', 'admin.businesses.create', 'admin.tours.create'] as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertOk()
                ->assertSee('name="town_id"', false)
                ->assertSee('Houston, BC');
        }
    }

    public function test_an_admin_can_set_a_town_map_colour(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.towns.store'), $this->payload(['name' => 'Coloured', 'color' => '#0E7490']))
            ->assertRedirect(route('admin.towns.index'));

        $this->assertSame('#0E7490', Town::firstWhere('name', 'Coloured')->color);
    }

    public function test_a_malformed_colour_is_rejected(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.towns.store'), $this->payload(['color' => 'teal']))
            ->assertSessionHasErrors('color');
    }

    public function test_the_colour_picker_appears_on_the_town_form(): void
    {
        $town = Town::factory()->create(['color' => '#B91C1C']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.towns.edit', $town))
            ->assertOk()
            ->assertSee('type="color"', false)
            ->assertSee('#B91C1C', false);
    }
}
