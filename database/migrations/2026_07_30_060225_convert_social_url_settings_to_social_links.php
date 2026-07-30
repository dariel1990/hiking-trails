<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The four fixed social URL settings replaced by the 'social_links' list.
     *
     * @var array<string, array{icon: string, label: string}>
     */
    protected array $legacyKeys = [
        'social_youtube_url' => ['icon' => 'youtube', 'label' => 'YouTube'],
        'social_instagram_url' => ['icon' => 'instagram', 'label' => 'Instagram'],
        'social_tiktok_url' => ['icon' => 'tiktok', 'label' => 'TikTok'],
        'social_facebook_url' => ['icon' => 'facebook', 'label' => 'Facebook'],
    ];

    public function up(): void
    {
        $existing = Setting::query()
            ->whereIn('key', array_keys($this->legacyKeys))
            ->pluck('value', 'key');

        if ($existing->isEmpty()) {
            return;
        }

        $links = [];

        foreach ($this->legacyKeys as $key => $meta) {
            $url = $existing->get($key);

            if (is_string($url) && $url !== '') {
                $links[] = ['icon' => $meta['icon'], 'label' => $meta['label'], 'url' => $url];
            }
        }

        Setting::set('social_links', $links);

        Setting::query()->whereIn('key', array_keys($this->legacyKeys))->delete();
    }

    public function down(): void
    {
        $links = collect(Setting::get('social_links') ?? [])
            ->filter(fn ($link): bool => is_array($link) && isset($link['icon']))
            ->keyBy('icon');

        foreach ($this->legacyKeys as $key => $meta) {
            Setting::query()->updateOrCreate(['key' => $key], [
                'value' => $links->get($meta['icon'])['url'] ?? null,
                'type' => 'string',
                'group' => 'contact',
            ]);
        }

        Setting::query()->where('key', 'social_links')->delete();
    }
};
