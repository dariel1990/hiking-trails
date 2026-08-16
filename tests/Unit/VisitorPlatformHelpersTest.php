<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VisitorPlatformHelpersTest extends TestCase
{
    #[DataProvider('inAppBrowserAgents')]
    public function test_third_party_in_app_browsers_still_see_the_store_badges(string $agent, ?string $platform): void
    {
        $this->assertFalse(visitor_in_native_app($agent));
        $this->assertSame($platform, visitor_mobile_platform($agent));
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function inAppBrowserAgents(): array
    {
        return [
            'instagram android' => [
                'Mozilla/5.0 (Linux; Android 14; SM-S911B Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/131.0.6778.135 Mobile Safari/537.36 Instagram 361.0.0.46.88 Android',
                'android',
            ],
            'instagram ios' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22B83 Instagram 361.0.0.46.88',
                'ios',
            ],
            'facebook ios' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22B83 [FBAN/FBIOS;FBAV/492.0.0.42.108]',
                'ios',
            ],
            'tiktok android' => [
                'Mozilla/5.0 (Linux; Android 13; Pixel 7 Build/TQ3A; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/130.0.0.0 Mobile Safari/537.36 BytedanceWebview/d8a21c6 musical_ly_2023',
                'android',
            ],
        ];
    }

    public function test_our_own_webviews_are_recognised_as_the_native_app(): void
    {
        $this->assertTrue(visitor_in_native_app(
            'Mozilla/5.0 (Linux; Android 14; SM-S911B Build/UP1A; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/131.0.0.0 Mobile Safari/537.36'
        ));

        $this->assertTrue(visitor_in_native_app(
            'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22B83 XploreSmithers/1.4.0'
        ));
    }

    public function test_ordinary_mobile_and_desktop_browsers_are_not_the_native_app(): void
    {
        $this->assertFalse(visitor_in_native_app(
            'Mozilla/5.0 (Linux; Android 14; SM-S911B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36'
        ));

        $this->assertFalse(visitor_in_native_app(
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Safari/605.1.15'
        ));
    }
}
