<?php

namespace Tests\Feature\Legal;

use App\Models\LegalDocumentVersion;
use App\Models\User;
use App\Services\LegalDocumentService;
use Database\Seeders\LegalTermsV2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class LegalTermsV2SeederTest extends TestCase
{
    use RefreshDatabase;

    private function seederWith(string $markdown): LegalTermsV2Seeder
    {
        return new class($markdown) extends LegalTermsV2Seeder
        {
            public function __construct(private string $text) {}

            protected function markdown(): string
            {
                return $this->text;
            }
        };
    }

    private function termsCount(): int
    {
        return LegalDocumentVersion::query()->where('type', LegalDocumentVersion::TERMS)->count();
    }

    public function test_it_publishes_version_two_as_a_material_change(): void
    {
        $this->seederWith('Final wording.')->run();

        $current = app(LegalDocumentService::class)->current(LegalDocumentVersion::TERMS);

        $this->assertSame(2, $current->version);
        $this->assertSame('Final wording.', $current->content);
        $this->assertTrue($current->is_material);
        $this->assertNotEmpty($current->change_summary);
    }

    public function test_the_effective_date_is_the_day_it_is_published(): void
    {
        $this->travelTo(now()->setDate(2026, 12, 3));

        $this->seederWith('Effective Date: :effective_date')->run();

        $this->assertSame(
            'Effective Date: December 3, 2026',
            app(LegalDocumentService::class)->current(LegalDocumentVersion::TERMS)->content,
        );
    }

    public function test_publishing_asks_existing_users_to_accept(): void
    {
        $this->seederWith('Final wording.')->run();

        $pending = app(LegalDocumentService::class)->pendingFor(User::factory()->create(['created_at' => now()->subYear()]));

        $this->assertSame([2], $pending->pluck('version')->all());
    }

    public function test_running_it_twice_changes_nothing(): void
    {
        $this->seederWith('Final wording.')->run();
        $this->seederWith('Different wording.')->run();

        $this->assertSame(2, $this->termsCount());
        $this->assertSame('Final wording.', app(LegalDocumentService::class)->current(LegalDocumentVersion::TERMS)->content);
    }

    public function test_it_never_replaces_a_newer_version(): void
    {
        LegalDocumentVersion::factory()->published()->create(['content' => 'Published from admin.']);
        LegalDocumentVersion::factory()->published()->create(['content' => 'Even newer.']);

        $this->seederWith('Final wording.')->run();

        $this->assertSame(3, $this->termsCount());
        $this->assertSame('Even newer.', app(LegalDocumentService::class)->current(LegalDocumentVersion::TERMS)->content);
    }

    public function test_it_refuses_to_publish_with_a_placeholder(): void
    {
        try {
            $this->seederWith('Email: [Insert official legal/contact email]')->run();
            $this->fail('The seeder should have refused to publish.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('[Insert', $exception->getMessage());
        }

        $this->assertSame(1, $this->termsCount());
    }

    public function test_it_refuses_to_publish_a_proposed_revision(): void
    {
        $this->expectException(RuntimeException::class);

        $this->seederWith('Version: 2.0 — Proposed Revision')->run();
    }

    public function test_it_allows_the_ordinary_word_propose(): void
    {
        $this->seederWith('The Company may propose updates.')->run();

        $this->assertSame(2, $this->termsCount());
    }

    public function test_it_fails_clearly_when_no_terms_exist(): void
    {
        LegalDocumentVersion::query()->where('type', LegalDocumentVersion::TERMS)->delete();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('migrate');

        $this->seederWith('Final wording.')->run();
    }

    public function test_it_leaves_an_admin_draft_alone(): void
    {
        $draft = LegalDocumentVersion::factory()->create(['content' => 'Work in progress.']);

        $this->seederWith('Final wording.')->run();

        $this->assertNull($draft->refresh()->published_at);
        $this->assertSame('Work in progress.', $draft->content);
    }

    public function test_the_document_renders_with_the_expected_structure(): void
    {
        $html = app(LegalDocumentService::class)->render(
            (string) file_get_contents(database_path('seeders/legal/terms-v2.md'))
        );

        $this->assertSame(20, substr_count($html, '<h2>'));
        $this->assertSame(1, substr_count($html, '<ol>'));
        $this->assertSame(1, substr_count($html, '<hr'));
        $this->assertStringContainsString('<h3>Electronic Acceptance</h3>', $html);
        $this->assertStringContainsString('<br', $html);
    }
}
