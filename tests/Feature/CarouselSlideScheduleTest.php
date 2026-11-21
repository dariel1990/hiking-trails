<?php

namespace Tests\Feature;

use App\Models\CarouselSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Carousel slides with a From / To schedule: hidden and switched off once the end date passes.
 */
class CarouselSlideScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * "Now" as a local (Smithers) time.
     */
    private function travelToLocal(string $localDateTime): void
    {
        Carbon::setTestNow(Carbon::parse($localDateTime, config('app.local_timezone')));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeSlide(string $caption, array $attributes = []): CarouselSlide
    {
        return CarouselSlide::create(array_merge([
            'filename' => str($caption)->slug().'.webp',
            'caption' => $caption,
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_a_slide_shows_through_its_end_date_in_local_time(): void
    {
        $this->makeSlide('Craft Fair', ['starts_at' => '2026-11-01', 'ends_at' => '2026-11-15']);

        // 11 PM on Nov 15 in Smithers is already Nov 16 in UTC; the slide must still show.
        $this->travelToLocal('2026-11-15 23:00');
        $this->get(route('events.index'))->assertOk()->assertSee('Craft Fair');

        $this->travelToLocal('2026-11-16 00:30');
        $this->get(route('events.index'))->assertOk()->assertDontSee('Craft Fair');
    }

    public function test_the_expire_command_switches_off_slides_past_their_end_date(): void
    {
        $ended = $this->makeSlide('Craft Fair', ['ends_at' => '2026-11-15']);
        $lastDay = $this->makeSlide('Winter Lights', ['ends_at' => '2026-11-16']);
        $open = $this->makeSlide('Ski Hill');

        $this->travelToLocal('2026-11-16 00:05');
        $this->artisan('carousel:expire')->expectsOutput('1 expired slide turned off.')->assertSuccessful();

        $this->assertFalse($ended->fresh()->is_active);
        $this->assertTrue($lastDay->fresh()->is_active);
        $this->assertTrue($open->fresh()->is_active);
    }

    public function test_the_admin_list_switches_off_ended_slides_and_says_when_they_ended(): void
    {
        $slide = $this->makeSlide('Craft Fair', ['ends_at' => '2026-11-15']);

        $this->travelToLocal('2026-11-20 09:00');
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.carousel.index'))->assertOk()->assertDontSee('Ended Nov 15');
        $this->assertFalse($slide->fresh()->is_active);

        $this->actingAs($admin)
            ->get(route('admin.carousel.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Ended Nov 15');
    }

    public function test_the_list_defaults_to_active_slides_with_inactive_and_all_tabs(): void
    {
        $this->makeSlide('Live Slide');
        $this->makeSlide('Coming Soon', ['starts_at' => '2026-12-01']);
        $this->makeSlide('Switched Off', ['is_active' => false]);
        $admin = $this->makeAdmin();
        $this->travelToLocal('2026-11-20 09:00');

        $html = $this->actingAs($admin)->get(route('admin.carousel.index'))
            ->assertOk()
            ->assertSee('value="Live Slide"', false)
            ->assertSee('value="Coming Soon"', false)
            ->assertDontSee('value="Switched Off"', false)
            ->assertSee('Starts Dec 1')
            ->getContent();
        $this->assertMatchesRegularExpression('/aria-current="page"[^>]*>\s*Active\s*<span[^>]*>2</', $html);

        $this->actingAs($admin)->get(route('admin.carousel.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('value="Switched Off"', false)
            ->assertDontSee('value="Live Slide"', false);

        $this->actingAs($admin)->get(route('admin.carousel.index', ['status' => 'all']))
            ->assertOk()
            ->assertSee('value="Live Slide"', false)
            ->assertSee('value="Switched Off"', false);

        // Unknown values fall back to Active.
        $this->actingAs($admin)->get(route('admin.carousel.index', ['status' => 'bogus']))
            ->assertOk()
            ->assertDontSee('value="Switched Off"', false);
    }

    public function test_saving_and_deleting_return_to_the_same_tab(): void
    {
        $slide = $this->makeSlide('Switched Off', ['is_active' => false]);
        $other = $this->makeSlide('Old Slide', ['is_active' => false]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.carousel.update', $slide), ['caption' => 'Renamed', 'is_active' => 0, 'return_status' => 'inactive'])
            ->assertRedirect(route('admin.carousel.index', ['status' => 'inactive']));
        $this->assertSame('Renamed', $slide->fresh()->caption);

        $this->actingAs($admin)
            ->delete(route('admin.carousel.destroy', $other), ['return_status' => 'all'])
            ->assertRedirect(route('admin.carousel.index', ['status' => 'all']));
        $this->assertModelMissing($other);
    }

    public function test_an_ended_slide_cannot_be_reactivated_without_changing_its_dates(): void
    {
        $slide = $this->makeSlide('Craft Fair', ['ends_at' => '2026-11-15', 'is_active' => false]);
        $admin = $this->makeAdmin();
        $this->travelToLocal('2026-11-20 09:00');

        $this->actingAs($admin)
            ->patch(route('admin.carousel.update', $slide), ['caption' => 'Craft Fair', 'is_active' => 1, 'ends_at' => '2026-11-15'])
            ->assertSessionHasErrors('ends_at');
        $this->assertFalse($slide->fresh()->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.carousel.update', $slide), ['caption' => 'Craft Fair', 'is_active' => 1, 'ends_at' => '2026-11-30'])
            ->assertSessionHasNoErrors();
        $this->assertTrue($slide->fresh()->is_active);
    }

    public function test_validation_errors_are_shown_on_the_carousel_page(): void
    {
        $slide = $this->makeSlide('Craft Fair', ['ends_at' => '2026-11-15', 'is_active' => false]);
        $this->travelToLocal('2026-11-20 09:00');

        $this->actingAs($this->makeAdmin())
            ->from(route('admin.carousel.index'))
            ->followingRedirects()
            ->patch(route('admin.carousel.update', $slide), ['caption' => 'Craft Fair', 'is_active' => 1, 'ends_at' => '2026-11-15'])
            ->assertOk()
            ->assertSee('end date has passed', false);
    }

    public function test_slides_without_an_end_date_are_never_switched_off(): void
    {
        $slide = $this->makeSlide('Ski Hill', ['starts_at' => '2026-09-28']);

        $this->travelToLocal('2027-03-01 12:00');
        $this->assertSame(0, CarouselSlide::deactivateExpired());
        $this->assertTrue($slide->fresh()->is_active);
        $this->get(route('events.index'))->assertOk()->assertSee('Ski Hill');
    }
}
