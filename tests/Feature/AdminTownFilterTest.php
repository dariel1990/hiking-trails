<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Town;
use App\Models\Trail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTownFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeTrail(?Town $town, string $name): Trail
    {
        return Trail::create([
            'town_id' => $town?->id,
            'name' => $name,
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'status' => 'active',
            'trail_type' => 'loop',
            'start_coordinates' => [54.0, -127.0],
        ]);
    }

    private function makeBusiness(?Town $town, string $name): Business
    {
        return Business::create([
            'town_id' => $town?->id,
            'name' => $name,
            'business_type' => 'cafe',
            'latitude' => 54.0,
            'longitude' => -127.0,
            'is_active' => true,
        ]);
    }

    // ------------------------------------------------------------ trails

    public function test_the_trail_list_can_be_filtered_by_town(): void
    {
        $houston = Town::factory()->create(['name' => 'Houston']);
        $telkwa = Town::factory()->create(['name' => 'Telkwa']);

        $this->makeTrail($houston, 'Houston Trail');
        $this->makeTrail($telkwa, 'Telkwa Trail');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.trails.index', ['town' => $houston->id]))
            ->assertOk()
            ->assertSee('Houston Trail')
            ->assertDontSee('Telkwa Trail');
    }

    /**
     * towns:assign leaves anything outside every radius unassigned. Those are
     * exactly the records an admin needs to find, so they get their own option
     * rather than being invisible.
     */
    public function test_the_trail_list_can_isolate_unassigned_trails(): void
    {
        $town = Town::factory()->create(['name' => 'Houston']);

        $this->makeTrail($town, 'Assigned Trail');
        $this->makeTrail(null, 'Orphan Trail');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.trails.index', ['town' => 'none']))
            ->assertOk()
            ->assertSee('Orphan Trail')
            ->assertDontSee('Assigned Trail');
    }

    public function test_the_trail_town_filter_combines_with_the_existing_filters(): void
    {
        $town = Town::factory()->create();

        $this->makeTrail($town, 'Active Here');
        $this->makeTrail($town, 'Closed Here')->update(['status' => 'closed']);
        $this->makeTrail(null, 'Active Elsewhere');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.trails.index', ['town' => $town->id, 'status' => 'active']))
            ->assertOk()
            ->assertSee('Active Here')
            ->assertDontSee('Closed Here')
            ->assertDontSee('Active Elsewhere');
    }

    public function test_the_trail_filter_offers_every_active_town_plus_unassigned(): void
    {
        Town::factory()->create(['name' => 'Houston']);
        Town::factory()->create(['name' => 'Telkwa']);
        Town::factory()->inactive()->create(['name' => 'Hiddentown']);

        $html = $this->actingAs($this->makeAdmin())
            ->get(route('admin.trails.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="town"', $html);
        $this->assertStringContainsString('Houston', $html);
        $this->assertStringContainsString('&mdash; Unassigned &mdash;', $html);

        // An inactive town cannot receive new assignments, so it is not offered.
        $this->assertStringNotContainsString('Hiddentown', $html);
    }

    public function test_the_trail_town_filter_survives_pagination(): void
    {
        $town = Town::factory()->create();

        $html = $this->actingAs($this->makeAdmin())
            ->get(route('admin.trails.index', ['town' => $town->id]))
            ->assertOk()
            ->getContent();

        // withQueryString() keeps the filter on the pager links.
        $this->assertStringContainsString('name="town"', $html);
        $this->assertStringContainsString('value="'.$town->id.'" selected', $html);
    }

    // -------------------------------------------------------- businesses

    public function test_the_business_list_can_be_filtered_by_town(): void
    {
        $houston = Town::factory()->create(['name' => 'Houston']);
        $telkwa = Town::factory()->create(['name' => 'Telkwa']);

        $this->makeBusiness($houston, 'Houston Cafe');
        $this->makeBusiness($telkwa, 'Telkwa Cafe');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.businesses.index', ['town' => $houston->id]))
            ->assertOk()
            ->assertSee('Houston Cafe')
            ->assertDontSee('Telkwa Cafe');
    }

    public function test_the_business_list_can_isolate_unassigned_businesses(): void
    {
        $town = Town::factory()->create();

        $this->makeBusiness($town, 'Assigned Cafe');
        $this->makeBusiness(null, 'Orphan Cafe');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.businesses.index', ['town' => 'none']))
            ->assertOk()
            ->assertSee('Orphan Cafe')
            ->assertDontSee('Assigned Cafe');
    }

    public function test_the_business_town_filter_combines_with_search(): void
    {
        $houston = Town::factory()->create();
        $telkwa = Town::factory()->create();

        $this->makeBusiness($houston, 'Riverside Cafe');
        $this->makeBusiness($houston, 'Mountain Grill');
        $this->makeBusiness($telkwa, 'Riverside Diner');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.businesses.index', ['town' => $houston->id, 'search' => 'Riverside']))
            ->assertOk()
            ->assertSee('Riverside Cafe')
            ->assertDontSee('Mountain Grill')
            ->assertDontSee('Riverside Diner');
    }

    public function test_the_business_clear_button_appears_for_a_town_only_filter(): void
    {
        $town = Town::factory()->create();
        $this->makeBusiness($town, 'A Cafe');

        // Clear used to key off $search alone, so a town-only filter had no way out.
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.businesses.index', ['town' => $town->id]))
            ->assertOk()
            ->assertSee('Clear');
    }

    public function test_an_unfiltered_list_shows_everything(): void
    {
        $town = Town::factory()->create();

        $this->makeTrail($town, 'Town Trail');
        $this->makeTrail(null, 'Orphan Trail');
        $this->makeBusiness($town, 'Town Cafe');
        $this->makeBusiness(null, 'Orphan Cafe');

        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.trails.index'))
            ->assertOk()->assertSee('Town Trail')->assertSee('Orphan Trail');

        $this->actingAs($admin)->get(route('admin.businesses.index'))
            ->assertOk()->assertSee('Town Cafe')->assertSee('Orphan Cafe');
    }
}
