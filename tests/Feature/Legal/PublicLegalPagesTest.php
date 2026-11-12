<?php

namespace Tests\Feature\Legal;

use App\Models\LegalDocumentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_terms_page_shows_the_imported_version_with_its_real_date(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Assumption of Risk')
            ->assertSee('June 9, 2026')
            ->assertSee('Version 1');
    }

    public function test_privacy_page_shows_its_real_date_instead_of_today(): void
    {
        $this->travelTo(now()->setDate(2030, 1, 15));

        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('July 11, 2026')
            ->assertDontSee('January 15, 2030');
    }

    public function test_privacy_page_keeps_the_contact_details_from_settings(): void
    {
        $this->get(route('privacy-policy'))->assertOk()->assertSee(setting('contact_address'));
    }

    public function test_the_latest_published_version_is_shown(): void
    {
        LegalDocumentVersion::factory()->published()->create(['content' => 'Brand new published terms.']);

        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Brand new published terms.')
            ->assertSee('Version 2')
            ->assertDontSee('Assumption of Risk');
    }

    public function test_publishing_one_document_does_not_change_the_other(): void
    {
        LegalDocumentVersion::factory()->published()->create(['content' => 'Only the terms changed.']);

        $this->get(route('privacy-policy'))->assertOk()->assertDontSee('Only the terms changed.');
    }

    public function test_drafts_are_never_visible(): void
    {
        LegalDocumentVersion::factory()->create(['content' => 'Secret draft wording.']);

        $this->get(route('terms'))->assertOk()->assertDontSee('Secret draft wording.');
    }

    public function test_raw_html_in_a_document_is_stripped(): void
    {
        LegalDocumentVersion::factory()->published()->create(['content' => "Safe text.\n\n<script>alert('legal')</script>"]);

        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Safe text.')
            ->assertDontSee("<script>alert('legal')</script>", false);
    }

    public function test_unsafe_links_are_neutralised(): void
    {
        LegalDocumentVersion::factory()->published()->create(['content' => '[click](javascript:alert(1))']);

        $this->get(route('terms'))->assertOk()->assertDontSee('href="javascript:', false);
    }
}
