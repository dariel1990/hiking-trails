<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\LegalAcceptance;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\LegalDocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class WebAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This account has been deactivated.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister(LegalDocumentService $legalDocuments): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.register', [
            'documents' => $legalDocuments->reviewPayload($legalDocuments->currentVersions()),
        ]);
    }

    public function register(RegisterRequest $request, LegalDocumentService $legalDocuments): RedirectResponse
    {
        $currentVersions = $legalDocuments->currentVersions();

        if (! $legalDocuments->matchesVersions($request->validated('accepted_versions'), $currentVersions)) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['accepted_versions' => 'These documents were just updated. Please review the latest versions.']);
        }

        $firstName = $request->string('first_name')->value();
        $lastName = $request->string('last_name')->value();

        $user = User::create([
            'name' => trim("{$firstName} {$lastName}"),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->string('email')->value(),
            'password' => $request->string('password')->value(),
        ]);

        $legalDocuments->recordAcceptance($user, $currentVersions, $request, LegalAcceptance::SOURCE_WEB);

        try {
            $user->notify(new WelcomeNotification);
        } catch (Throwable $e) {
            report($e);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            report($e);
        }

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
