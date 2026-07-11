<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class WebGoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
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
                $user->notify(new WelcomeNotification(viaGoogle: true));
            } catch (Throwable $e) {
                report($e);
            }
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
