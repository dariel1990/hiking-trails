<?php

namespace Tests\Feature;

use App\Models\Town;
use App\Models\Trail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    private function makeTrail(Town $town): Trail
    {
        return Trail::create([
            'town_id' => $town->id,
            'name' => 'Sitemap Trail',
            'description' => 'A trail used in tests.',
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'status' => 'active',
            'difficulty_level' => 3,
            'distance_km' => 5,
            'trail_type' => 'loop',
            'start_coordinates' => [(float) $town->latitude, (float) $town->longitude],
        ]);
    }

    public function test_it_returns_well_formed_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk()->assertHeader('Content-Type', 'application/xml');

        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml, 'The sitemap is not valid XML.');
        $this->assertSame('urlset', $xml->getName());
    }

    public function test_it_lists_towns_that_have_trails(): void
    {
        $town = Town::factory()->create(['name' => 'Houston', 'slug' => 'houston-bc']);
        $this->makeTrail($town);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('towns.show', $town), false);
    }

    public function test_it_omits_a_town_with_no_trails(): void
    {
        $empty = Town::factory()->create(['name' => 'Stewart', 'slug' => 'stewart-bc']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('towns.show', $empty), false);
    }

    public function test_it_omits_a_town_the_admin_marked_noindex(): void
    {
        $town = Town::factory()->create(['slug' => 'hidden-bc', 'is_indexable' => false]);
        $this->makeTrail($town);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('towns.show', $town), false);
    }

    public function test_it_includes_an_empty_town_the_admin_forced_to_index(): void
    {
        $town = Town::factory()->create(['slug' => 'forced-bc', 'is_indexable' => true]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('towns.show', $town), false);
    }

    public function test_it_lists_the_core_public_pages(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();

        foreach (['home', 'towns.index', 'trails.index', 'fishing-lakes.index', 'tours.index'] as $name) {
            $response->assertSee(route($name), false);
        }
    }
}
