<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\VerifyAppKey;
use App\Models\LegalAcceptance;
use App\Models\LegalDocumentVersion;
use App\Models\User;
use App\Services\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LegalAcceptanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyAppKey::class);
        Notification::fake();
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
            ...$overrides,
        ];
    }

    private function existingUser(): User
    {
        return User::factory()->create(['created_at' => now()->subYear()]);
    }

    /**
     * @return array<int, int>
     */
    private function currentVersionIds(): array
    {
        return app(LegalDocumentService::class)->currentVersions()->pluck('id')->all();
    }

    public function test_current_documents_are_public_and_complete(): void
    {
        $this->getJson('/api/legal/current')
            ->assertOk()
            ->assertJsonCount(2, 'documents')
            ->assertJsonPath('documents.0.type', LegalDocumentVersion::TERMS)
            ->assertJsonPath('documents.1.type', LegalDocumentVersion::PRIVACY)
            ->assertJsonPath('documents.0.html', fn (string $html): bool => str_contains($html, 'Assumption of Risk'))
            ->assertJsonPath('documents.1.html', fn (string $html): bool => str_contains($html, setting('contact_address')));
    }

    public function test_register_records_the_accepted_versions(): void
    {
        $this->postJson('/api/auth/register', $this->registration(['accepted_versions' => $this->currentVersionIds()]))->assertCreated();

        $this->assertSame(2, LegalAcceptance::query()->where('source', LegalAcceptance::SOURCE_API)->count());
    }

    public function test_register_with_an_outdated_document_creates_no_account(): void
    {
        $outdated = $this->currentVersionIds();
        $current = LegalDocumentVersion::factory()->material()->create();

        $this->postJson('/api/auth/register', $this->registration(['accepted_versions' => $outdated]))
            ->assertStatus(409)
            ->assertJsonPath('documents.0.id', $current->id);

        $this->assertFalse(User::query()->where('email', 'jane@example.com')->exists());
    }

    public function test_register_with_only_one_document_accepted_is_rejected(): void
    {
        $this->postJson('/api/auth/register', $this->registration(['accepted_versions' => [$this->currentVersionIds()[0]]]))
            ->assertStatus(409);

        $this->assertSame(0, LegalAcceptance::query()->count());
    }

    public function test_new_accounts_from_older_app_builds_become_pending(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $this->postJson('/api/auth/register', $this->registration())->assertCreated();

        Sanctum::actingAs(User::query()->where('email', 'jane@example.com')->firstOrFail());

        $this->getJson('/api/legal/status')->assertJsonCount(2, 'pending');
    }

    public function test_older_app_builds_can_still_register_without_the_field(): void
    {
        $this->postJson('/api/auth/register', $this->registration())->assertCreated();

        $this->assertSame(0, LegalAcceptance::query()->count());
    }

    public function test_register_rejects_malformed_accepted_versions(): void
    {
        $this->postJson('/api/auth/register', $this->registration(['accepted_versions' => 'all']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('accepted_versions');
    }

    public function test_status_requires_authentication(): void
    {
        $this->getJson('/api/legal/status')->assertUnauthorized();
    }

    public function test_status_is_empty_without_a_material_change(): void
    {
        Sanctum::actingAs($this->existingUser());

        $this->getJson('/api/legal/status')->assertOk()->assertExactJson(['pending' => []]);
    }

    public function test_status_lists_pending_versions(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create(['change_summary' => 'New billing terms.']);
        Sanctum::actingAs($this->existingUser());

        $this->getJson('/api/legal/status')
            ->assertOk()
            ->assertJsonPath('pending.0.id', $version->id)
            ->assertJsonPath('pending.0.type', LegalDocumentVersion::TERMS)
            ->assertJsonPath('pending.0.change_summary', 'New billing terms.')
            ->assertJsonPath('pending.0.url', route('terms'));
    }

    public function test_accept_records_and_clears_pending(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $user = $this->existingUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/legal/accept', ['versions' => [$version->id], 'accepted' => true])
            ->assertOk()
            ->assertExactJson(['pending' => []]);

        $this->assertDatabaseHas('legal_acceptances', [
            'user_id' => $user->id,
            'legal_document_version_id' => $version->id,
            'source' => LegalAcceptance::SOURCE_API,
        ]);
        $this->getJson('/api/legal/status')->assertExactJson(['pending' => []]);
    }

    public function test_accept_rejects_a_stale_version_with_a_conflict(): void
    {
        $stale = LegalDocumentVersion::factory()->material()->create();
        $current = LegalDocumentVersion::factory()->material()->create();
        $user = $this->existingUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/legal/accept', ['versions' => [$stale->id], 'accepted' => true])
            ->assertStatus(409)
            ->assertJsonPath('pending.0.id', $current->id);

        $this->assertSame(0, $user->legalAcceptances()->count());
    }

    public function test_accept_requires_explicit_agreement(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        Sanctum::actingAs($this->existingUser());

        $this->postJson('/api/legal/accept', ['versions' => [$version->id]])
            ->assertStatus(422)
            ->assertJsonValidationErrors('accepted');
    }

    public function test_pending_acceptance_does_not_block_the_api(): void
    {
        LegalDocumentVersion::factory()->material()->create();
        Sanctum::actingAs($this->existingUser());

        $this->getJson('/api/me')->assertOk();
    }
}
