<?php

namespace Tests\Feature\Legal;

use App\Models\LegalAcceptance;
use App\Models\LegalDocumentVersion;
use App\Models\User;
use App\Services\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegalAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private const NOTICE = 'Review updated terms';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function acceptancePayload(LegalDocumentVersion ...$versions): array
    {
        return ['versions' => array_map(fn (LegalDocumentVersion $version): int => $version->id, $versions), 'accepted' => '1'];
    }

    private function existingUser(): User
    {
        return User::factory()->create(['created_at' => now()->subYear()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function registration(array $overrides = []): array
    {
        return [
            'first_name' => 'Jane',
            'last_name' => 'Hiker',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
            'privacy' => '1',
            'accepted_versions' => app(LegalDocumentService::class)->currentVersions()->pluck('id')->all(),
            ...$overrides,
        ];
    }

    public function test_register_page_contains_both_documents_for_review(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Assumption of Risk')
            ->assertSee('Information We Collect')
            ->assertSee(setting('contact_address'));
    }

    public function test_registration_records_both_current_versions(): void
    {
        Notification::fake();

        $this->post(route('register.post'), $this->registration())->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

        $this->assertSame(2, $user->legalAcceptances()->where('source', LegalAcceptance::SOURCE_WEB)->count());
        $this->assertNotNull($user->legalAcceptances()->first()->ip_address);
    }

    public function test_new_registrations_are_not_prompted_after_accepting_in_the_modal(): void
    {
        Notification::fake();
        LegalDocumentVersion::factory()->material()->create();

        $this->post(route('register.post'), $this->registration());

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

        $this->assertTrue(app(LegalDocumentService::class)->pendingFor($user)->isEmpty());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function requiredAcceptanceFields(): array
    {
        return [
            'terms' => ['terms'],
            'privacy' => ['privacy'],
            'accepted_versions' => ['accepted_versions'],
        ];
    }

    #[DataProvider('requiredAcceptanceFields')]
    public function test_registration_requires_each_acceptance(string $field): void
    {
        $payload = $this->registration();
        unset($payload[$field]);

        $this->post(route('register.post'), $payload)->assertSessionHasErrors($field);

        $this->assertFalse(User::query()->where('email', 'jane@example.com')->exists());
        $this->assertSame(0, LegalAcceptance::query()->count());
    }

    public function test_registration_with_an_outdated_document_creates_no_account(): void
    {
        $payload = $this->registration();
        LegalDocumentVersion::factory()->material()->create();

        $this->post(route('register.post'), $payload)->assertSessionHasErrors('accepted_versions');

        $this->assertFalse(User::query()->where('email', 'jane@example.com')->exists());
    }

    public function test_new_account_without_acceptance_must_accept_both_documents(): void
    {
        LegalDocumentVersion::factory()->material()->create();
        $user = User::factory()->create();

        $this->assertSame(
            [LegalDocumentVersion::TERMS, LegalDocumentVersion::PRIVACY],
            app(LegalDocumentService::class)->pendingFor($user)->pluck('type')->all(),
        );
    }

    public function test_older_accounts_are_only_asked_about_the_material_document(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $this->assertSame(
            [LegalDocumentVersion::TERMS],
            app(LegalDocumentService::class)->pendingFor($this->existingUser())->pluck('type')->all(),
        );
    }

    public function test_accept_page_shows_the_full_document(): void
    {
        LegalDocumentVersion::factory()->material()->create(['content' => 'The complete new wording.']);

        $this->actingAs($this->existingUser())
            ->get(route('legal.accept.show'))
            ->assertOk()
            ->assertSee('The complete new wording.');
    }

    public function test_users_are_not_prompted_before_any_material_change(): void
    {
        $this->actingAs($this->existingUser())
            ->get(route('pro.show'))
            ->assertDontSee(self::NOTICE);

        $this->get(route('legal.accept.show'))->assertRedirect(route('home'));
    }

    public function test_a_non_material_publish_does_not_prompt(): void
    {
        LegalDocumentVersion::factory()->published()->create();

        $this->actingAs($this->existingUser())->get(route('pro.show'))->assertOk()->assertDontSee(self::NOTICE);
    }

    public function test_a_material_publish_shows_an_account_menu_notice_without_blocking_the_page(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create(['change_summary' => 'New liability wording.']);
        $user = $this->existingUser();

        $this->actingAs($user)
            ->get(route('pro.show', ['plan' => 'annual']))
            ->assertOk()
            ->assertSee(self::NOTICE)
            ->assertSee('data-legal-notice-dot', false)
            ->assertDontSee('Terms & Conditions and Privacy Policy');

        $this->withHeader('referer', route('pro.show', ['plan' => 'annual']))
            ->get(route('legal.accept.show'))
            ->assertOk()
            ->assertSee('New liability wording.');

        $this->post(route('legal.accept.store'), $this->acceptancePayload($version))
            ->assertRedirect(route('pro.show', ['plan' => 'annual']));

        $this->assertDatabaseHas('legal_acceptances', [
            'user_id' => $user->id,
            'legal_document_version_id' => $version->id,
            'source' => LegalAcceptance::SOURCE_WEB,
        ]);
    }

    public function test_accepted_users_are_not_prompted_again(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $user = $this->existingUser();

        $this->actingAs($user)->post(route('legal.accept.store'), $this->acceptancePayload($version));

        $this->get(route('pro.show'))->assertOk()->assertDontSee(self::NOTICE);
    }

    public function test_a_later_non_material_version_does_not_reprompt_accepted_users(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $user = $this->existingUser();
        $this->actingAs($user)->post(route('legal.accept.store'), $this->acceptancePayload($version));

        LegalDocumentVersion::factory()->published()->create();

        $this->get(route('pro.show'))->assertOk()->assertDontSee(self::NOTICE);
    }

    public function test_users_accept_the_live_version_when_newer_than_the_material_one(): void
    {
        LegalDocumentVersion::factory()->material()->create();
        $current = LegalDocumentVersion::factory()->published()->create(['content' => 'The latest live wording.']);
        $user = $this->existingUser();

        $this->actingAs($user)->get(route('legal.accept.show'))->assertSee('The latest live wording.');
        $this->post(route('legal.accept.store'), $this->acceptancePayload($current));

        $this->assertDatabaseHas('legal_acceptances', ['user_id' => $user->id, 'legal_document_version_id' => $current->id]);
    }

    public function test_two_material_documents_must_be_accepted_together(): void
    {
        $terms = LegalDocumentVersion::factory()->material()->create();
        $privacy = LegalDocumentVersion::factory()->privacy()->material()->create();
        $user = $this->existingUser();

        $this->actingAs($user)->post(route('legal.accept.store'), $this->acceptancePayload($terms))
            ->assertRedirect(route('legal.accept.show'));
        $this->assertSame(0, $user->legalAcceptances()->count());

        $this->post(route('legal.accept.store'), $this->acceptancePayload($terms, $privacy));
        $this->assertSame(2, $user->legalAcceptances()->count());
    }

    public function test_a_stale_version_is_rejected(): void
    {
        $stale = LegalDocumentVersion::factory()->material()->create();
        LegalDocumentVersion::factory()->material()->create();
        $user = $this->existingUser();

        $this->actingAs($user)
            ->post(route('legal.accept.store'), $this->acceptancePayload($stale))
            ->assertRedirect(route('legal.accept.show'))
            ->assertSessionHasErrors('versions');

        $this->assertSame(0, $user->legalAcceptances()->count());
    }

    public function test_the_checkbox_is_required(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $user = $this->existingUser();

        $this->actingAs($user)
            ->post(route('legal.accept.store'), ['versions' => [$version->id]])
            ->assertSessionHasErrors('accepted');

        $this->assertSame(0, $user->legalAcceptances()->count());
    }

    public function test_guests_are_unaffected(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $this->get(route('pro.show'))->assertOk()->assertDontSee(self::NOTICE);
        $this->get(route('legal.accept.show'))->assertRedirect(route('login'));
    }

    public function test_legal_pages_stay_readable_while_acceptance_is_pending(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $this->actingAs($this->existingUser());

        $this->get(route('terms'))->assertOk()->assertDontSee(self::NOTICE);
        $this->get(route('privacy-policy'))->assertOk()->assertDontSee(self::NOTICE);
    }

    public function test_users_can_log_out_while_acceptance_is_pending(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $this->actingAs($this->existingUser())->post(route('logout'))->assertRedirect();

        $this->assertGuest();
    }

    public function test_admins_are_not_locked_out_of_the_admin_panel(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.legal.index'))
            ->assertOk()
            ->assertDontSee(self::NOTICE);
    }

    public function test_new_accounts_see_both_documents_in_the_notice(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('pro.show'))
            ->assertOk()
            ->assertSee(self::NOTICE)
            ->assertSee('Terms & Conditions and Privacy Policy');
    }

    public function test_the_review_page_ignores_external_referers(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();

        $this->actingAs($this->existingUser())
            ->withHeader('referer', 'https://evil.example.com/page')
            ->get(route('legal.accept.show'))
            ->assertOk();

        $this->post(route('legal.accept.store'), $this->acceptancePayload($version))
            ->assertRedirect(route('home'));
    }

    public function test_json_requests_are_not_redirected(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $response = $this->actingAs($this->existingUser())->getJson(route('pro.show'));

        $this->assertFalse($response->isRedirect());
    }
}
