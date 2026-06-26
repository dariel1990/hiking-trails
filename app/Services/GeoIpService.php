<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    private const FALLBACK = 'CA';

    private const TTL_HOURS = 24;

    public function countryCode(string $ip): string
    {
        // Dev override: set GEO_OVERRIDE_COUNTRY=PH in .env to test regional pricing locally.
        if ($override = config('services.geo_override_country')) {
            return strtoupper($override);
        }

        if ($this->isPrivateOrLocal($ip)) {
            return self::FALLBACK;
        }

        return Cache::remember("geoip:{$ip}", now()->addHours(self::TTL_HOURS), function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'countryCode,status',
                ]);

                if ($response->successful() && $response->json('status') === 'success') {
                    return (string) ($response->json('countryCode') ?? self::FALLBACK);
                }
            } catch (\Throwable $e) {
                Log::debug('GeoIpService lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
            }

            return self::FALLBACK;
        });
    }

    private function isPrivateOrLocal(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
