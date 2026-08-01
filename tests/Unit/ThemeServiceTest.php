<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ThemeService $theme;

    protected function setUp(): void
    {
        parent::setUp();

        $this->theme = app(ThemeService::class);
    }

    public function test_deriving_with_the_canonical_base_reproduces_the_canonical_ramp(): void
    {
        foreach (ThemeService::COLOR_SETTINGS as $family) {
            $base = ThemeService::RAMPS[$family][ThemeService::BASE_SHADES[$family]];

            $this->assertSame(
                array_map('strtolower', ThemeService::RAMPS[$family]),
                $this->theme->deriveRamp($family, $base),
                "Identity failed for family {$family}",
            );
        }
    }

    public function test_derived_ramp_keeps_descending_lightness_and_valid_hex(): void
    {
        $ramp = $this->theme->deriveRamp('forest', '#5f2c5f');

        $previous = PHP_INT_MAX;
        foreach ($ramp as $shade => $hex) {
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex, "Shade {$shade} invalid");

            [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');
            $luma = $r + $g + $b;
            $this->assertLessThanOrEqual($previous, $luma, "Shade {$shade} not darker than previous");
            $previous = $luma;
        }
    }

    public function test_extreme_bases_do_not_crash_and_stay_valid(): void
    {
        foreach (['#000000', '#ffffff', '#ff0000'] as $base) {
            foreach ($this->theme->deriveRamp('accent', $base) as $hex) {
                $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex);
            }
        }
    }

    public function test_css_variables_are_empty_at_defaults(): void
    {
        $this->assertSame([], $this->theme->cssVariables());
        $this->assertTrue($this->theme->isDefault());
        $this->assertNull($this->theme->fontLink());
    }

    public function test_changing_a_color_emits_only_that_family(): void
    {
        Setting::set('theme_accent_color', '#3366cc');

        $variables = $this->theme->cssVariables();

        $this->assertArrayHasKey('--c-accent-500', $variables);
        $this->assertSame('51 102 204', $variables['--c-accent-500']);
        $this->assertArrayNotHasKey('--c-forest-600', $variables);
        $this->assertFalse($this->theme->isDefault());
    }

    public function test_changing_brand_also_emits_the_footer_dark_shade(): void
    {
        Setting::set('theme_brand_color', '#5f2c5f');

        $this->assertArrayHasKey('--c-forest-950', $this->theme->cssVariables());
    }

    public function test_changing_fonts_emits_stack_variable_and_bunny_link(): void
    {
        Setting::set('theme_body_font', 'Nunito');
        Setting::set('theme_heading_font', 'Lora');

        $variables = $this->theme->cssVariables();

        $this->assertStringContainsString('Nunito', $variables['--font-body']);
        $this->assertStringContainsString('Lora', $variables['--font-heading']);

        $link = $this->theme->fontLink();
        $this->assertStringContainsString('fonts.bunny.net/css?family=', $link);
        $this->assertStringContainsString('lora:', $link);
        $this->assertStringContainsString('nunito:', $link);
        $this->assertStringContainsString('|', $link);
    }

    public function test_case_differences_from_default_emit_nothing(): void
    {
        Setting::set('theme_brand_color', '#2c5f5d');

        $this->assertSame([], $this->theme->cssVariables());
    }
}
