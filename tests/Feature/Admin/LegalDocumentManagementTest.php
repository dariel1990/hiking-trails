<?php

namespace Tests\Feature\Admin;

use App\Models\LegalDocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use LogicException;
use Tests\TestCase;

class LegalDocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    private function draftOf(string $type = LegalDocumentVersion::TERMS): ?LegalDocumentVersion
    {
        return LegalDocumentVersion::query()->draft()->where('type', $type)->first();
    }

    private function publishDraft(string $type = LegalDocumentVersion::TERMS, array $overrides = []): TestResponse
    {
        return $this->actingAs($this->admin)->post(route('admin.legal.draft.publish', $type), [
            'change_summary' => 'Updated the billing section.',
            'is_material' => '1',
            ...$overrides,
        ]);
    }

    public function test_guests_and_non_admins_cannot_access_the_admin_pages(): void
    {
        $this->get(route('admin.legal.index'))->assertRedirect();

        $response = $this->actingAs(User::factory()->create())->get(route('admin.legal.index'));

        $this->assertNotSame(200, $response->getStatusCode());
    }

    public function test_non_admins_cannot_publish(): void
    {
        LegalDocumentVersion::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('admin.legal.draft.publish', LegalDocumentVersion::TERMS), [
            'change_summary' => 'Sneaky.',
            'is_material' => '0',
        ]);

        $this->assertNotNull($this->draftOf());
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.legal.index'))->assertOk()->assertSee('Terms &amp; Conditions', false);
        $this->get(route('admin.legal.history', LegalDocumentVersion::PRIVACY))->assertOk()->assertSee('v1');
        $this->get(route('admin.legal.draft', LegalDocumentVersion::TERMS))->assertOk()->assertSee('Assumption of Risk');
        $this->get(route('admin.legal.version', [LegalDocumentVersion::TERMS, 1]))->assertOk()->assertSee('Assumption of Risk');
    }

    public function test_unknown_document_type_and_version_return_404(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin/legal/cookies')->assertNotFound();
        $this->get(route('admin.legal.version', [LegalDocumentVersion::TERMS, 99]))->assertNotFound();
    }

    public function test_saving_twice_keeps_a_single_draft(): void
    {
        $this->actingAs($this->admin);

        $this->put(route('admin.legal.draft.save', LegalDocumentVersion::TERMS), ['content' => 'First wording.'])->assertRedirect();
        $this->put(route('admin.legal.draft.save', LegalDocumentVersion::TERMS), ['content' => 'Second wording.'])->assertRedirect();

        $drafts = LegalDocumentVersion::query()->draft()->where('type', LegalDocumentVersion::TERMS)->get();

        $this->assertCount(1, $drafts);
        $this->assertSame('Second wording.', $drafts->first()->content);
        $this->assertSame($this->admin->id, $drafts->first()->created_by);
    }

    public function test_saving_a_draft_does_not_change_the_public_page(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.legal.draft.save', LegalDocumentVersion::TERMS), ['content' => 'Unpublished wording.']);

        $this->get(route('terms'))->assertDontSee('Unpublished wording.');
    }

    public function test_draft_line_endings_are_normalised(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.legal.draft.save', LegalDocumentVersion::TERMS), ['content' => "One\r\n\r\nTwo"]);

        $this->assertSame("One\n\nTwo", $this->draftOf()->content);
    }

    public function test_empty_draft_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.legal.draft.save', LegalDocumentVersion::TERMS), ['content' => ''])
            ->assertSessionHasErrors('content');

        $this->assertNull($this->draftOf());
    }

    public function test_preview_renders_markdown_and_strips_html(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.legal.preview'), ['content' => "## Heading\n\n<b>raw</b>"])
            ->assertOk()
            ->assertJsonPath('html', fn (string $html): bool => str_contains($html, '<h2>Heading</h2>') && ! str_contains($html, '<b>'));
    }

    public function test_publishing_creates_the_next_locked_version(): void
    {
        $draft = LegalDocumentVersion::factory()->create(['content' => 'Revised terms.']);

        $this->publishDraft()->assertRedirect(route('admin.legal.history', LegalDocumentVersion::TERMS));

        $draft->refresh();

        $this->assertSame(2, $draft->version);
        $this->assertNotNull($draft->published_at);
        $this->assertSame('Updated the billing section.', $draft->change_summary);
        $this->assertTrue($draft->is_material);
        $this->assertSame($this->admin->id, $draft->published_by);
        $this->get(route('terms'))->assertSee('Revised terms.');
    }

    public function test_version_numbers_are_independent_per_document(): void
    {
        LegalDocumentVersion::factory()->published()->create(['content' => 'Terms two.']);
        $privacyDraft = LegalDocumentVersion::factory()->privacy()->create(['content' => 'Privacy two.']);

        $this->publishDraft(LegalDocumentVersion::PRIVACY);

        $this->assertSame(2, $privacyDraft->refresh()->version);
    }

    public function test_publishing_requires_a_summary_and_a_material_choice(): void
    {
        LegalDocumentVersion::factory()->create(['content' => 'Revised.']);

        $this->actingAs($this->admin)
            ->post(route('admin.legal.draft.publish', LegalDocumentVersion::TERMS), [])
            ->assertSessionHasErrors(['change_summary', 'is_material']);

        $this->assertNotNull($this->draftOf());
    }

    public function test_publishing_without_a_draft_fails_cleanly(): void
    {
        $this->publishDraft()->assertSessionHasErrors('draft');

        $this->assertSame(1, LegalDocumentVersion::query()->where('type', LegalDocumentVersion::TERMS)->count());
    }

    public function test_publishing_identical_content_is_rejected(): void
    {
        $current = LegalDocumentVersion::query()->where('type', LegalDocumentVersion::TERMS)->first();
        LegalDocumentVersion::factory()->create(['content' => $current->content]);

        $this->publishDraft()->assertSessionHasErrors('draft');

        $this->assertNotNull($this->draftOf());
    }

    public function test_published_versions_cannot_be_changed(): void
    {
        $this->expectException(LogicException::class);

        LegalDocumentVersion::query()->published()->first()->update(['content' => 'Rewritten history.']);
    }

    public function test_published_versions_cannot_be_deleted(): void
    {
        $this->expectException(LogicException::class);

        LegalDocumentVersion::query()->published()->first()->delete();
    }

    public function test_discarding_removes_only_the_draft(): void
    {
        LegalDocumentVersion::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.legal.draft.discard', LegalDocumentVersion::TERMS))
            ->assertRedirect(route('admin.legal.index'));

        $this->assertNull($this->draftOf());
        $this->assertSame(1, LegalDocumentVersion::query()->published()->where('type', LegalDocumentVersion::TERMS)->count());
    }

    public function test_compare_defaults_to_the_two_latest_versions(): void
    {
        LegalDocumentVersion::factory()->published()->create(['content' => "Shared line\nOld wording"]);
        LegalDocumentVersion::factory()->published()->create(['content' => "Shared line\nNew wording"]);

        $this->actingAs($this->admin)
            ->get(route('admin.legal.compare', LegalDocumentVersion::TERMS))
            ->assertOk()
            ->assertSee('Old wording')
            ->assertSee('New wording')
            ->assertSee('1 lines added, 1 lines removed');
    }

    public function test_compare_with_only_one_version_explains_itself(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.legal.compare', LegalDocumentVersion::PRIVACY))
            ->assertOk()
            ->assertSee('nothing to compare');
    }
}
