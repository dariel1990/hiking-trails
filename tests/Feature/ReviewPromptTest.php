<?php

namespace Tests\Feature;

use App\Models\ReviewPromptEvent;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_url_uses_a_business_profile_short_link_verbatim(): void
    {
        Setting::set('google_review_link', 'https://g.page/r/CRrb7X7uacc0EBM/review');

        $this->assertSame('https://g.page/r/CRrb7X7uacc0EBM/review', review_url('web'));
    }

    public function test_web_url_builds_a_write_review_url_from_a_raw_place_id(): void
    {
        Setting::set('google_review_link', 'ChIJExamplePlaceId');

        $this->assertSame(
            'https://search.google.com/local/writereview?placeid=ChIJExamplePlaceId',
            review_url('web')
        );
    }

    public function test_web_url_is_null_when_the_review_link_is_blank(): void
    {
        Setting::set('google_review_link', '   ');

        $this->assertNull(review_url('web'));
    }

    public function test_feedback_email_falls_back_to_the_developer_email(): void
    {
        Setting::set('review_feedback_email', null);
        Setting::set('developer_email', 'dev@example.com');

        $this->assertSame('dev@example.com', review_feedback_email());

        Setting::set('review_feedback_email', 'hello@example.com');

        $this->assertSame('hello@example.com', review_feedback_email());
    }

    public function test_the_prompt_renders_on_a_public_page_once_configured(): void
    {
        Setting::set('google_review_link', 'https://g.page/r/CRrb7X7uacc0EBM/review');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('xs-review-modal');
        $response->assertSee('Leave a review');
        $response->assertSee('Send us feedback');
        $response->assertSee('Review us on Google');
    }

    public function test_the_prompt_is_absent_when_disabled(): void
    {
        Setting::set('google_review_link', 'https://g.page/r/CRrb7X7uacc0EBM/review');
        Setting::set('review_prompt_enabled', false);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('xs-review-modal');
    }

    public function test_the_prompt_is_absent_when_no_review_link_is_configured(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('xs-review-modal');
    }

    public function test_the_event_beacon_records_an_event_without_an_app_key(): void
    {
        $response = $this->postJson('/api/review-prompt/event', [
            'channel' => 'web',
            'action' => 'review_clicked',
            'trigger' => 'engagement',
            'page_url' => '/trails/example',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('review_prompt_events', [
            'channel' => 'web',
            'action' => 'review_clicked',
            'trigger' => 'engagement',
            'page_url' => '/trails/example',
            'user_id' => null,
        ]);
    }

    public function test_the_event_beacon_rejects_an_unknown_action(): void
    {
        $this->postJson('/api/review-prompt/event', [
            'channel' => 'web',
            'action' => 'five_stars_please',
        ])->assertStatus(422);

        $this->assertSame(0, ReviewPromptEvent::query()->count());
    }

    public function test_the_settings_page_exposes_the_review_prompt_group(): void
    {
        $this->assertArrayHasKey('reviews', config('settings.groups'));

        $reviewKeys = collect(config('settings.definitions'))
            ->filter(fn (array $definition): bool => $definition['group'] === 'reviews')
            ->keys();

        $this->assertContains('review_prompt_enabled', $reviewKeys);
        $this->assertContains('google_review_link', $reviewKeys);
        $this->assertContains('review_prompt_snooze_days', $reviewKeys);
    }

    public function test_an_admin_can_save_the_review_prompt_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'reviews']))
            ->assertOk()
            ->assertSee('Google review link');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'group' => 'reviews',
                'review_prompt_enabled' => '1',
                'google_review_link' => 'https://g.page/r/CRrb7X7uacc0EBM/review',
                'review_prompt_min_page_views' => 6,
                'review_prompt_min_sessions' => 3,
                'review_prompt_min_days' => 1,
                'review_prompt_snooze_days' => 120,
                'review_feedback_email' => 'hello@example.com',
            ])
            ->assertRedirect();

        $this->assertSame('https://g.page/r/CRrb7X7uacc0EBM/review', setting('google_review_link'));
        $this->assertSame(120, setting('review_prompt_snooze_days'));
        $this->assertTrue(setting('review_prompt_enabled'));
    }

    public function test_the_device_analytics_page_shows_the_review_prompt_totals(): void
    {
        ReviewPromptEvent::query()->create(['channel' => 'web', 'action' => 'shown', 'trigger' => 'engagement']);
        ReviewPromptEvent::query()->create(['channel' => 'web', 'action' => 'review_clicked']);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.device-analytics.index'))
            ->assertOk()
            ->assertSee('Review Prompt')
            ->assertSee('Went to Review');
    }
}
