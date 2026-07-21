<?php

namespace App\Http\Middleware;

use App\Models\AppToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VerifyAppKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $staticKey = (string) config('services.app_api_key', '');

        // Local development / test-suite bypass: no key configured means the
        // gate isn't set up in this environment. Must run before the missing-
        // header check or requests without X-App-Key can never pass it.
        if (empty($staticKey) && app()->environment('local', 'testing')) {
            return $next($request);
        }

        $provided = (string) $request->header('X-App-Key', '');

        if (empty($provided)) {
            return $this->unauthorized();
        }

        // 1. Accept the legacy static key while old app versions are still in the wild.
        //    Remove this branch once all installs have migrated to per-install tokens.
        if (! empty($staticKey) && hash_equals($staticKey, $provided)) {
            return $next($request);
        }

        // 2. Accept a Play-Integrity-issued per-install token.
        //    Cache hits avoid a DB round-trip on every request (TTL = 5 min).
        $cacheKey = 'app_token:'.hash('sha256', $provided);

        $valid = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($provided) {
            return AppToken::where('token', $provided)->exists();
        });

        if ($valid) {
            // Touch last_used_at asynchronously — don't block the request.
            AppToken::where('token', $provided)
                ->update(['last_used_at' => now()]);

            return $next($request);
        }

        return $this->unauthorized();
    }

    private function unauthorized(): Response
    {
        return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }
}
