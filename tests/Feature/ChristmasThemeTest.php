<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Each event has a Christmas theme toggle; when it is on, the event page
 * gets the festive look (lights, snow, holiday fonts, sign-off) in every layout.
 */
class ChristmasThemeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Light Up Night',
            'event_date' => now()->addDays(5)->toDateString(),
            'event_time' => '18:30',
            'venue' => 'Bovill Square',
            'theme' => Event::THEME_STANDARD,
            'is_active' => '1',
        ], $overrides);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function layouts(): array
    {
        return [
            'premium' => ['premium'],
            'booking' => ['booking'],
            'minimal' => ['minimal'],
            'editorial' => ['editorial'],
            'corporate' => ['corporate'],
        ];
    }

    public function test_the_event_form_has_a_christmas_toggle(): void
    {
        $html = $this->actingAs($this->admin())
            ->get(route('admin.local-events.create'))
            ->assertOk()
            ->assertSee('Christmas theme')
            ->assertSee('<input type="hidden" name="theme" value="standard">', false)
            ->assertSee('name="theme" value="christmas"', false)
            ->getContent();

        preg_match('~<input type="checkbox" id="theme"[^>]*>~s', $html, $checkbox);
        $this->assertStringNotContainsString('checked', $checkbox[0]);
    }

    public function test_the_toggle_is_on_when_editing_a_christmas_event(): void
    {
        $event = Event::factory()->christmas()->create();

        $html = $this->actingAs($this->admin())
            ->get(route('admin.local-events.edit', $event))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('~name="theme" value="christmas"[^>]*checked~s', $html);
    }

    public function test_turning_the_toggle_on_makes_a_christmas_event(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.local-events.store'), $this->eventPayload(['theme' => Event::THEME_CHRISTMAS]))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Event::where('title', 'Light Up Night')->firstOrFail()->isChristmasEvent());
    }

    public function test_leaving_the_toggle_off_makes_a_standard_event(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.local-events.store'), $this->eventPayload())
            ->assertSessionHasNoErrors();

        $this->assertFalse(Event::where('title', 'Light Up Night')->firstOrFail()->isChristmasEvent());
    }

    public function test_turning_the_toggle_off_removes_the_christmas_theme(): void
    {
        $event = Event::factory()->christmas()->create(['title' => 'Light Up Night']);

        $this->actingAs($this->admin())
            ->put(route('admin.local-events.update', $event), $this->eventPayload())
            ->assertSessionHasNoErrors();

        $this->assertFalse($event->fresh()->isChristmasEvent());
    }

    public function test_the_admin_list_marks_christmas_events(): void
    {
        Event::factory()->christmas()->create(['title' => 'Light Up Night']);
        Event::factory()->create(['title' => 'Ski Swap']);

        $response = $this->actingAs($this->admin())->get(route('admin.local-events.index'))->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), '🎄 Christmas'));
    }

    #[DataProvider('layouts')]
    public function test_christmas_events_look_festive_in_every_layout(string $layout): void
    {
        Setting::set('event_page_layout', $layout);
        $event = Event::factory()->christmas()->create(['image_url' => 'https://example.com/parade.jpg']);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('data-theme="christmas"', false)
            ->assertSee('Mountains+of+Christmas', false)
            ->assertSee('event-title-festive', false)
            ->assertSee('xmas-bulbs-a', false)
            ->assertSee('class="xmas-snow', false)
            ->assertSee('Merry Christmas!')
            ->assertSee('Happy holidays from all of us at');
    }

    #[DataProvider('layouts')]
    public function test_standard_events_have_no_christmas_decorations(string $layout): void
    {
        Setting::set('event_page_layout', $layout);
        $event = Event::factory()->create(['image_url' => 'https://example.com/market.jpg']);

        $this->get(route('local-events.show', $event->slug))
            ->assertOk()
            ->assertSee('data-theme="standard"', false)
            ->assertDontSee('Mountains+of+Christmas', false)
            ->assertDontSee('xmas-bulbs-a', false)
            ->assertDontSee('xmas-snow', false)
            ->assertDontSee('Merry Christmas!');
    }
}
