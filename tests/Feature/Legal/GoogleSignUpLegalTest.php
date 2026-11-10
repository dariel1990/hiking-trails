<?php

namespace Tests\Feature\Legal;

use App\Models\LegalAcceptance;
use App\Models\LegalDocumentVersion;
use App\Models\User;
use App\Services\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GoogleSignUpLegalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Notification::fake();
    }

    private function signInWithGoogle(string $email): void
    {
        Socialite::shouldReceive('driver->user')->andReturn(
            (new SocialiteUser)->map(['id' => 'google-1', 'name' => 'Jane Hiker', 'email' => $email])
        );

        $this->get(route('google.callback'))->assertRedirect(route('home'));
    }

    /**
     * @return array<string, mixed>
     */
    private function consent(array $overrides = []): array
    {
        return [
            'terms' => '1',
            'privacy' => '1',
            'accepted_versions' => app(LegalDocumentService::class)->currentVersions()->pluck('id')->all(),
            ...$overrides,
        ];
    }

    public function test_register_page_reviews_documents_before_going_to_google(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('continueWithGoogle()', false)
            ->assertSee(route('google.accept'), false);
    }

    public function test_accepting_on_the_register_page_continues_to_google(): void
    {
        $this->post(route('google.accept'), $this->consent())->assertRedirect(route('google.redirect'));

        $this->assertSame(0, LegalAcceptance::query()->count());
    }

    public function test_acceptance_is_recorded_when_google_returns_and_no_prompt_follows(): void
    {
        LegalDocumentVersion::factory()->material()->create();
        $this->post(route('google.accept'), $this->consent());

        $this->signInWithGoogle('new-hiker@example.com');

        $user = User::query()->where('email', 'new-hiker@example.com')->firstOrFail();

        $this->assertSame(2, $user->legalAcceptances()->where('source', LegalAcceptance::SOURCE_WEB)->count());
        $this->assertTrue(app(LegalDocumentService::class)->pendingFor($user)->isEmpty());
        $this->assertNull(session('google_sign_up.accepted_versions'));
    }

    public function test_a_document_published_during_google_sign_in_is_still_reviewed(): void
    {
        $this->post(route('google.accept'), $this->consent());
        LegalDocumentVersion::factory()->material()->create();

        $this->signInWithGoogle('new-hiker@example.com');

        $this->assertSame(0, LegalAcceptance::query()->count());
        $this->get(route('home'))->assertOk()->assertSee('Review updated terms');
    }

    public function test_an_outdated_document_returns_to_the_review(): void
    {
        $payload = $this->consent();
        LegalDocumentVersion::factory()->material()->create();

        $this->post(route('google.accept'), $payload)
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('accepted_versions')
            ->assertSessionHas('legalReviewAction', 'google');

        $this->assertNull(session('google_sign_up.accepted_versions'));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function requiredConsentFields(): array
    {
        return [
            'terms' => ['terms'],
            'privacy' => ['privacy'],
            'accepted_versions' => ['accepted_versions'],
        ];
    }

    #[DataProvider('requiredConsentFields')]
    public function test_every_acceptance_is_required_before_going_to_google(string $field): void
    {
        $payload = $this->consent();
        unset($payload[$field]);

        $this->post(route('google.accept'), $payload)->assertSessionHasErrors($field);

        $this->assertNull(session('google_sign_up.accepted_versions'));
    }

    public function test_a_new_google_account_must_review_both_documents_before_using_the_site(): void
    {
        LegalDocumentVersion::factory()->material()->create(['content' => 'New terms wording.']);

        $this->signInWithGoogle('new-hiker@example.com');

        $this->get(route('home'))->assertOk()->assertSee('Review updated terms');

        $this->get(route('legal.accept.show'))
            ->assertOk()
            ->assertSee('New terms wording.')
            ->assertSee('Information We Collect');
    }

    public function test_an_existing_google_account_that_never_accepted_is_asked_about_the_terms(): void
    {
        LegalDocumentVersion::factory()->material()->create(['content' => 'New terms wording.']);
        User::factory()->create(['email' => 'old-hiker@example.com', 'created_at' => now()->subYear()]);

        $this->signInWithGoogle('old-hiker@example.com');

        $this->get(route('home'))->assertOk()->assertSee('Review updated terms');

        $this->get(route('legal.accept.show'))
            ->assertSee('New terms wording.')
            ->assertDontSee('Information We Collect');
    }
}
