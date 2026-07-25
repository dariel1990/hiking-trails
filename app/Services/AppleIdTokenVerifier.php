<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Verifies Sign in with Apple identity tokens (JWS) against Apple's public
 * keys. Unlike AppStoreSubscriptionVerifier (which treats notification
 * payloads as untrusted hints and re-fetches from Apple), an identity token
 * IS the proof of identity, so its signature must be checked.
 */
class AppleIdTokenVerifier
{
    private const JWKS_URL = 'https://appleid.apple.com/auth/keys';

    private const ISSUER = 'https://appleid.apple.com';

    /**
     * Returns the verified claims (sub, email?, email_verified?, ...) or null
     * when the token is invalid, expired, or not issued for this app.
     *
     * @return array<string, mixed>|null
     */
    public function verify(string $idToken): ?array
    {
        try {
            $jwks = Cache::remember('apple_signin_jwks', 3600, function (): array {
                return Http::timeout(10)->get(self::JWKS_URL)->throw()->json();
            });

            $claims = (array) JWT::decode($idToken, JWK::parseKeySet($jwks, 'RS256'));
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        if (($claims['iss'] ?? null) !== self::ISSUER) {
            return null;
        }

        if (($claims['aud'] ?? null) !== config('services.apple.signin_bundle_id')) {
            return null;
        }

        return $claims;
    }
}
