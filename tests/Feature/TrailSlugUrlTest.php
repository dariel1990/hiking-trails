<?php

namespace Tests\Feature;

use App\Models\Trail;
use App\Models\TrailNetwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrailSlugUrlTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 Safari/605.1.15';

    private const ANDROID_APP_UA = 'Mozilla/5.0 (Linux; Android 14; Pixel 8; wv) AppleWebKit/537.36 Chrome/120.0 Mobile Safari/537.36 XploreSmithersApp/1.0';

    private const IOS_APP_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 XploreSmithersApp/1.0';

    private function makeTrail(array $attributes = []): Trail
    {
        return Trail::create(array_merge([
            'name' => 'Sulphur Creek Trail',
            'description' => 'A trail used in tests.',
            'location_type' => 'trail',
            'geometry_type' => 'linestring',
            'status' => 'active',
            'difficulty_level' => 3,
            'distance_km' => 5,
            'elevation_gain_m' => 100,
            'estimated_time_hours' => 2,
            'trail_type' => 'loop',
            'start_coordinates' => [54.0, -127.0],
        ], $attributes));
    }

    public function test_a_slug_is_generated_from_the_name_on_create(): void
    {
        $trail = $this->makeTrail(['name' => 'Sulphur Creek Trail']);

        $this->assertSame('sulphur-creek-trail', $trail->slug);
    }

    public function test_duplicate_names_get_unique_slugs(): void
    {
        $first = $this->makeTrail(['name' => 'Blue Lakes']);
        $second = $this->makeTrail(['name' => 'Blue Lakes']);
        $third = $this->makeTrail(['name' => 'Blue Lakes']);

        $this->assertSame('blue-lakes', $first->slug);
        $this->assertSame('blue-lakes-2', $second->slug);
        $this->assertSame('blue-lakes-3', $third->slug);
    }

    public function test_an_all_digit_name_gets_a_suffix_so_it_is_not_read_as_an_id(): void
    {
        $trail = $this->makeTrail(['name' => '100']);

        $this->assertSame('100-trail', $trail->slug);
    }

    public function test_the_slug_does_not_change_when_the_trail_is_renamed(): void
    {
        $trail = $this->makeTrail(['name' => 'Old Name']);

        $trail->update(['name' => 'New Name']);

        $this->assertSame('old-name', $trail->fresh()->slug);
    }

    public function test_a_browser_visiting_the_id_url_is_permanently_redirected_to_the_slug_url(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get('/trails/'.$trail->id)
            ->assertStatus(301)
            ->assertRedirect('/trails/sulphur-creek-trail');
    }

    public function test_the_redirect_keeps_the_query_string(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get('/trails/'.$trail->id.'?from=map')
            ->assertRedirect('/trails/sulphur-creek-trail?from=map');
    }

    public function test_a_browser_can_view_the_slug_url(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get('/trails/sulphur-creek-trail')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/trails/sulphur-creek-trail').'">', false);

        $this->assertSame(1, $trail->fresh()->view_count);
    }

    public function test_the_android_app_keeps_the_id_url_without_a_redirect(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::ANDROID_APP_UA)
            ->get('/trails/'.$trail->id)
            ->assertOk();
    }

    public function test_the_ios_app_keeps_the_id_url_without_a_redirect(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::IOS_APP_UA)
            ->get('/trails/'.$trail->id)
            ->assertOk();
    }

    public function test_the_app_opening_a_slug_url_is_sent_to_the_id_url(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::ANDROID_APP_UA)
            ->get('/trails/sulphur-creek-trail?from=map')
            ->assertStatus(302)
            ->assertRedirect('/trails/'.$trail->id.'?from=map');
    }

    public function test_redirects_do_not_count_as_views(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::BROWSER_UA)->get('/trails/'.$trail->id);

        $this->assertSame(0, (int) $trail->fresh()->view_count);
    }

    public function test_unknown_slugs_and_ids_return_404(): void
    {
        $this->withHeader('User-Agent', self::BROWSER_UA)->get('/trails/no-such-trail')->assertNotFound();
        $this->withHeader('User-Agent', self::BROWSER_UA)->get('/trails/999999')->assertNotFound();
    }

    public function test_a_closed_trail_is_404_by_slug_for_browsers_and_by_id_for_the_app(): void
    {
        $trail = $this->makeTrail(['status' => 'closed']);

        $this->withHeader('User-Agent', self::BROWSER_UA)->get('/trails/'.$trail->slug)->assertNotFound();
        $this->withHeader('User-Agent', self::ANDROID_APP_UA)->get('/trails/'.$trail->id)->assertNotFound();
    }

    public function test_a_trail_in_an_inactive_network_is_404(): void
    {
        $network = TrailNetwork::create([
            'network_name' => 'Hidden Network',
            'type' => 'hiking',
            'season' => 'both',
            'latitude' => 54.78,
            'longitude' => -127.17,
            'is_active' => false,
        ]);
        $trail = $this->makeTrail(['trail_network_id' => $network->id]);

        $this->withHeader('User-Agent', self::BROWSER_UA)->get('/trails/'.$trail->slug)->assertNotFound();
    }

    public function test_the_api_still_addresses_trails_by_id_and_includes_the_slug(): void
    {
        $trail = $this->makeTrail();

        $this->getJson('/api/trails/'.$trail->id)
            ->assertOk()
            ->assertJsonPath('id', $trail->id)
            ->assertJsonPath('slug', 'sulphur-creek-trail');

        $this->getJson('/api/trails/sulphur-creek-trail')->assertNotFound();
    }

    public function test_the_api_trail_list_includes_the_slug(): void
    {
        $this->makeTrail();

        $this->getJson('/api/trails')
            ->assertOk()
            ->assertJsonPath('0.slug', 'sulphur-creek-trail');
    }

    public function test_the_trail_list_links_to_slugs_for_browsers_and_ids_for_the_app(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get('/trails')
            ->assertOk()
            ->assertSee(url('/trails/sulphur-creek-trail'), false)
            ->assertSee('window.IS_NATIVE_APP = false', false);

        $this->withHeader('User-Agent', self::ANDROID_APP_UA)
            ->get('/trails')
            ->assertOk()
            ->assertSee(url('/trails/'.$trail->id), false)
            ->assertDontSee(url('/trails/sulphur-creek-trail'), false)
            ->assertSee('window.IS_NATIVE_APP = true', false);
    }

    public function test_the_sitemap_lists_slug_urls(): void
    {
        $this->makeTrail();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(url('/trails/sulphur-creek-trail'), false);
    }

    public function test_a_browser_opening_the_map_with_a_trail_id_is_redirected_to_the_slug(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get('/map?trail='.$trail->id)
            ->assertStatus(301)
            ->assertRedirect(route('map', ['trail' => 'sulphur-creek-trail']));
    }

    public function test_the_map_redirect_keeps_other_query_parameters(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get('/map?town=smithers-bc&trail='.$trail->id)
            ->assertRedirect(route('map', ['town' => 'smithers-bc', 'trail' => 'sulphur-creek-trail']));
    }

    public function test_the_map_focuses_a_trail_given_by_slug(): void
    {
        $this->makeTrail(['start_coordinates' => [54.7804, -127.1743]]);

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get('/map?trail=sulphur-creek-trail')
            ->assertOk()
            ->assertViewHas('focusCoordinates', [54.7804, -127.1743]);
    }

    public function test_the_app_keeps_the_numeric_map_trail_parameter(): void
    {
        $trail = $this->makeTrail(['start_coordinates' => [54.7804, -127.1743]]);

        $this->withHeader('User-Agent', self::ANDROID_APP_UA)
            ->get('/map?trail='.$trail->id)
            ->assertOk()
            ->assertViewHas('focusCoordinates', [54.7804, -127.1743]);
    }

    public function test_the_map_ignores_unknown_or_malformed_trail_parameters(): void
    {
        $this->withHeader('User-Agent', self::BROWSER_UA)->get('/map?trail=999999')->assertOk();
        $this->withHeader('User-Agent', self::BROWSER_UA)->get('/map?trail=no-such-trail')->assertOk();
        $this->withHeader('User-Agent', self::BROWSER_UA)->get('/map?trail[]=1')->assertOk();
    }

    public function test_the_trail_page_map_links_use_the_slug_for_browsers_and_the_id_for_the_app(): void
    {
        $trail = $this->makeTrail();

        $this->withHeader('User-Agent', self::BROWSER_UA)
            ->get('/trails/sulphur-creek-trail?from=map')
            ->assertSee(route('map').'?trail=sulphur-creek-trail', false);

        $this->withHeader('User-Agent', self::ANDROID_APP_UA)
            ->get('/trails/'.$trail->id.'?from=map')
            ->assertSee(route('map').'?trail='.$trail->id, false)
            ->assertDontSee('?trail=sulphur-creek-trail', false);
    }

    public function test_the_admin_trail_page_shows_the_public_slug_url(): void
    {
        $trail = $this->makeTrail();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.trails.show', $trail))
            ->assertOk()
            ->assertSee(url('/trails/sulphur-creek-trail'), false);
    }

    public function test_admin_trail_routes_still_bind_by_id(): void
    {
        $trail = $this->makeTrail();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertStringEndsWith('/admin/trails/'.$trail->id, route('admin.trails.show', $trail));

        $this->actingAs($admin)->get('/admin/trails/'.$trail->id.'/edit')->assertOk();
    }

    public function test_an_app_webview_without_the_app_token_is_still_treated_as_the_app(): void
    {
        $trail = $this->makeTrail();
        $androidWebView = 'Mozilla/5.0 (Linux; Android 14; Pixel 8; wv) AppleWebKit/537.36 Chrome/120.0 Mobile Safari/537.36';
        $iosWebView = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148';

        $this->withHeader('User-Agent', $androidWebView)->get('/trails/'.$trail->id)->assertOk();
        $this->withHeader('User-Agent', $iosWebView)->get('/trails/'.$trail->id)->assertOk();
    }

    public function test_mobile_browsers_get_the_slug_url(): void
    {
        $trail = $this->makeTrail();
        $iosSafari = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
        $androidChrome = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Mobile Safari/537.36';

        $this->withHeader('User-Agent', $iosSafari)->get('/trails/'.$trail->id)->assertRedirect('/trails/sulphur-creek-trail');
        $this->withHeader('User-Agent', $androidChrome)->get('/trails/'.$trail->id)->assertRedirect('/trails/sulphur-creek-trail');
    }
}
