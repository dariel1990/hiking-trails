<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReviewChannelTest extends TestCase
{
    #[DataProvider('userAgents')]
    public function test_review_channel_resolves_from_the_user_agent(string $agent, string $expected): void
    {
        $this->assertSame($expected, review_channel($agent));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function userAgents(): array
    {
        return [
            'our ios app' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22B83 XploreSmithers/1.4.0',
                'ios',
            ],
            'our android app' => [
                'Mozilla/5.0 (Linux; Android 14; SM-S911B Build/UP1A; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/131.0.0.0 Mobile Safari/537.36 XploreSmithers/1.4.0',
                'android',
            ],
            'android webview without our token' => [
                'Mozilla/5.0 (Linux; Android 14; SM-S911B Build/UP1A; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/131.0.0.0 Mobile Safari/537.36',
                'android',
            ],
            'mobile safari' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Mobile/15E148 Safari/604.1',
                'web',
            ],
            'chrome on android' => [
                'Mozilla/5.0 (Linux; Android 14; SM-S911B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Mobile Safari/537.36',
                'web',
            ],
            'desktop safari' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.1 Safari/605.1.15',
                'web',
            ],
            // Social in-app browsers are WebViews but their users have not installed
            // the app, so they must be sent to Google Reviews rather than a store.
            'instagram ios' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22B83 Instagram 361.0.0.46.88',
                'web',
            ],
            'facebook ios' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 18_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/22B83 [FBAN/FBIOS;FBAV/492.0.0.42.108]',
                'web',
            ],
            'tiktok android' => [
                'Mozilla/5.0 (Linux; Android 13; Pixel 7 Build/TQ3A; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/130.0.0.0 Mobile Safari/537.36 BytedanceWebview/d8a21c6 musical_ly_2023',
                'web',
            ],
        ];
    }

    public function test_ios_url_uses_the_write_review_action_with_the_configured_app_id(): void
    {
        config(['services.ios_app.app_id' => '6791770404']);

        $this->assertSame(
            'https://apps.apple.com/app/id6791770404?action=write-review',
            review_url('ios')
        );
    }

    public function test_ios_url_parses_the_app_id_out_of_the_listing_url(): void
    {
        config([
            'services.ios_app.app_id' => null,
            'services.ios_app.app_store_url' => 'https://apps.apple.com/app/id6791770404',
        ]);

        $this->assertSame(
            'https://apps.apple.com/app/id6791770404?action=write-review',
            review_url('ios')
        );
    }

    public function test_ios_url_falls_back_to_the_listing_url_when_no_id_can_be_found(): void
    {
        config([
            'services.ios_app.app_id' => null,
            'services.ios_app.app_store_url' => 'https://apps.apple.com/ca/app/xploresmithers',
        ]);

        $this->assertSame('https://apps.apple.com/ca/app/xploresmithers', review_url('ios'));
    }

    public function test_ios_url_is_null_when_the_app_store_is_not_configured(): void
    {
        config([
            'services.ios_app.app_id' => null,
            'services.ios_app.app_store_url' => null,
        ]);

        $this->assertNull(review_url('ios'));
    }

    public function test_android_url_targets_the_play_listing_for_the_package(): void
    {
        config(['services.android_app.package_name' => 'com.xploresmithers.app']);

        $this->assertSame(
            'https://play.google.com/store/apps/details?id=com.xploresmithers.app',
            review_url('android')
        );
    }

    public function test_android_url_is_null_when_neither_package_nor_url_is_configured(): void
    {
        config([
            'services.android_app.package_name' => null,
            'services.android_app.play_store_url' => null,
        ]);

        $this->assertNull(review_url('android'));
    }

    /**
     * The configured-link cases need the settings table, so they live in
     * tests/Feature/ReviewPromptTest.php.
     */
    public function test_web_url_is_null_without_a_google_review_link(): void
    {
        $this->assertNull(review_url('web'));
    }
}
