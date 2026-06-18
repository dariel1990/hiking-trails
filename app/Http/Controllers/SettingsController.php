<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\DeactivateAccountRequest;
use App\Http\Requests\Settings\UpdateAccountRequest;
use App\Http\Requests\Settings\UpdateAvatarRequest;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function profile(Request $request): View
    {
        $user = $request->user();
        [$fallbackFirst, $fallbackLast] = array_pad(explode(' ', $user->name, 2), 2, '');

        return view('settings.profile', [
            'user' => $user,
            'firstName' => $user->first_name ?? $fallbackFirst,
            'lastName' => $user->last_name ?? $fallbackLast,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->first_name = $request->string('first_name')->value();
        $user->last_name = $request->string('last_name')->value();
        $user->bio = $request->input('bio');
        $user->name = trim("{$user->first_name} {$user->last_name}");
        $user->save();

        return redirect()->route('settings.profile')->with('success', 'Profile updated successfully!');
    }

    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = $request->file('avatar')->store('avatars', 'public');
        $user->save();

        return redirect()->route('settings.profile')->with('success', 'Profile photo updated!');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return redirect()->route('settings.profile')->with('success', 'Profile photo removed.');
    }

    public function account(Request $request): View
    {
        $user = $request->user();

        return view('settings.account', [
            'user' => $user,
            'googleConnected' => filled($user->google_id),
            'canDisconnectGoogle' => filled($user->password),
        ]);
    }

    public function updateAccount(UpdateAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->email !== $request->string('email')->value()) {
            $user->email = $request->string('email')->value();
            $user->email_verified_at = null;
        }

        $user->phone = $request->input('phone');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->string('password')->value());
        }

        $user->save();

        $message = 'Account updated successfully!';
        if ($user->wasChanged('email')) {
            $message .= ' Please verify your new email address.';
        }

        return redirect()->route('settings.account')->with('success', $message);
    }

    public function destroyAccount(DeactivateAccountRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->is_active = false;
        $user->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deactivated.');
    }

    public function disconnectGoogle(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! filled($user->password)) {
            return redirect()->route('settings.account')
                ->with('error', 'Set a password before disconnecting Google, so you don\'t get locked out.');
        }

        $user->google_id = null;
        $user->save();

        return redirect()->route('settings.account')->with('success', 'Google account disconnected.');
    }

    public function subscription(Request $request): View
    {
        $user = $request->user();
        $subscription = $user->currentProSubscription();
        $isPro = $user->hasActiveProEntitlement();

        $prices = [
            'xs_offline_monthly' => '4.99/mo',
            'xs_pro_web_monthly' => '4.99/mo',
            'xs_offline_annual' => '39.99/yr',
            'xs_pro_web_annual' => '39.99/yr',
        ];

        return view('settings.subscription', [
            'isPro' => $isPro,
            'subscription' => $subscription,
            'priceLabel' => $subscription ? ($prices[$subscription->product_id] ?? null) : null,
            'isAnnual' => $subscription && str($subscription->product_id)->endsWith('annual'),
            'isGooglePlay' => $subscription && in_array($subscription->product_id, Subscription::OFFLINE_PRODUCT_IDS, true),
        ]);
    }
}
