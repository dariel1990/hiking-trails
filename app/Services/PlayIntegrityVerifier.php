<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Verifies a Play Integrity token issued by the Android app.
 *
 * Flow:
 *   1. Android client generates a nonce and calls the Play Integrity API.
 *   2. Client sends {integrity_token, nonce} to POST /api/auth/app-token.
 *   3. This service decodes the token via Google's server-side API and
 *      validates the verdict fields before we issue a per-install app token.
 *
 * Required env:
 *   GOOGLE_PLAY_SERVICE_ACCOUNT_JSON  — path to the same service-account JSON
 *                                       used by GooglePlaySubscriptionVerifier.
 *   GOOGLE_PLAY_PACKAGE_NAME          — com.xploresmithers.app
 */
class PlayIntegrityVerifier
{
    private const SCOPE = 'https://www.googleapis.com/auth/playintegrity';

    private const DECODE_URL = 'https://playintegrity.googleapis.com/v1/%s:decodeIntegrityToken';

    /** Maximum age of an integrity token we will accept (10 minutes). */
    private const MAX_TOKEN_AGE_MS = 10 * 60 * 1_000;

    public function verify(string $integrityToken, string $expectedNonce): array
    {
        $package = $this->packageName();
        $url = sprintf(self::DECODE_URL, rawurlencode($package));

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(10)
            ->post($url, ['integrity_token' => $integrityToken]);

        if (! $response->successful()) {
            Log::warning('PlayIntegrity decodeIntegrityToken failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('Play Integrity verification failed.');
        }

        $payload = $response->json('tokenPayloadExternal', []);

        $this->assertRequestDetails($payload, $expectedNonce, $package);
        $this->assertAppIntegrity($payload, $package);
        $this->assertDeviceIntegrity($payload);

        return $payload;
    }

    private function assertRequestDetails(array $payload, string $expectedNonce, string $package): void
    {
        $details = $payload['requestDetails'] ?? [];

        if (($details['requestPackageName'] ?? '') !== $package) {
            throw new RuntimeException('Integrity token package name mismatch.');
        }

        $nonce = $details['nonce'] ?? '';
        // The nonce is Base64-encoded by the client; compare decoded values.
        if (base64_decode($nonce, strict: false) !== $expectedNonce
            && $nonce !== $expectedNonce) {
            throw new RuntimeException('Integrity token nonce mismatch.');
        }

        $timestampMs = (int) ($details['timestampMillis'] ?? 0);
        $ageMs = (int) round(microtime(true) * 1000) - $timestampMs;
        if ($ageMs > self::MAX_TOKEN_AGE_MS || $ageMs < 0) {
            throw new RuntimeException('Integrity token has expired or is from the future.');
        }
    }

    private function assertAppIntegrity(array $payload, string $package): void
    {
        $app = $payload['appIntegrity'] ?? [];

        if (($app['appRecognitionVerdict'] ?? '') !== 'PLAY_RECOGNIZED') {
            throw new RuntimeException('App not recognised by Play Integrity.');
        }

        if (($app['packageName'] ?? '') !== $package) {
            throw new RuntimeException('App package name in integrity verdict does not match.');
        }
    }

    private function assertDeviceIntegrity(array $payload): void
    {
        $verdicts = $payload['deviceIntegrity']['deviceRecognitionVerdict'] ?? [];

        // MEETS_DEVICE_INTEGRITY means the device passes Play's basic hardware checks.
        if (! in_array('MEETS_DEVICE_INTEGRITY', $verdicts, strict: true)) {
            throw new RuntimeException('Device does not meet Play Integrity requirements.');
        }
    }

    private function accessToken(): string
    {
        $path = (string) config('services.google_play.service_account_json');

        if (blank($path)) {
            throw new RuntimeException('GOOGLE_PLAY_SERVICE_ACCOUNT_JSON is not configured.');
        }

        $resolved = str_starts_with($path, '/') ? $path : base_path($path);

        if (! is_file($resolved) || ! is_readable($resolved)) {
            throw new RuntimeException("Service account JSON not readable at {$resolved}.");
        }

        $credentials = new ServiceAccountCredentials(self::SCOPE, $resolved);
        $token = $credentials->fetchAuthToken();

        if (empty($token['access_token'])) {
            throw new RuntimeException('Failed to obtain Play Integrity access token.');
        }

        return (string) $token['access_token'];
    }

    private function packageName(): string
    {
        $package = (string) config('services.google_play.package_name');

        if (blank($package)) {
            throw new RuntimeException('GOOGLE_PLAY_PACKAGE_NAME is not configured.');
        }

        return $package;
    }
}
