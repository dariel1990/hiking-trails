<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreGoogleSignUpConsentRequest;
use App\Models\LegalAcceptance;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\LegalDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class WebGoogleAuthController extends Controller
{
    private const ACCEPTED_VERSIONS_SESSION_KEY = 'google_sign_up.accepted_versions';

    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Remember what the visitor accepted on the register page. No account
     * exists until Google returns, so it is recorded in the callback.
     */
    public function acceptLegalDocuments(StoreGoogleSignUpConsentRequest $request, LegalDocumentService $legalDocuments): RedirectResponse
    {
        if (! $legalDocuments->matchesVersions($request->validated('accepted_versions'), $legalDocuments->currentVersions())) {
            return redirect()
                ->route('register')
                ->with('legalReviewAction', 'google')
                ->withErrors(['accepted_versions' => 'These documents were just updated. Please review the latest versions.']);
        }

        $request->session()->put(self::ACCEPTED_VERSIONS_SESSION_KEY, $request->validated('accepted_versions'));

        return redirect()->route('google.redirect');
    }

    public function callback(Request $request, LegalDocumentService $legalDocuments): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('home')->with('error', 'Sign in with Google failed. Please try again.');
        }

        if (empty($googleUser->getEmail())) {
            return redirect()->route('home')->with('error', 'Google did not return an email address.');
        }

        $existing = User::where('email', $googleUser->getEmail())->first();

        if ($existing && ! $existing->is_active) {
            return redirect()->route('home')->with('error', 'This account has been deactivated.');
        }

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?: $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
            ]
        );

        // A Google sign-in proves ownership of the address, even for accounts
        // that registered earlier and never verified.
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if ($user->wasRecentlyCreated) {
            try {
                $user->notify(new WelcomeNotification(provider: 'google'));
            } catch (Throwable $e) {
                report($e);
            }
        }

        $acceptedVersionIds = $request->session()->pull(self::ACCEPTED_VERSIONS_SESSION_KEY);
        $currentVersions = $legalDocuments->currentVersions();

        if ($acceptedVersionIds !== null && $legalDocuments->matchesVersions($acceptedVersionIds, $currentVersions)) {
            $legalDocuments->recordAcceptance($user, $currentVersions, $request, LegalAcceptance::SOURCE_WEB);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
