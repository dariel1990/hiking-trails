<?php

namespace App\Services;

use App\Models\Subscription;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * iOS counterpart of GooglePlaySubscriptionVerifier.
 *
 * The app posts the StoreKit 2 signed transaction (a JWS) as `purchaseToken`.
 * The JWS is only used to extract the originalTransactionId — the trust anchor
 * is Apple's App Store Server API ("Get All Subscription Statuses"), queried
 * directly over TLS with an ES256-signed App Store Connect JWT.
 *
 * Uses firebase/php-jwt for the ES256 signature (already installed as a
 * dependency of google/auth).
 *
 * Config (config/services.php → app_store): issuer_id + key_id + private_key
 * from an App Store Connect "In-App Purchase" API key (.p8).
 */
class AppStoreSubscriptionVerifier
{
    private const PROD_BASE = 'https://api.storekit.itunes.apple.com/inApps/v1';

    private const SANDBOX_BASE = 'https://api.storekit-sandbox.itunes.apple.com/inApps/v1';

    public function __construct(
        private readonly ?string $issuerId = null,
        private readonly ?string $keyId = null,
        private readonly ?string $privateKeyPath = null,
        private readonly ?string $bundleId = null,
    ) {}

    /**
     * Verify a StoreKit 2 signed transaction and return normalized state.
     *
     * @return array{
     *     status: string,
     *     expiresAt: ?Carbon,
     *     autoRenewing: bool,
     *     originalTransactionId: string,
     *     productId: ?string,
     *     raw: array<string, mixed>,
     * }
     */
    public function verify(string $signedTransaction): array
    {
        $claims = $this->decodeJwsPayload($signedTransaction);

        $originalTransactionId = (string) ($claims['originalTransactionId'] ?? '');
        if ($originalTransactionId === '') {
            throw new RuntimeException('Signed transaction carries no originalTransactionId.');
        }

        $claimedBundle = (string) ($claims['bundleId'] ?? '');
        if ($claimedBundle !== '' && $claimedBundle !== $this->bundle()) {
            throw new RuntimeException("Signed transaction bundle mismatch ({$claimedBundle}).");
        }

        $statuses = $this->fetchStatuses($originalTransactionId);

        $last = $this->latestTransaction($statuses, $originalTransactionId);
        $appleStatus = (int) ($last['status'] ?? 0);
        $status = Subscription::APPLE_STATE_MAP[$appleStatus] ?? 'expired';

        $transaction = $this->decodeJwsPayload((string) ($last['signedTransactionInfo'] ?? ''));
        $renewal = [];
        if (! empty($last['signedRenewalInfo'])) {
            $renewal = $this->decodeJwsPayload((string) $last['signedRenewalInfo']);
        }

        $expiresMs = $transaction['expiresDate'] ?? null;

        return [
            'status' => $status,
            'expiresAt' => $expiresMs ? Carbon::createFromTimestampMs((int) $expiresMs) : null,
            'autoRenewing' => (int) ($renewal['autoRenewStatus'] ?? 0) === 1,
            'originalTransactionId' => $originalTransactionId,
            'productId' => isset($transaction['productId']) ? (string) $transaction['productId'] : null,
            'raw' => [
                'appleStatus' => $appleStatus,
                'transaction' => $transaction,
                'renewal' => $renewal,
            ],
        ];
    }

    /**
     * GET /inApps/v1/subscriptions/{originalTransactionId}. Production first,
     * then sandbox. 404 = transaction lives in the sandbox environment
     * (Apple's documented pattern). 401 also falls back: production answers
     * Unauthenticated — not 404 — for apps that have never been released to
     * the App Store, even with valid credentials.
     *
     * @return array<string, mixed>
     */
    private function fetchStatuses(string $originalTransactionId): array
    {
        $jwt = $this->appStoreJwt();
        $path = '/subscriptions/'.rawurlencode($originalTransactionId);

        $response = $this->http($jwt)->get(self::PROD_BASE.$path);

        if (in_array($response->status(), [401, 404], true)) {
            $response = $this->http($jwt)->get(self::SANDBOX_BASE.$path);
        }

        $this->ensureOk($response, 'subscriptions.statuses');

        return (array) $response->json();
    }

    /**
     * Pick the last transaction for our subscription, preferring the exact
     * originalTransactionId match across subscription groups.
     *
     * @param  array<string, mixed>  $statuses
     * @return array<string, mixed>
     */
    private function latestTransaction(array $statuses, string $originalTransactionId): array
    {
        $fallback = null;
        foreach ((array) ($statuses['data'] ?? []) as $group) {
            foreach ((array) ($group['lastTransactions'] ?? []) as $item) {
                $fallback ??= $item;
                if ((string) ($item['originalTransactionId'] ?? '') === $originalTransactionId) {
                    return (array) $item;
                }
            }
        }

        if ($fallback === null) {
            throw new RuntimeException('App Store returned no transactions for this subscription.');
        }

        return (array) $fallback;
    }

    /** Short-lived ES256 App Store Connect API token. */
    private function appStoreJwt(): string
    {
        $issuerId = $this->issuerId ?? (string) config('services.app_store.issuer_id');
        $keyId = $this->keyId ?? (string) config('services.app_store.key_id');

        if (blank($issuerId) || blank($keyId)) {
            throw new RuntimeException('APP_STORE_ISSUER_ID / APP_STORE_KEY_ID are not configured.');
        }

        $now = time();

        return JWT::encode(
            [
                'iss' => $issuerId,
                'iat' => $now,
                'exp' => $now + 300,
                'aud' => 'appstoreconnect-v1',
                'bid' => $this->bundle(),
            ],
            $this->privateKey(),
            'ES256',
            $keyId,
        );
    }

    private function privateKey(): string
    {
        $path = $this->privateKeyPath ?? (string) config('services.app_store.private_key');

        if (blank($path)) {
            throw new RuntimeException('APP_STORE_PRIVATE_KEY is not configured.');
        }

        $resolved = str_starts_with($path, '/') ? $path : base_path($path);

        if (! is_file($resolved) || ! is_readable($resolved)) {
            throw new RuntimeException("App Store private key not readable at {$resolved}.");
        }

        return (string) file_get_contents($resolved);
    }

    private function bundle(): string
    {
        $bundle = $this->bundleId ?? (string) config('services.app_store.bundle_id');

        if (blank($bundle)) {
            throw new RuntimeException('APP_STORE_BUNDLE_ID is not configured.');
        }

        return $bundle;
    }

    /**
     * Base64url-decode a JWS payload without signature verification — used on
     * JWSes we either treat as untrusted hints (the client transaction, only
     * to learn the transaction id) or that arrived from Apple over TLS.
     *
     * @return array<string, mixed>
     */
    private function decodeJwsPayload(string $jws): array
    {
        $parts = explode('.', $jws);

        if (count($parts) !== 3) {
            throw new RuntimeException('Malformed JWS.');
        }

        $decoded = base64_decode(strtr($parts[1], '-_', '+/'), false);
        $payload = $decoded === false ? null : json_decode($decoded, true);

        if (! is_array($payload)) {
            throw new RuntimeException('Malformed JWS payload.');
        }

        return $payload;
    }

    private function http(string $jwt): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken($jwt)->acceptJson()->timeout(10);
    }

    private function ensureOk(Response $response, string $operation): void
    {
        if ($response->successful()) {
            return;
        }

        Log::warning("App Store Server API call failed: {$operation}", [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new RuntimeException("App Store {$operation} failed with status {$response->status()}.");
    }
}
