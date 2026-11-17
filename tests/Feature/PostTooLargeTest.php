<?php

namespace Tests\Feature;

use App\Support\UploadLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTooLargeTest extends TestCase
{
    use RefreshDatabase;

    private function oversizedContentLength(): int
    {
        return UploadLimits::maxPostBytes() + 1024;
    }

    public function test_ajax_upload_over_post_limit_returns_json_413_with_message(): void
    {
        $response = $this->call('POST', route('admin.businesses.photos.upload'), [], [], [], [
            'CONTENT_LENGTH' => $this->oversizedContentLength(),
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response->assertStatus(413)
            ->assertJsonPath('message', UploadLimits::tooLargeMessage())
            ->assertJsonPath('error', UploadLimits::tooLargeMessage());
    }

    public function test_xhr_upload_without_json_accept_header_still_gets_json(): void
    {
        $response = $this->call('POST', route('admin.businesses.photos.upload'), [], [], [], [
            'CONTENT_LENGTH' => $this->oversizedContentLength(),
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(413)->assertJsonStructure(['message', 'error']);
    }

    public function test_full_page_form_post_over_limit_renders_friendly_413_page(): void
    {
        $response = $this->call('POST', route('settings.profile.avatar.update'), [], [], [], [
            'CONTENT_LENGTH' => $this->oversizedContentLength(),
            'HTTP_ACCEPT' => 'text/html',
        ]);

        $response->assertStatus(413)
            ->assertSee('That upload is too heavy to carry')
            ->assertSee(UploadLimits::format(UploadLimits::maxPostBytes()))
            ->assertSee('Go Back');
    }

    public function test_request_within_limit_is_not_rejected_as_too_large(): void
    {
        $response = $this->call('POST', route('admin.businesses.photos.upload'), [], [], [], [
            'CONTENT_LENGTH' => 1024,
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertNotSame(413, $response->getStatusCode());
    }

    public function test_layouts_expose_upload_limits_to_the_frontend(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="upload-max-post-bytes" content="'.UploadLimits::maxPostBytes().'">', false)
            ->assertSee('<meta name="upload-max-file-bytes" content="'.UploadLimits::maxFileBytes().'">', false);
    }

    public function test_parse_ini_size_handles_unit_suffixes(): void
    {
        $this->assertSame(20 * 1024 * 1024, UploadLimits::parseIniSize('20M'));
        $this->assertSame(512 * 1024, UploadLimits::parseIniSize('512k'));
        $this->assertSame(2 * 1024 ** 3, UploadLimits::parseIniSize('2G'));
        $this->assertSame(1000, UploadLimits::parseIniSize('1000'));
        $this->assertSame(0, UploadLimits::parseIniSize(''));
        $this->assertSame('20 MB', UploadLimits::format(20 * 1024 * 1024));
        $this->assertSame('1.5 MB', UploadLimits::format((int) (1.5 * 1024 * 1024)));
    }
}
