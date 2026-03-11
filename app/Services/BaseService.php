<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

abstract class BaseService
{
    /**
     * Check if the app is running inside NativePHP (mobile).
     */
    public function isMobile(): bool
    {
        return (bool) config('nativephp-internal.running');
    }

    /**
     * Get the production site URL for a given path.
     */
    public function getProductionUrl(string $path = ''): string
    {
        return rtrim(config('nativephp.site_url', 'https://trails.xploresmithers.com'), '/').'/'.ltrim($path, '/');
    }

    /**
     * Fetch a rendered page from the production server and return it
     * as an HTTP response. Used for Eloquent-heavy pages on mobile.
     */
    public function fetchProductionPage(string $path = ''): \Illuminate\Http\Response
    {
        $url = $this->getProductionUrl($path);
        $siteUrl = rtrim(config('nativephp.site_url', 'https://trails.xploresmithers.com'), '/');

        $response = Http::withOptions([
            'allow_redirects' => ['max' => 5, 'track_redirects' => true],
        ])
            ->connectTimeout(30)
            ->timeout(60)
            ->withHeaders(['User-Agent' => 'NativePHP-Mobile/1.0'])
            ->get($url);

        if ($response->failed()) {
            return response('<html><body><p>Unable to load page.</p></body></html>', 200)
                ->header('Content-Type', 'text/html');
        }

        $html = $response->body();

        // Convert absolute production URLs to relative paths so links
        // stay inside the NativePHP WebView instead of opening in the browser.
        // Keep /storage/ and /build/ URLs absolute so images and assets load from production.
        $html = str_replace($siteUrl.'/storage/', '___STORAGE_PLACEHOLDER___', $html);
        $html = str_replace($siteUrl.'/build/', '___BUILD_PLACEHOLDER___', $html);
        $html = str_replace($siteUrl, '', $html);
        $html = str_replace('___STORAGE_PLACEHOLDER___', $siteUrl.'/storage/', $html);
        $html = str_replace('___BUILD_PLACEHOLDER___', $siteUrl.'/build/', $html);

        // Fix home link: route('home') renders as the bare site URL,
        // which becomes an empty href after replacement. Point it to "/".
        $html = str_replace('href=""', 'href="/"', $html);

        // Convert remaining relative /storage/ URLs to absolute production URLs
        // so images load from production (Storage::url() returns relative paths).
        $html = str_replace('"/storage/', '"'.$siteUrl.'/storage/', $html);
        $html = str_replace("'/storage/", "'".$siteUrl.'/storage/', $html);

        return response($html, $response->status())
            ->header('Content-Type', $response->header('Content-Type') ?? 'text/html');
    }

    /**
     * Make a GET request to the production API.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function apiGet(string $endpoint, array $params = []): array
    {
        $url = $this->getApiBaseUrl().'/'.ltrim($endpoint, '/');

        $response = Http::acceptJson()
            ->connectTimeout(30)
            ->timeout(60)
            ->get($url, $params);

        return $response->json() ?? [];
    }

    /**
     * Make a POST request to the production API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function apiPost(string $endpoint, array $data = []): array
    {
        $url = $this->getApiBaseUrl().'/'.ltrim($endpoint, '/');

        $response = Http::acceptJson()
            ->connectTimeout(30)
            ->timeout(60)
            ->post($url, $data);

        return $response->json() ?? [];
    }

    /**
     * Convert an array (or collection of arrays) to objects
     * so Blade templates can use ->property syntax.
     */
    protected function toObject(mixed $data): mixed
    {
        if (is_array($data)) {
            // Indexed arrays (lists like best_seasons, coordinates) stay as arrays
            if (array_is_list($data)) {
                return array_map(fn ($item) => $this->toObject($item), $data);
            }

            // Associative arrays become objects
            return (object) array_map(fn ($item) => $this->toObject($item), $data);
        }

        return $data;
    }

    /**
     * Convert a list of arrays to a collection of objects.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function toObjectCollection(array $items): \Illuminate\Support\Collection
    {
        return collect($items)->map(fn ($item) => $this->toObject($item));
    }

    /**
     * Resolve relative URLs (like /storage/...) to absolute production URLs.
     * Applies recursively to all string values in an object or array.
     */
    protected function resolveMediaUrls(mixed $data): mixed
    {
        $siteUrl = rtrim(config('nativephp.site_url', 'https://trails.xploresmithers.com'), '/');

        if (is_string($data) && str_starts_with($data, '/storage/')) {
            return $siteUrl.$data;
        }

        if ($data instanceof \stdClass) {
            foreach (get_object_vars($data) as $key => $value) {
                $data->$key = $this->resolveMediaUrls($value);
            }

            return $data;
        }

        if (is_array($data)) {
            return array_map(fn ($item) => $this->resolveMediaUrls($item), $data);
        }

        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->map(fn ($item) => $this->resolveMediaUrls($item));
        }

        return $data;
    }

    /**
     * Get the production API base URL.
     */
    private function getApiBaseUrl(): string
    {
        return rtrim(config('nativephp.api_url', 'https://trails.xploresmithers.com/api'), '/');
    }
}
