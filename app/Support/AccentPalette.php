<?php

namespace App\Support;

/**
 * Shades of one accent colour for buttons, icons and map markers: a darker
 * hover, a pale tint, a soft border, and the text colour that reads best on it.
 */
class AccentPalette
{
    public const DARK_TEXT = '#111827';

    /**
     * @return array{primary: string, dark: string, soft: string, border: string, onPrimary: string}
     */
    public static function fromHex(string $hex): array
    {
        $rgb = self::toRgb($hex);

        return [
            'primary' => self::toHex($rgb),
            'dark' => self::toHex(self::mix($rgb, [0, 0, 0], 0.25)),
            'soft' => self::toHex(self::mix($rgb, [255, 255, 255], 0.93)),
            'border' => self::toHex(self::mix($rgb, [255, 255, 255], 0.55)),
            'onPrimary' => self::contrast($rgb, [255, 255, 255]) >= self::contrast($rgb, self::toRgb(self::DARK_TEXT))
                ? '#ffffff'
                : self::DARK_TEXT,
        ];
    }

    public static function isValidHex(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function toRgb(string $hex): array
    {
        return array_map('hexdec', str_split(ltrim($hex, '#'), 2));
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function toHex(array $rgb): string
    {
        return sprintf('#%02x%02x%02x', ...array_map(fn ($channel) => (int) round($channel), $rgb));
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $from
     * @param  array{0: int, 1: int, 2: int}  $to
     * @return array{0: float, 1: float, 2: float}
     */
    private static function mix(array $from, array $to, float $amount): array
    {
        return array_map(fn ($a, $b) => $a + ($b - $a) * $amount, $from, $to);
    }

    /**
     * WCAG contrast ratio between two colours.
     *
     * @param  array{0: int, 1: int, 2: int}  $a
     * @param  array{0: int, 1: int, 2: int}  $b
     */
    private static function contrast(array $a, array $b): float
    {
        [$lighter, $darker] = [max(self::luminance($a), self::luminance($b)), min(self::luminance($a), self::luminance($b))];

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function luminance(array $rgb): float
    {
        [$r, $g, $b] = array_map(function ($channel) {
            $channel /= 255;

            return $channel <= 0.03928 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
