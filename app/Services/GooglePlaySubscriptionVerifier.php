<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GooglePlaySubscriptionVerifier
{
    private const SCOPE = 'https://www.googleapis.com/auth/androidpublisher';

    private const BASE = 'https://androidpublisher.googleapis.com/androidpublisher/v3/applications';

    public function __construct(
        private readonly ?string $packageName = null,
        private readonly ?string $serviceAccountJsonPath = null,
    ) {}

    /**
     * Fetch subscription state for a purchase token via subscriptionsv2.get.
     *
     * @return array<string, mixed>
     */
    public function getSubscription(string $purchaseToken): array
    {
        $package = $this->package();
        $url = self::BASE."/{$package}/purchases/subscriptionsv2/tokens/".rawurlencode($purchaseToken);

        $response = $this->http()->get($url);

        $this->ensureOk($response, 'subscriptionsv2.get');

        return (array) $response->json();
    }

    /**
     * Acknowledge a one-shot or renewal purchase so Play does not auto-refund.
     */
    public function acknowledge(string $productId, string $purchaseToken): void
    {
        $package = $this->package();
        $url = self::BASE."/{$package}/purchases/subscriptions/"
            .rawurlencode($productId).'/tokens/'.rawurlencode($purchaseToken).':acknowledge';

        $response = $this->http()->post($url);

        if ($response->status() === 400 && str_contains((string) $response->body(), 'already acknowledged')) {
            return;
        }

        $this->ensureOk($response, 'subscriptions.acknowledge');
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(10);
    }

    private function accessToken(): string
    {
        $path = $this->serviceAccountJsonPath ?? (string) config('services.google_play.service_account_json');

        if (blank($path)) {
            throw new RuntimeException('GOOGLE_PLAY_SERVICE_ACCOUNT_JSON is not configured.');
        }

        $resolved = $this->resolvePath($path);

        if (! is_file($resolved) || ! is_readable($resolved)) {
            throw new RuntimeException("Service account JSON not readable at {$resolved}.");
        }

        $credentials = new ServiceAccountCredentials(self::SCOPE, $resolved);
        $token = $credentials->fetchAuthToken();

        if (empty($token['access_token'])) {
            throw new RuntimeException('Failed to obtain Google Play access token.');
        }

        return (string) $token['access_token'];
    }

    private function package(): string
    {
        $package = $this->packageName ?? (string) config('services.google_play.package_name');

        if (blank($package)) {
            throw new RuntimeException('GOOGLE_PLAY_PACKAGE_NAME is not configured.');
        }

        return $package;
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    private function ensureOk(Response $response, string $operation): void
    {
        if ($response->successful()) {
            return;
        }

        Log::warning("Google Play API call failed: {$operation}", [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new RuntimeException("Google Play {$operation} failed with status {$response->status()}.");
    }
}
