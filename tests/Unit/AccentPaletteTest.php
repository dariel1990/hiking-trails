<?php

namespace Tests\Unit;

use App\Support\AccentPalette;
use PHPUnit\Framework\TestCase;

class AccentPaletteTest extends TestCase
{
    public function test_it_derives_darker_lighter_and_border_shades(): void
    {
        $palette = AccentPalette::fromHex('#2C5F5D');

        $this->assertSame('#2c5f5d', $palette['primary']);
        $this->assertSame('#214746', $palette['dark']);
        $this->assertSame('#f0f4f4', $palette['soft']);
        $this->assertSame('#a0b7b6', $palette['border']);
    }

    public function test_dark_colours_get_white_text(): void
    {
        $this->assertSame('#ffffff', AccentPalette::fromHex('#2C5F5D')['onPrimary']);
        $this->assertSame('#ffffff', AccentPalette::fromHex('#B3202A')['onPrimary']);
        $this->assertSame('#ffffff', AccentPalette::fromHex('#000000')['onPrimary']);
    }

    public function test_light_colours_get_dark_text(): void
    {
        $this->assertSame(AccentPalette::DARK_TEXT, AccentPalette::fromHex('#F5D90A')['onPrimary']);
        $this->assertSame(AccentPalette::DARK_TEXT, AccentPalette::fromHex('#E87B35')['onPrimary']);
        $this->assertSame(AccentPalette::DARK_TEXT, AccentPalette::fromHex('#FFFFFF')['onPrimary']);
    }

    public function test_only_six_digit_hex_colours_are_valid(): void
    {
        $this->assertTrue(AccentPalette::isValidHex('#1a2B3c'));
        $this->assertFalse(AccentPalette::isValidHex('1a2b3c'));
        $this->assertFalse(AccentPalette::isValidHex('#abc'));
        $this->assertFalse(AccentPalette::isValidHex('#12345g'));
        $this->assertFalse(AccentPalette::isValidHex('red'));
        $this->assertFalse(AccentPalette::isValidHex(null));
    }
}
