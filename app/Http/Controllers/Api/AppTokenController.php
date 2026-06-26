<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppToken;
use App\Services\PlayIntegrityVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Issues a per-install app token in exchange for a verified Play Integrity token.
 *
 * The issued token replaces the static APP_API_KEY that was previously baked
 * into the APK. It is stored in EncryptedSharedPreferences on the device and
 * sent as X-App-Key on every subsequent API request.
 *
 * This endpoint is intentionally exempt from the VerifyAppKey middleware
 * (it must be reachable before the app has a key), but is rate-limited and
 * requires a valid Play Integrity verdict before issuing anything.
 */
class AppTokenController extends Controller
{
    public function issue(Request $request, PlayIntegrityVerifier $verifier): JsonResponse
    {
        $validated = $request->validate([
            'integrity_token' => ['required', 'string', 'max:4096'],
            'nonce'           => ['required', 'string', 'max:256'],
        ]);

        try {
            $verifier->verify($validated['integrity_token'], $validated['nonce']);
        } catch (Throwable $e) {
            Log::warning('AppToken: Play Integrity verification failed', [
                'reason' => $e->getMessage(),
                'ip'     => $request->ip(),
            ]);

            return response()->json(
                ['message' => 'Integrity check failed.'],
                Response::HTTP_FORBIDDEN,
            );
        }

        $token = AppToken::create([
            'token'        => Str::random(64),
            'package_name' => config('services.google_play.package_name'),
        ]);

        return response()->json(['app_token' => $token->token], Response::HTTP_CREATED);
    }
}
