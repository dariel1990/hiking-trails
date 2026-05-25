<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify a Google reCAPTCHA v3 token.
     *
     * Returns true when the token is valid, the action matches the expected
     * action, and the score is at or above the configured threshold. The
     * service fails closed when secrets are missing or the API call errors.
     */
    public function verify(?string $token, string $expectedAction, ?string $remoteIp = null): bool
    {
        if (blank($token)) {
            return false;
        }

        $secret = config('services.recaptcha.secret_key');

        if (blank($secret)) {
            Log::warning('reCAPTCHA secret is not configured — rejecting submission.');

            return false;
        }

        $response = Http::asForm()
            ->timeout(5)
            ->post((string) config('services.recaptcha.verify_url'), array_filter([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]));

        if (! $response->successful()) {
            Log::warning('reCAPTCHA verify request failed', ['status' => $response->status()]);

            return false;
        }

        $data = $response->json();

        $minScore = (float) config('services.recaptcha.min_score', 0.5);
        $score = (float) ($data['score'] ?? 0);
        $action = $data['action'] ?? null;
        $success = (bool) ($data['success'] ?? false);

        Log::info('reCAPTCHA verify response', [
            'success' => $success,
            'score' => $score,
            'action' => $action,
            'expected_action' => $expectedAction,
            'min_score' => $minScore,
            'error_codes' => $data['error-codes'] ?? [],
            'hostname' => $data['hostname'] ?? null,
        ]);

        if (! $success) {
            return false;
        }

        if ($action !== $expectedAction) {
            return false;
        }

        return $score >= $minScore;
    }
}
