@extends('layouts.settings')

@section('title', 'Account')

@section('settings-content')
<h2 class="text-xl font-bold text-gray-900 mb-8">Account</h2>

<form method="POST" action="{{ route('settings.account.update') }}" class="mb-10">
    @csrf
    @method('PUT')

    <div class="mb-6">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-6">
        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone number</label>
        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="(250) 555-0123"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <p class="mt-1.5 text-xs text-gray-500">We'll only use your phone number if you opt into SMS updates from a live share contact.</p>
        @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if($user->password)
        <div class="mb-6">
            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">Current password</label>
            <input type="password" id="current_password" name="current_password" value="{{ old('current_password') }}" placeholder="Current password" autocomplete="current-password"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            @error('current_password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
        <input type="password" id="password" name="password" placeholder="New password" autocomplete="new-password"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <p class="mt-1.5 text-xs text-gray-500">Leave blank to keep your current password.</p>
        @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mb-10">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" autocomplete="new-password"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
    </div>

    <div class="border-t border-gray-100 pt-8">
        <button type="submit"
                class="rounded-full bg-forest-600 hover:bg-forest-700 text-white font-semibold py-2.5 px-7 transition-colors">
            Save
        </button>
    </div>
</form>

<div class="border-t border-gray-100 pt-8 mb-10">
    <h3 class="text-sm font-semibold text-gray-900 mb-4">Connected accounts</h3>
    <div class="flex items-center justify-between py-2">
        <span class="text-sm text-gray-700">Google</span>
        @if($googleConnected)
            @if($canDisconnectGoogle)
                <form method="POST" action="{{ route('settings.account.google.disconnect') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-600 hover:text-red-600 border border-gray-300 rounded-full px-4 py-1.5 transition-colors">
                        Disconnect
                    </button>
                </form>
            @else
                <span class="text-sm font-medium text-emerald-700 bg-emerald-50 rounded-full px-4 py-1.5">Connected</span>
            @endif
        @else
            <a href="{{ route('google.redirect') }}" class="text-sm font-medium text-white bg-black hover:bg-gray-800 rounded-full px-4 py-1.5 transition-colors">
                Connect
            </a>
        @endif
    </div>
</div>

<div class="border-t border-gray-100 pt-8" x-data="{ confirmOpen: {{ $errors->has('password') ? 'true' : 'false' }} }">
    <h3 class="text-sm font-semibold text-gray-900 mb-1">Delete account</h3>
    <p class="text-sm text-gray-500 mb-4">Deactivating your account hides your profile and signs you out. Contact support if you'd like your data fully removed.</p>
    <button type="button" @click="confirmOpen = true"
            class="text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-full px-5 py-2 transition-colors">
        Delete account
    </button>

    <div x-show="confirmOpen" x-cloak
         class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 p-4"
         @keydown.escape.window="confirmOpen = false">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" @click.outside="confirmOpen = false">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete your account?</h3>
            <p class="text-sm text-gray-500 mb-4">
                @if($user->password)
                    Enter your password to confirm. You'll be signed out immediately.
                @else
                    You'll be signed out immediately.
                @endif
            </p>

            <form method="POST" action="{{ route('settings.account.destroy') }}">
                @csrf
                @method('DELETE')

                @if($user->password)
                    <input type="password" name="password" placeholder="Password" autocomplete="current-password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-2">
                    @error('password')
                        <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @endif

                <div class="flex gap-3 mt-4">
                    <button type="submit" class="flex-1 rounded-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 transition-colors">
                        Delete account
                    </button>
                    <button type="button" @click="confirmOpen = false" class="flex-1 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
