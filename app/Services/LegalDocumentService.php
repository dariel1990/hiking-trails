<?php

namespace App\Services;

use App\Models\LegalAcceptance;
use App\Models\LegalDocumentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegalDocumentService
{
    public const CACHE_KEY = 'legal.published_state';

    private const USER_AGENT_MAX_LENGTH = 500;

    public function current(string $type): ?LegalDocumentVersion
    {
        return $this->publishedState()['current'][$type] ?? null;
    }

    public function render(string $markdown): string
    {
        return (string) Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Lock a draft in as the next version number for its document type.
     */
    public function publish(LegalDocumentVersion $draft, User $publisher, string $changeSummary, bool $isMaterial): LegalDocumentVersion
    {
        $published = DB::transaction(function () use ($draft, $publisher, $changeSummary, $isMaterial): LegalDocumentVersion {
            $lockedDraft = LegalDocumentVersion::query()
                ->draft()
                ->whereKey($draft->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $latestVersion = LegalDocumentVersion::query()
                ->published()
                ->where('type', $lockedDraft->type)
                ->lockForUpdate()
                ->max('version');

            $lockedDraft->update([
                'version' => ((int) $latestVersion) + 1,
                'change_summary' => $changeSummary,
                'is_material' => $isMaterial,
                'published_by' => $publisher->getKey(),
                'published_at' => now(),
            ]);

            return $lockedDraft;
        });

        Cache::forget(self::CACHE_KEY);

        return $published;
    }

    /**
     * The live version of every document, Terms first.
     *
     * @return Collection<int, LegalDocumentVersion>
     */
    public function currentVersions(): Collection
    {
        return collect(array_keys(LegalDocumentVersion::TITLES))
            ->map(fn (string $type): ?LegalDocumentVersion => $this->current($type))
            ->filter()
            ->values();
    }

    /**
     * The current versions a user must accept. A document is pending when its
     * latest material version is newer than what the user has accepted, or when
     * the account was created after acceptance tracking went live (the first
     * material publish) and has never accepted that document, which covers
     * Google and Apple sign-ups. Older accounts are only asked about material changes.
     *
     * @return Collection<int, LegalDocumentVersion>
     */
    public function pendingFor(User $user): Collection
    {
        $state = $this->publishedState();

        if ($state['first_material_at'] === null) {
            return collect();
        }

        $highestAccepted = LegalDocumentVersion::query()
            ->whereHas('acceptances', fn ($query) => $query->where('user_id', $user->getKey()))
            ->selectRaw('type, MAX(version) as highest_version')
            ->groupBy('type')
            ->pluck('highest_version', 'type');

        $isNewAccount = $user->created_at !== null && $user->created_at->gte($state['first_material_at']);

        return $this->currentVersions()
            ->filter(function (LegalDocumentVersion $current) use ($state, $highestAccepted, $isNewAccount): bool {
                $accepted = (int) ($highestAccepted[$current->type] ?? 0);
                $required = $state['material'][$current->type] ?? null;

                return ($required !== null && $accepted < $required->version)
                    || ($accepted === 0 && $isNewAccount);
            })
            ->values();
    }

    /**
     * Users who still have to accept the current version of a document type,
     * using the same rules as pendingFor() but as a single query for admin lists.
     *
     * @return Builder<User>
     */
    public function pendingUsersQuery(string $type): Builder
    {
        $state = $this->publishedState();
        $required = $state['material'][$type] ?? null;
        $firstMaterialAt = $state['first_material_at'];

        if ($firstMaterialAt === null || $this->current($type) === null) {
            return User::query()->whereRaw('0 = 1');
        }

        $acceptedAtLeast = fn (int $minimumVersion): \Closure => fn (Builder $query): Builder => $query
            ->where('type', $type)
            ->where('version', '>=', $minimumVersion);

        return User::query()->where(function (Builder $query) use ($required, $firstMaterialAt, $acceptedAtLeast): void {
            if ($required !== null) {
                $query->whereDoesntHave('legalAcceptances.version', $acceptedAtLeast($required->version));
            }

            $query->orWhere(fn (Builder $newAccounts): Builder => $newAccounts
                ->where('created_at', '>=', $firstMaterialAt)
                ->whereDoesntHave('legalAcceptances.version', $acceptedAtLeast(1)));
        });
    }

    /**
     * The full text and details needed to show documents for review.
     *
     * @param  Collection<int, LegalDocumentVersion>  $versions
     * @return list<array{id: int, type: string, title: string, version: int, is_material: bool, change_summary: ?string, published_at: string, published_on: string, url: string, html: string}>
     */
    public function reviewPayload(Collection $versions): array
    {
        return $versions->map(fn (LegalDocumentVersion $version): array => [
            'id' => $version->id,
            'type' => $version->type,
            'title' => $version->title(),
            'version' => $version->version,
            'is_material' => $version->is_material,
            'change_summary' => $version->change_summary,
            'published_at' => $version->published_at->toIso8601String(),
            'published_on' => $version->published_at->format('F j, Y'),
            'url' => $version->publicUrl(),
            'html' => $this->renderDocument($version),
        ])->values()->all();
    }

    /**
     * The document as readers see it, including the Privacy Policy's
     * contact block, which comes from site settings rather than the text.
     */
    public function renderDocument(LegalDocumentVersion $version): string
    {
        $html = $this->render($version->content);

        return $version->type === LegalDocumentVersion::PRIVACY
            ? $html.view('legal._privacy_contact')->render()
            : $html;
    }

    /**
     * @param  Collection<int, LegalDocumentVersion>  $versions
     */
    public function recordAcceptance(User $user, Collection $versions, Request $request, string $source): void
    {
        foreach ($versions as $version) {
            LegalAcceptance::query()->firstOrCreate(
                ['user_id' => $user->getKey(), 'legal_document_version_id' => $version->getKey()],
                [
                    'accepted_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, self::USER_AGENT_MAX_LENGTH) ?: null,
                    'source' => $source,
                ],
            );
        }
    }

    /**
     * Whether submitted version ids are exactly the versions expected.
     *
     * @param  array<int, int|string>  $submittedIds
     * @param  Collection<int, LegalDocumentVersion>  $versions
     */
    public function matchesVersions(array $submittedIds, Collection $versions): bool
    {
        $submitted = collect($submittedIds)->map(fn (int|string $id): int => (int) $id)->sort()->values();

        return $submitted->all() === $versions->map->getKey()->sort()->values()->all();
    }

    /**
     * @return array{current: array<string, LegalDocumentVersion>, material: array<string, LegalDocumentVersion>, first_material_at: ?Carbon}
     */
    private function publishedState(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $versions = LegalDocumentVersion::query()->published()->orderByDesc('version')->get();

            return [
                'current' => $versions->unique('type')->keyBy('type')->all(),
                'material' => $versions->where('is_material', true)->unique('type')->keyBy('type')->all(),
                'first_material_at' => $versions->where('is_material', true)->sortBy('published_at')->first()?->published_at,
            ];
        });
    }
}
