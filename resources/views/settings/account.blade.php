@extends('layouts.settings')

@section('title', 'Account')

@section('settings-content')
{{-- Section header --}}
<div class="border-b border-gray-100 px-7 py-5">
    <h2 class="text-base font-bold text-gray-900" style="font-family: 'Inter', sans-serif;">Account</h2>
    <p class="text-sm text-gray-400 mt-0.5">Update your email, password, and connected services.</p>
</div>

<div class="divide-y divide-gray-100">

    {{-- Contact & password --}}
    <form method="POST" action="{{ route('settings.account.update') }}" class="px-7 py-8 space-y-5">
        @csrf
        @method('PUT')

        @if(session('success'))
            <div class="flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-2 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Contact</p>

        <div class="grid gap-1.5">
            <label for="email" class="text-xs font-semibold text-gray-600">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                   class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-mono text-gray-900 placeholder:text-gray-300 transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('email') border-red-400 bg-red-50 @enderror">
            @error('email')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-1.5">
            <label for="phone" class="text-xs font-semibold text-gray-600">Phone number</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                   placeholder="(250) 555-0123"
                   class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-300 transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('phone') border-red-400 bg-red-50 @enderror">
            <p class="text-xs text-gray-400">Used only if you opt in to SMS updates.</p>
            @error('phone')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 pt-3">Password</p>

        @if($user->password)
            <div class="grid gap-1.5">
                <label for="current_password" class="text-xs font-semibold text-gray-600">Current password</label>
                <input type="password" id="current_password" name="current_password" placeholder="••••••••" autocomplete="current-password"
                       class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-300 transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('current_password') border-red-400 bg-red-50 @enderror">
                @error('current_password')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div class="grid sm:grid-cols-2 gap-5">
            <div class="grid gap-1.5">
                <label for="password" class="text-xs font-semibold text-gray-600">New password</label>
                <input type="password" id="password" name="password" placeholder="Min. 8 characters" autocomplete="new-password"
                       class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-300 transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('password') border-red-400 bg-red-50 @enderror">
                @error('password')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid gap-1.5">
                <label for="password_confirmation" class="text-xs font-semibold text-gray-600">Confirm new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat password" autocomplete="new-password"
                       class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-300 transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
            </div>
        </div>
        <p class="text-xs text-gray-400 -mt-2">Leave both fields blank to keep your current password.</p>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-gray-700 active:scale-95">
                Save changes
            </button>
        </div>
    </form>

    {{-- Connected accounts --}}
    <div class="px-7 py-8">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-5">Connected accounts</p>

        <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white border border-gray-200 shadow-sm">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Google</p>
                    <p class="text-xs text-gray-400">Sign in with your Google account</p>
                </div>
            </div>

            @if($googleConnected)
                @if($canDisconnectGoogle)
                    <form method="POST" action="{{ route('settings.account.google.disconnect') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-lg border border-gray-200 bg-white px-4 py-1.5 text-xs font-semibold text-gray-600 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 active:scale-95">
                            Disconnect
                        </button>
                    </form>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Connected
                    </span>
                @endif
            @else
                <a href="{{ route('google.redirect') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-gray-700 active:scale-95">
                    Connect
                </a>
            @endif
        </div>
    </div>

    {{-- Delete account --}}
    <div class="px-7 py-8" x-data="{ confirmOpen: {{ $errors->has('password') ? 'true' : 'false' }} }">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Danger zone</p>
        <p class="text-sm text-gray-500 mb-5">Deleting your account hides your profile and signs you out. Contact support to fully remove your data.</p>
        <button type="button" @click="confirmOpen = true"
                class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-600 transition-all hover:bg-red-600 hover:text-white hover:border-red-600 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Delete account
        </button>

        <div x-show="confirmOpen" x-cloak
             class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
             @keydown.escape.window="confirmOpen = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7" @click.outside="confirmOpen = false">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Delete your account?</h3>
                <p class="text-sm text-gray-500 mb-5 leading-relaxed">
                    @if($user->password)
                        Enter your password to confirm. You'll be signed out immediately and your data will be hidden.
                    @else
                        You'll be signed out immediately and your data will be hidden.
                    @endif
                </p>

                <form method="POST" action="{{ route('settings.account.destroy') }}">
                    @csrf
                    @method('DELETE')

                    @if($user->password)
                        <input type="password" name="password" placeholder="Your password" autocomplete="current-password"
                               class="h-10 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-300 mb-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        @error('password')
                            <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endif

                    <div class="flex gap-3 mt-4">
                        <button type="submit"
                                class="flex-1 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 text-sm transition-colors active:scale-95">
                            Delete account
                        </button>
                        <button type="button" @click="confirmOpen = false"
                                class="flex-1 rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold py-2.5 text-sm transition-colors active:scale-95">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
