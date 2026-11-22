<?php

namespace Tests\Feature;

use App\Models\CarouselSlide;
use App\Models\CarouselSlideDay;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Carousel slides added to the /events calendar with their own title, dates/hours,
 * location and details. The slideshow itself is unchanged.
 */
class CarouselSlideCalendarTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
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

    private function calendar(int $month, int $year = 2030): TestResponse
    {
        return $this->get(route('events.index', ['view' => 'calendar', 'month' => $month, 'year' => $year]))->assertOk();
    }

    private function daysShown(TestResponse $response, CarouselSlide $slide): int
    {
        return substr_count($response->getContent(), 'openSlideModal('.$slide->id.')');
    }

    // ── Public calendar ─────────────────────────────────────────────────────

    public function test_a_slide_on_the_calendar_shows_on_its_date_with_its_details(): void
    {
        $slide = $this->makeSlide('Truck Parade', [
            'show_on_calendar' => true,
            'calendar_date' => '2030-12-06',
            'calendar_start_time' => '18:00',
            'calendar_end_time' => '19:30',
            'calendar_location' => 'Main Street',
            'calendar_description' => 'Lit-up trucks down Main Street.',
        ]);

        $response = $this->calendar(12);

        $this->assertSame(1, $this->daysShown($response, $slide));
        $response->assertSee('Truck Parade')
            ->assertSee('6 PM – 7:30 PM')
            ->assertSee('Main Street')
            ->assertSee('Lit-up trucks down Main Street.');
    }

    public function test_the_calendar_title_is_the_caption_until_an_admin_changes_it(): void
    {
        $slide = $this->makeSlide('Truck Parade', ['show_on_calendar' => true, 'calendar_date' => '2030-12-06']);
        $this->assertSame('Truck Parade', $slide->calendar_display_title);

        $slide->update(['caption' => 'Truck Parade 2030']);
        $this->calendar(12)->assertSee('Truck Parade 2030');

        $slide->update(['calendar_title' => 'Christmas Truck Parade']);
        $this->assertSame('Christmas Truck Parade', $slide->fresh()->calendar_display_title);
        $this->calendar(12)->assertSee('Christmas Truck Parade');
    }

    public function test_admin_changes_the_calendar_title_and_clearing_it_goes_back_to_the_caption(): void
    {
        $slide = $this->makeSlide('Truck Parade');
        $admin = $this->makeAdmin();
        $payload = ['show_on_calendar' => '1', 'schedule_mode' => 'single', 'event_date' => '2030-12-06'];

        $this->actingAs($admin)->put(route('admin.carousel.calendar.update', $slide), $payload + ['calendar_title' => 'Christmas Truck Parade']);
        $this->assertSame('Christmas Truck Parade', $slide->fresh()->calendar_title);

        // Leaving the prefilled caption unchanged keeps following the caption.
        $this->actingAs($admin)->put(route('admin.carousel.calendar.update', $slide), $payload + ['calendar_title' => 'Truck Parade']);
        $this->assertNull($slide->fresh()->calendar_title);

        $this->actingAs($admin)->put(route('admin.carousel.calendar.update', $slide), $payload + ['calendar_title' => 'Custom']);
        $this->actingAs($admin)->put(route('admin.carousel.calendar.update', $slide), $payload + ['calendar_title' => '']);
        $this->assertNull($slide->fresh()->calendar_title);
        $this->assertSame('Truck Parade', $slide->fresh()->calendar_display_title);
    }

    public function test_a_multi_day_slide_shows_on_every_day_including_across_months(): void
    {
        $slide = $this->makeSlide('Light Festival', [
            'show_on_calendar' => true,
            'calendar_date' => '2030-11-29',
            'calendar_end_date' => '2030-12-03',
        ]);

        $this->assertSame(3, $this->daysShown($this->calendar(12), $slide));
        $this->assertSame(2, $this->daysShown($this->calendar(11), $slide));
    }

    public function test_a_slide_with_schedule_days_shows_only_on_those_days_with_their_hours(): void
    {
        $slide = $this->makeSlide('Craft Fair', ['show_on_calendar' => true, 'calendar_date' => '2030-12-05', 'calendar_end_date' => '2030-12-19']);
        CarouselSlideDay::factory()->create(['carousel_slide_id' => $slide->id, 'date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00']);
        CarouselSlideDay::factory()->create(['carousel_slide_id' => $slide->id, 'date' => '2030-12-12', 'start_time' => '12:00', 'end_time' => '20:00']);
        CarouselSlideDay::factory()->create(['carousel_slide_id' => $slide->id, 'date' => '2030-12-19', 'start_time' => null, 'end_time' => null]);

        $response = $this->calendar(12);

        $this->assertSame(3, $this->daysShown($response, $slide));
        $response->assertSee('10 AM – 4 PM')->assertSee('12 PM – 8 PM');

        $payload = $slide->fresh('days')->toCalendarPayload();
        $this->assertSame('Dec 5, 12 & 19, 2030', $payload['date_label']);
        $this->assertSame(['10 AM – 4 PM', '12 PM – 8 PM', 'All day'], array_column($payload['day_times'], 'time'));
    }

    public function test_slides_not_on_the_calendar_are_not_shown(): void
    {
        $slide = $this->makeSlide('Ski Hill', ['show_on_calendar' => false, 'calendar_date' => '2030-12-05']);

        $this->assertSame(0, $this->daysShown($this->calendar(12), $slide));
    }

    public function test_an_inactive_slide_is_hidden_from_the_calendar(): void
    {
        $slide = $this->makeSlide('Past Parade', [
            'is_active' => false,
            'show_on_calendar' => true,
            'calendar_date' => '2030-12-06',
        ]);

        $this->assertSame(0, $this->daysShown($this->calendar(12), $slide));
    }

    public function test_calendar_details_do_not_change_the_slideshow(): void
    {
        $this->makeSlide('Hidden Slide', ['is_active' => false, 'show_on_calendar' => true, 'calendar_date' => now()->toDateString()]);
        $this->makeSlide('Live Slide', ['show_on_calendar' => false]);

        $this->assertSame(['Live Slide'], CarouselSlide::currentlyActive()->pluck('caption')->all());
    }

    // ── Public list view ────────────────────────────────────────────────────

    public function test_upcoming_calendar_slides_show_in_the_list_view_with_their_details(): void
    {
        $slide = $this->makeSlide('Truck Parade', [
            'show_on_calendar' => true,
            'calendar_date' => now()->addDays(5)->toDateString(),
            'calendar_start_time' => '18:00',
            'calendar_end_time' => '19:30',
            'calendar_location' => 'Main Street',
            // Not in the slideshow yet, so any <img> of it would have to come from the list card.
            'starts_at' => now()->addDays(10)->toDateString(),
        ]);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('openSlideModal('.$slide->id.')', false)
            ->assertSee('Truck Parade')
            ->assertSee('6:00 PM – 7:30 PM')
            ->assertSee('Main Street')
            ->assertDontSee('Featured')
            ->assertDontSee('src="'.$slide->url.'"', false);
    }

    public function test_a_multi_day_slide_stays_in_the_list_until_its_last_day(): void
    {
        $ongoing = $this->makeSlide('Light Festival', [
            'show_on_calendar' => true,
            'calendar_date' => now()->subDays(2)->toDateString(),
            'calendar_end_date' => now()->addDay()->toDateString(),
        ]);
        $finished = $this->makeSlide('Old Parade', ['show_on_calendar' => true, 'calendar_date' => now()->subDays(3)->toDateString()]);
        $hidden = $this->makeSlide('Ski Hill', ['show_on_calendar' => false, 'calendar_date' => now()->addDay()->toDateString()]);

        $response = $this->get(route('events.index'))->assertOk();

        $response->assertSee('openSlideModal('.$ongoing->id.')', false);
        $response->assertDontSee('openSlideModal('.$finished->id.')', false);
        $response->assertDontSee('openSlideModal('.$hidden->id.')', false);
    }

    public function test_the_category_shows_as_a_badge_on_the_card_and_in_the_popup_data(): void
    {
        $slide = $this->makeSlide('Truck Parade', [
            'show_on_calendar' => true,
            'calendar_date' => now()->addDay()->toDateString(),
            'calendar_category' => 'Holiday Fun',
        ]);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('<span class="category-badge text-xs font-semibold px-3 py-1 rounded-full inline-block mb-3 w-fit">', false)
            ->assertSee('Holiday Fun');

        $this->assertSame('Holiday Fun', $slide->toCalendarPayload()['category']);
    }

    public function test_slides_follow_the_category_filter_in_both_views(): void
    {
        $date = now()->addDay();
        $parade = $this->makeSlide('Truck Parade', ['show_on_calendar' => true, 'calendar_date' => $date->toDateString(), 'calendar_category' => 'Holiday Fun']);
        $concert = $this->makeSlide('Concert', ['show_on_calendar' => true, 'calendar_date' => $date->toDateString(), 'calendar_category' => 'Music']);
        $plain = $this->makeSlide('Ski Hill', ['show_on_calendar' => true, 'calendar_date' => $date->toDateString()]);

        foreach ([['category' => 'Holiday Fun'], ['category' => 'Holiday Fun', 'view' => 'calendar', 'month' => $date->month, 'year' => $date->year]] as $query) {
            $response = $this->get(route('events.index', $query))->assertOk();

            $response->assertSee('openSlideModal('.$parade->id.')', false);
            $response->assertDontSee('openSlideModal('.$concert->id.')', false);
            $response->assertDontSee('openSlideModal('.$plain->id.')', false);
        }

        $this->get(route('events.index'))->assertSee('openSlideModal('.$plain->id.')', false);
    }

    public function test_slide_categories_are_offered_in_the_category_filter(): void
    {
        $this->makeSlide('Truck Parade', ['show_on_calendar' => true, 'calendar_date' => now()->addDay()->toDateString(), 'calendar_category' => 'Holiday Fun']);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee(route('events.index', ['category' => 'Holiday Fun']), false);
    }

    public function test_admin_saves_the_category_and_existing_categories_are_suggested(): void
    {
        $slide = $this->makeSlide('Truck Parade');
        Event::factory()->create(['category' => 'Community']);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.carousel.calendar.edit', $slide))
            ->assertSee('name="calendar_category"', false)
            ->assertSee('<option value="Community">', false);

        $this->actingAs($admin)
            ->put(route('admin.carousel.calendar.update', $slide), [
                'show_on_calendar' => '1',
                'schedule_mode' => 'single',
                'event_date' => '2030-12-06',
                'calendar_category' => '  Holiday Fun ',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Holiday Fun', $slide->fresh()->calendar_category);
    }

    // ── Admin ───────────────────────────────────────────────────────────────

    public function test_admin_can_open_the_calendar_page_prefilled_from_the_slideshow_window(): void
    {
        $slide = $this->makeSlide('Truck Parade', ['starts_at' => '2030-11-20', 'ends_at' => '2030-12-06']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.carousel.calendar.edit', $slide))
            ->assertOk()
            ->assertSee('Show on the events calendar')
            ->assertSee('name="calendar_title"', false)
            ->assertSee('value="Truck Parade"', false)
            ->assertSee('value="2030-11-20"', false)
            ->assertSee('value="2030-12-06"', false);
    }

    public function test_the_carousel_list_links_to_each_slides_calendar_page(): void
    {
        $slide = $this->makeSlide('Truck Parade', ['show_on_calendar' => true, 'calendar_date' => '2030-12-06']);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.carousel.index'))
            ->assertOk()
            ->assertSee(route('admin.carousel.calendar.edit', $slide))
            ->assertSee('Dec 6');
    }

    public function test_admin_saves_a_single_date_range(): void
    {
        $slide = $this->makeSlide('Truck Parade');

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.carousel.calendar.update', $slide), [
                'show_on_calendar' => '1',
                'calendar_location' => 'Main Street',
                'calendar_description' => 'Lit-up trucks.',
                'calendar_url' => 'https://example.com/parade',
                'schedule_mode' => 'single',
                'event_date' => '2030-12-06',
                'end_date' => '2030-12-07',
                'event_time' => '18:00',
                'end_time' => '19:30',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.carousel.calendar.edit', $slide));

        $slide->refresh();
        $this->assertTrue($slide->show_on_calendar);
        $this->assertSame('Truck Parade', $slide->calendar_display_title);
        $this->assertSame('2030-12-06', $slide->calendar_date->toDateString());
        $this->assertSame('2030-12-07', $slide->calendar_end_date->toDateString());
        $this->assertSame('18:00', $slide->calendar_start_time->format('H:i'));
        $this->assertSame('19:30', $slide->calendar_end_time->format('H:i'));
        $this->assertSame('Main Street', $slide->calendar_location);
        $this->assertSame('https://example.com/parade', $slide->calendar_url);
        $this->assertCount(0, $slide->days);
    }

    public function test_admin_saves_schedule_days_and_the_summary_follows_them(): void
    {
        $slide = $this->makeSlide('Craft Fair');

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.carousel.calendar.update', $slide), [
                'show_on_calendar' => '1',
                'schedule_mode' => 'days',
                'days' => [
                    ['date' => '2030-12-19', 'start_time' => '12:00', 'end_time' => '20:00', 'note' => ''],
                    ['date' => '2030-12-05', 'start_time' => '10:00', 'end_time' => '16:00', 'note' => 'Opening day'],
                ],
                // Stale single-mode values are ignored in days mode.
                'event_date' => 'not a date',
            ])
            ->assertSessionHasNoErrors();

        $slide->refresh();
        $this->assertSame('2030-12-05', $slide->calendar_date->toDateString());
        $this->assertSame('2030-12-19', $slide->calendar_end_date->toDateString());
        $this->assertSame('10:00', $slide->calendar_start_time->format('H:i'));
        $this->assertSame(['2030-12-05', '2030-12-19'], $slide->days->map(fn ($d) => $d->date->toDateString())->all());
        $this->assertSame('Opening day', $slide->days->first()->note);
    }

    public function test_switching_back_to_a_single_range_removes_the_schedule_days(): void
    {
        $slide = $this->makeSlide('Craft Fair', ['show_on_calendar' => true, 'calendar_date' => '2030-12-05']);
        CarouselSlideDay::factory()->count(2)->create(['carousel_slide_id' => $slide->id]);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.carousel.calendar.update', $slide), [
                'show_on_calendar' => '1',
                'schedule_mode' => 'single',
                'event_date' => '2030-12-10',
            ])
            ->assertSessionHasNoErrors();

        $this->assertCount(0, $slide->fresh()->days);
    }

    public function test_turning_the_calendar_off_keeps_the_saved_details(): void
    {
        $slide = $this->makeSlide('Craft Fair', ['show_on_calendar' => true, 'calendar_date' => '2030-12-05', 'calendar_location' => 'Bovill Square']);
        CarouselSlideDay::factory()->create(['carousel_slide_id' => $slide->id, 'date' => '2030-12-05']);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.carousel.calendar.update', $slide), ['show_on_calendar' => '0'])
            ->assertSessionHasNoErrors();

        $slide->refresh();
        $this->assertFalse($slide->show_on_calendar);
        $this->assertSame('Bovill Square', $slide->calendar_location);
        $this->assertCount(1, $slide->days);
    }

    public function test_a_date_is_required_when_the_slide_is_on_the_calendar(): void
    {
        $slide = $this->makeSlide('Truck Parade');

        $this->actingAs($this->makeAdmin())
            ->from(route('admin.carousel.calendar.edit', $slide))
            ->put(route('admin.carousel.calendar.update', $slide), ['show_on_calendar' => '1', 'schedule_mode' => 'single'])
            ->assertSessionHasErrors(['event_date' => 'Enter the date the event starts.'])
            ->assertRedirect(route('admin.carousel.calendar.edit', $slide));

        $this->assertFalse($slide->fresh()->show_on_calendar);
    }

    public function test_an_end_date_before_the_start_and_a_bad_link_are_rejected(): void
    {
        $slide = $this->makeSlide('Truck Parade');

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.carousel.calendar.update', $slide), [
                'show_on_calendar' => '1',
                'schedule_mode' => 'single',
                'event_date' => '2030-12-06',
                'end_date' => '2030-12-01',
                'calendar_url' => 'not-a-link',
            ])
            ->assertSessionHasErrors(['end_date', 'calendar_url']);
    }

    public function test_guests_cannot_edit_calendar_details(): void
    {
        $slide = $this->makeSlide('Truck Parade');

        $this->get(route('admin.carousel.calendar.edit', $slide))->assertRedirect();
        $this->put(route('admin.carousel.calendar.update', $slide), ['show_on_calendar' => '1'])->assertRedirect();
        $this->assertFalse($slide->fresh()->show_on_calendar);
    }

    public function test_deleting_a_slide_removes_its_schedule_days(): void
    {
        $slide = $this->makeSlide('Craft Fair', ['show_on_calendar' => true, 'calendar_date' => '2030-12-05']);
        CarouselSlideDay::factory()->create(['carousel_slide_id' => $slide->id]);

        $this->actingAs($this->makeAdmin())->delete(route('admin.carousel.destroy', $slide));

        $this->assertSame(0, CarouselSlideDay::count());
    }
}
