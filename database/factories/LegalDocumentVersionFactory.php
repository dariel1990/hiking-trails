<?php

namespace Database\Factories;

use App\Models\LegalDocumentVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalDocumentVersion>
 */
class LegalDocumentVersionFactory extends Factory
{
    /**
     * Define the model's default state: an unpublished draft of the Terms.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => LegalDocumentVersion::TERMS,
            'version' => null,
            'content' => '## '.fake()->sentence(3)."\n\n".fake()->paragraph(),
            'change_summary' => null,
            'is_material' => false,
            'published_at' => null,
        ];
    }

    public function privacy(): static
    {
        return $this->state(fn (): array => ['type' => LegalDocumentVersion::PRIVACY]);
    }

    /**
     * Publish as the next version number for the document type.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'version' => (LegalDocumentVersion::query()->where('type', $attributes['type'])->max('version') ?? 0) + 1,
            'change_summary' => fake()->sentence(),
            'published_at' => now(),
        ]);
    }

    public function material(): static
    {
        return $this->published()->state(fn (): array => ['is_material' => true]);
    }
}
