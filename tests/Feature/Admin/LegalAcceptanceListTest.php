<?php

namespace Tests\Feature\Admin;

use App\Models\LegalAcceptance;
use App\Models\LegalDocumentVersion;
use App\Models\User;
use App\Services\LegalDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalAcceptanceListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->create(['is_admin' => true, 'created_at' => now()->subYears(2)]);
    }

    private function accept(User $user, LegalDocumentVersion $version, string $source = LegalAcceptance::SOURCE_WEB): void
    {
        LegalAcceptance::query()->create([
            'user_id' => $user->id,
            'legal_document_version_id' => $version->id,
            'accepted_at' => now(),
            'ip_address' => '203.0.113.7',
            'source' => $source,
        ]);
    }

    private function versionUrl(LegalDocumentVersion $version, array $query = []): string
    {
        return route('admin.legal.version', [$version->type, $version->version, ...$query]);
    }

    public function test_the_accepted_tab_lists_who_accepted_and_where(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $webUser = User::factory()->create(['name' => 'Wendy Web', 'email' => 'wendy@example.com']);
        $appUser = User::factory()->create(['name' => 'Andy App']);
        $this->accept($webUser, $version);
        $this->accept($appUser, $version, LegalAcceptance::SOURCE_API);

        $this->actingAs($this->admin)
            ->get($this->versionUrl($version))
            ->assertOk()
            ->assertSee('Wendy Web')
            ->assertSee('wendy@example.com')
            ->assertSee('Andy App')
            ->assertSee('Website')
            ->assertSee('App')
            ->assertSee('203.0.113.7');
    }

    public function test_the_pending_tab_lists_users_who_have_not_accepted(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $accepted = User::factory()->create(['name' => 'Already Agreed', 'created_at' => now()->subYear()]);
        User::factory()->create(['name' => 'Still Waiting', 'created_at' => now()->subYear()]);
        $this->accept($accepted, $version);

        $this->actingAs($this->admin)
            ->get($this->versionUrl($version, ['tab' => 'pending']))
            ->assertOk()
            ->assertSee('Still Waiting')
            ->assertDontSee('Already Agreed');
    }

    public function test_search_filters_both_lists(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $this->accept(User::factory()->create(['name' => 'Alice Accepted']), $version);
        $this->accept(User::factory()->create(['name' => 'Bob Accepted']), $version);
        User::factory()->create(['name' => 'Carol Pending', 'created_at' => now()->subYear()]);
        User::factory()->create(['name' => 'Dave Pending', 'email' => 'dave@example.com', 'created_at' => now()->subYear()]);

        $this->actingAs($this->admin);

        $this->get($this->versionUrl($version, ['search' => 'alice']))
            ->assertSee('Alice Accepted')
            ->assertDontSee('Bob Accepted');

        $this->get($this->versionUrl($version, ['tab' => 'pending', 'search' => 'dave@']))
            ->assertSee('Dave Pending')
            ->assertDontSee('Carol Pending');
    }

    public function test_older_versions_have_no_pending_tab(): void
    {
        $old = LegalDocumentVersion::factory()->material()->create();
        LegalDocumentVersion::factory()->material()->create();
        User::factory()->create(['name' => 'Still Waiting', 'created_at' => now()->subYear()]);

        $this->actingAs($this->admin)
            ->get($this->versionUrl($old, ['tab' => 'pending']))
            ->assertOk()
            ->assertDontSee('Still pending')
            ->assertDontSee('Still Waiting');
    }

    public function test_nobody_is_pending_before_any_material_change(): void
    {
        $version = LegalDocumentVersion::factory()->published()->create();
        User::factory()->create(['name' => 'Long Time User', 'created_at' => now()->subYear()]);

        $this->actingAs($this->admin)
            ->get($this->versionUrl($version, ['tab' => 'pending']))
            ->assertOk()
            ->assertDontSee('Long Time User')
            ->assertSee('Everyone is up to date.');
    }

    public function test_the_document_tab_shows_the_text(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create(['content' => 'Full wording of the terms.']);

        $this->actingAs($this->admin)
            ->get($this->versionUrl($version, ['tab' => 'document']))
            ->assertOk()
            ->assertSee('Full wording of the terms.');
    }

    public function test_non_admins_cannot_see_the_lists(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();

        $response = $this->actingAs(User::factory()->create())->get($this->versionUrl($version));

        $this->assertNotSame(200, $response->getStatusCode());
    }

    public function test_the_pending_query_matches_the_per_user_rules(): void
    {
        $terms = LegalDocumentVersion::factory()->material()->create();
        $privacy = LegalDocumentVersion::factory()->privacy()->material()->create();
        LegalDocumentVersion::factory()->published()->create();

        $users = collect([
            'old, nothing accepted' => User::factory()->create(['created_at' => now()->subYear()]),
            'old, accepted terms' => User::factory()->create(['created_at' => now()->subYear()]),
            'old, accepted both' => User::factory()->create(['created_at' => now()->subYear()]),
            'new, nothing accepted' => User::factory()->create(['created_at' => now()->addMinute()]),
            'new, accepted privacy' => User::factory()->create(['created_at' => now()->addMinute()]),
        ]);
        $this->accept($users['old, accepted terms'], $terms);
        $this->accept($users['old, accepted both'], $terms);
        $this->accept($users['old, accepted both'], $privacy);
        $this->accept($users['new, accepted privacy'], $privacy);

        $service = app(LegalDocumentService::class);

        foreach ([LegalDocumentVersion::TERMS, LegalDocumentVersion::PRIVACY] as $type) {
            $expected = $users->filter(fn (User $user): bool => $service->pendingFor($user)->contains('type', $type))
                ->pluck('id')->sort()->values()->all();
            $actual = $service->pendingUsersQuery($type)->whereIn('id', $users->pluck('id'))
                ->pluck('id')->sort()->values()->all();

            $this->assertSame($expected, $actual, "Pending users differ for {$type}");
        }
    }
}
