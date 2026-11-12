<?php

namespace Tests\Feature;

use App\Models\Town;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * GET /api/towns — the town list behind the native app's municipality legend
 * and its Town filter section.
 */
class TownApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_active_towns_in_display_order(): void
    {
        Town::factory()->create(['name' => 'Telkwa', 'sort_order' => 2]);
        Town::factory()->create(['name' => 'Smithers', 'sort_order' => 1]);
        Town::factory()->inactive()->create(['name' => 'Hidden', 'sort_order' => 0]);

        $towns = $this->getJson('/api/towns')->assertOk()->json();

        $this->assertSame(['Smithers', 'Telkwa'], array_column($towns, 'name'));
    }

    public function test_it_exposes_the_fields_the_legend_and_camera_need(): void
    {
        $town = Town::factory()->at(54.7804, -127.1686)->create([
            'name' => 'Smithers',
            'slug' => 'smithers-bc',
            'color' => '#C2410C',
            'map_zoom' => 12,
        ]);

        $payload = $this->getJson('/api/towns')->assertOk()->json()[0];

        $this->assertSame($town->id, $payload['id']);
        $this->assertSame('Smithers', $payload['name']);
        $this->assertSame('smithers-bc', $payload['slug']);
        $this->assertSame('#C2410C', $payload['color']);
        $this->assertSame(54.7804, $payload['latitude']);
        $this->assertSame(-127.1686, $payload['longitude']);
        $this->assertSame(12, $payload['map_zoom']);
    }

    public function test_a_town_with_no_colour_falls_back_to_grey(): void
    {
        Town::factory()->create(['color' => null]);

        $payload = $this->getJson('/api/towns')->assertOk()->json()[0];

        $this->assertSame(Town::DEFAULT_COLOR, $payload['color']);
    }
}
