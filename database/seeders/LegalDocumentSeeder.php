<?php

namespace Database\Seeders;

use App\Models\LegalDocumentVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LegalDocumentSeeder extends Seeder
{
    /**
     * The date each document's text last really changed, before versioning existed.
     *
     * @var array<string, string>
     */
    private const INITIAL_PUBLISHED_AT = [
        LegalDocumentVersion::TERMS => '2026-06-09 12:00:00',
        LegalDocumentVersion::PRIVACY => '2026-07-11 12:00:00',
    ];

    public function run(): void
    {
        foreach (self::INITIAL_PUBLISHED_AT as $type => $publishedAt) {
            if (LegalDocumentVersion::query()->where('type', $type)->exists()) {
                continue;
            }

            LegalDocumentVersion::query()->create([
                'type' => $type,
                'version' => 1,
                'content' => trim((string) file_get_contents(database_path("seeders/legal/{$type}.md"))),
                'change_summary' => 'Initial version, imported from the original website page.',
                'is_material' => false,
                'published_at' => Carbon::parse($publishedAt),
            ]);
        }
    }
}
