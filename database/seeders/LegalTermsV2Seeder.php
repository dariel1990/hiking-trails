<?php

namespace Database\Seeders;

use App\Models\LegalDocumentVersion;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * One-time publish of the rewritten Terms. Run explicitly on each environment;
 * it is deliberately not part of DatabaseSeeder.
 */
class LegalTermsV2Seeder extends Seeder
{
    private const VERSION = 2;

    private const EFFECTIVE_DATE_TOKEN = ':effective_date';

    /**
     * @var array<int, string>
     */
    private const UNFINISHED_MARKERS = ['[Insert', 'Proposed'];

    private const CHANGE_SUMMARY = 'Full rewrite of the Terms. Expands the outdoor safety warnings and assumption of risk, adds sections on trail access, offline maps and emergencies, updates the limitation of liability, including claims of ordinary negligence, and revises the subscription, privacy and governing law sections.';

    public function run(): void
    {
        $latestVersion = LegalDocumentVersion::query()
            ->published()
            ->where('type', LegalDocumentVersion::TERMS)
            ->max('version');

        if ($latestVersion === null) {
            throw new RuntimeException('No published Terms found. Run "php artisan migrate" first.');
        }

        if ((int) $latestVersion >= self::VERSION) {
            $this->command?->warn("Terms are already at version {$latestVersion}. Nothing was changed.");

            return;
        }

        $content = str_replace(self::EFFECTIVE_DATE_TOKEN, now()->format('F j, Y'), $this->markdown());

        $this->ensureWordingIsFinal($content);

        LegalDocumentVersion::query()->create([
            'type' => LegalDocumentVersion::TERMS,
            'version' => self::VERSION,
            'content' => $content,
            'change_summary' => self::CHANGE_SUMMARY,
            'is_material' => true,
            'published_at' => now(),
        ]);

        $this->command?->info('Terms version '.self::VERSION.' is now live. Logged-in website users will be asked to accept it.');
    }

    protected function markdown(): string
    {
        return trim((string) file_get_contents(database_path('seeders/legal/terms-v2.md')));
    }

    private function ensureWordingIsFinal(string $content): void
    {
        $found = array_filter(self::UNFINISHED_MARKERS, fn (string $marker): bool => str_contains($content, $marker));

        if ($found !== []) {
            throw new RuntimeException(
                'Not published: database/seeders/legal/terms-v2.md still contains unfinished wording: '.implode(', ', $found)
            );
        }
    }
}
