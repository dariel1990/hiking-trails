<?php

namespace App\Services;

class ThemeService
{
    /**
     * Canonical compiled ramps — must stay byte-identical to the fallback
     * values in tailwind.config.js. Shade derivation preserves the shape of
     * these hand-tuned ramps around any admin-picked base color.
     */
    public const RAMPS = [
        'forest' => [
            50 => '#f5f8f7', 100 => '#e8f0ed', 200 => '#d1e1db', 300 => '#a8c4b9',
            400 => '#7da599', 500 => '#5a8579', 600 => '#2C5F5D', 700 => '#234e4c',
            800 => '#1d403e', 900 => '#193634', 950 => '#1a2e2e',
        ],
        'accent' => [
            50 => '#fff7ed', 100 => '#ffedd5', 200 => '#fed7aa', 300 => '#fdba74',
            400 => '#fb923c', 500 => '#E87B35', 600 => '#ea580c', 700 => '#c2410c',
            800 => '#9a3412', 900 => '#7c2d12',
        ],
        'emerald' => [
            50 => '#ecfdf5', 100 => '#d1fae5', 200 => '#a7f3d0', 300 => '#6ee7b7',
            400 => '#4A9B8E', 500 => '#10b981', 600 => '#059669', 700 => '#047857',
            800 => '#065f46', 900 => '#064e3b',
        ],
    ];

    public const BASE_SHADES = ['forest' => 600, 'accent' => 500, 'emerald' => 500];

    public const COLOR_SETTINGS = [
        'theme_brand_color' => 'forest',
        'theme_accent_color' => 'accent',
        'theme_action_color' => 'emerald',
    ];

    public const FONT_SETTINGS = [
        'theme_heading_font' => '--font-heading',
        'theme_body_font' => '--font-body',
        'theme_subheading_font' => '--font-subheading',
    ];

    /**
     * CSS custom properties to emit, only for values changed from defaults.
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        $variables = [];

        foreach (self::COLOR_SETTINGS as $key => $family) {
            $picked = $this->normalizeHex((string) setting($key));

            if ($picked === null || $picked === $this->defaultFor($key)) {
                continue;
            }

            foreach ($this->deriveRamp($family, $picked) as $shade => $hex) {
                $variables["--c-{$family}-{$shade}"] = $this->hexToRgbTriplet($hex);
            }
        }

        foreach (self::FONT_SETTINGS as $key => $variable) {
            $family = (string) setting($key);

            if ($family === $this->fontDefault($key) || ! isset(config('theme-fonts')[$family])) {
                continue;
            }

            $variables[$variable] = config('theme-fonts')[$family]['stack'];
        }

        return $variables;
    }

    /**
     * Bunny Fonts stylesheet URL for the non-default selected families.
     */
    public function fontLink(): ?string
    {
        $families = [];

        foreach (array_keys(self::FONT_SETTINGS) as $key) {
            $family = (string) setting($key);

            if ($family === $this->fontDefault($key)) {
                continue;
            }

            $font = config('theme-fonts')[$family] ?? null;

            if ($font !== null) {
                $families[$family] = "{$font['bunny']}:{$font['weights']}";
            }
        }

        if ($families === []) {
            return null;
        }

        return 'https://fonts.bunny.net/css?family='.implode('|', $families).'&display=swap';
    }

    public function isDefault(): bool
    {
        return $this->cssVariables() === [];
    }

    /**
     * Resolved hex for one shade — derived when the family's setting was
     * changed, canonical otherwise. For JS contexts (Mapbox paint, canvas)
     * where CSS var() references cannot be used.
     */
    public function colorHex(string $family, int $shade): string
    {
        $settingKey = array_search($family, self::COLOR_SETTINGS, true);
        $picked = $settingKey !== false ? $this->normalizeHex((string) setting($settingKey)) : null;

        if ($settingKey === false || $picked === null || $picked === $this->defaultFor($settingKey)) {
            return strtolower(self::RAMPS[$family][$shade]);
        }

        return $this->deriveRamp($family, $picked)[$shade];
    }

    /**
     * Resolved shade as a comma-separated RGB triplet ("44, 95, 93") for
     * legacy rgba(...) syntax in JS canvas code.
     */
    public function colorRgb(string $family, int $shade): string
    {
        return str_replace(' ', ', ', $this->hexToRgbTriplet($this->colorHex($family, $shade)));
    }

    /**
     * Derive a full shade ramp from a picked base color by re-applying each
     * canonical shade's HSL offsets (relative to the family's base shade)
     * onto the new base. Feeding the canonical base back in reproduces the
     * canonical ramp exactly.
     *
     * @return array<int, string> shade => hex
     */
    public function deriveRamp(string $family, string $baseHex): array
    {
        $ramp = self::RAMPS[$family];
        $baseShade = self::BASE_SHADES[$family];

        if ($this->normalizeHex($baseHex) === strtolower($ramp[$baseShade])) {
            return array_map('strtolower', $ramp);
        }

        $canonicalBase = $this->hexToHsl($ramp[$baseShade]);
        $newBase = $this->hexToHsl($this->normalizeHex($baseHex) ?? $ramp[$baseShade]);

        $derived = [];

        foreach ($ramp as $shade => $hex) {
            $canonical = $this->hexToHsl($hex);

            $h = fmod($newBase['h'] + ($canonical['h'] - $canonicalBase['h']) + 360.0, 360.0);
            $s = max(0.0, min(100.0, $newBase['s'] + ($canonical['s'] - $canonicalBase['s'])));
            $l = max(0.0, min(100.0, $newBase['l'] + ($canonical['l'] - $canonicalBase['l'])));

            $derived[$shade] = $this->hslToHex($h, $s, $l);
        }

        return $derived;
    }

    public function hexToRgbTriplet(string $hex): string
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');

        return "{$r} {$g} {$b}";
    }

    public function normalizeHex(string $hex): ?string
    {
        $hex = strtolower(trim($hex));

        return preg_match('/^#[0-9a-f]{6}$/', $hex) === 1 ? $hex : null;
    }

    protected function defaultFor(string $key): string
    {
        return strtolower((string) config("settings.definitions.{$key}.default"));
    }

    protected function fontDefault(string $key): string
    {
        return (string) config("settings.definitions.{$key}.default");
    }

    /**
     * @return array{h: float, s: float, l: float}
     */
    protected function hexToHsl(string $hex): array
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        $l = ($max + $min) / 2;

        if ($delta == 0.0) {
            return ['h' => 0.0, 's' => 0.0, 'l' => $l * 100];
        }

        $s = $delta / (1 - abs(2 * $l - 1));

        $h = match ($max) {
            $r => fmod(($g - $b) / $delta, 6),
            $g => (($b - $r) / $delta) + 2,
            default => (($r - $g) / $delta) + 4,
        };

        return [
            'h' => fmod($h * 60 + 360, 360),
            's' => min(1.0, $s) * 100,
            'l' => $l * 100,
        ];
    }

    protected function hslToHex(float $h, float $s, float $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return sprintf(
            '#%02x%02x%02x',
            (int) round(($r + $m) * 255),
            (int) round(($g + $m) * 255),
            (int) round(($b + $m) * 255),
        );
    }
}
