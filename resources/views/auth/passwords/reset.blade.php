@extends('layouts.public')

@section('title', 'Reset Password')

@push('styles')
<style>
    .auth-stage {
        min-height: 100dvh;
        background-color: #15292a; /* neutral fallback while the photo loads */
        position: relative;
        overflow: hidden;
    }
    /* Blurred photographic background. Scaled up so the blur never reveals edges. */
    .auth-bg {
        position: absolute; inset: 0; pointer-events: none;
        background: url("{{ asset('images/login-background.jpg') }}") center / cover no-repeat;
        filter: blur(6px);
        transform: scale(1.06);
    }
    /* Neutral dark scrim (no colour) so the white card and text stay legible. */
    .auth-overlay {
        position: absolute; inset: 0; pointer-events: none;
        background: rgba(12, 18, 18, 0.45);
    }
    /* Fine grain so the background never looks plasticky */
    .auth-noise {
        position: absolute; inset: 0; pointer-events: none; opacity: 0.5;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
    }

    @keyframes auth-rise {
        from { transform: translateY(24px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .auth-card { animation: auth-rise .6s cubic-bezier(.16,1,.3,1) both; }

    .auth-head {
        background: linear-gradient(150deg, #2C5F5D 0%, #1a2e2e 100%);
        position: relative; overflow: hidden;
    }
    .auth-head::after {
        content: ''; position: absolute; top: -55%; right: -18%;
        width: 240px; height: 240px;
        background: radial-gradient(circle, rgba(232,123,53,0.38), transparent 70%);
    }
</style>
@endpush

@section('content')
<div class="auth-stage flex items-center justify-center px-4 py-10 font-sans">
    <div class="auth-bg"></div>
    <div class="auth-overlay"></div>
    <div class="auth-noise"></div>

    {{-- Back to site — nav is hidden on this page, so keep a clear way out --}}
    <a href="{{ route('login') }}"
       class="absolute top-5 left-5 sm:top-7 sm:left-7 z-10 inline-flex items-center gap-1.5 text-xs font-medium uppercase tracking-[0.15em] text-white/60 hover:text-white transition group">
        <svg class="h-3.5 w-3.5 transition group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to sign in
    </a>

    <div class="auth-card relative w-full max-w-md bg-white rounded-3xl overflow-hidden shadow-[0_30px_80px_-20px_rgba(10,30,28,0.65)]">
        {{-- Header band --}}
        <div class="auth-head px-8 pt-9 pb-8 text-center">
            <a href="{{ route('home') }}" class="relative inline-block">
                <img src="{{ asset('images/xploresmithers_white.png') }}" alt="XploreSmithers" class="h-20 w-auto max-w-[300px] mx-auto object-contain">
            </a>
            <p class="relative mt-5 text-[11px] font-semibold uppercase tracking-[0.22em] text-accent-500">Almost there</p>
            <h1 class="relative mt-2 text-2xl font-bold text-white tracking-tight" style="text-wrap:balance">Choose a new password</h1>
            <p class="relative mt-2 text-sm text-white/65 leading-relaxed">Pick a strong password you don't use anywhere else.</p>
        </div>

        {{-- Body --}}
        <div class="px-7 sm:px-8 pt-7 pb-8">
            <form class="space-y-4" method="POST" action="{{ route('password.store') }}" x-data="{ showPassword: false }">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-600 mb-1.5">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required readonly
                           class="block w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-600 transition focus:outline-none @error('email') border-red-400 @enderror"
                           value="{{ old('email', $email) }}">
                    @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-600 mb-1.5">New password</label>
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required autofocus
                               class="block w-full px-4 py-3 pr-11 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:bg-white focus:ring-4 focus:ring-forest-600/10 focus:border-forest-600 @error('password') border-red-400 @enderror"
                               placeholder="At least 8 characters">
                        <button type="button" @click="showPassword = !showPassword" aria-label="Toggle password visibility"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-forest-700 transition">
                            <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908A3 3 0 1115 12m-7.071 4.929L4 21m16-16l-4.929 4.929M9.88 9.88a3 3 0 014.243 4.243M3 3l18 18"/></svg>
                        </button>
                    </div>
                    @error('password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-600 mb-1.5">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required
                           class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 transition focus:outline-none focus:bg-white focus:ring-4 focus:ring-forest-600/10 focus:border-forest-600"
                           placeholder="Re-enter your new password">
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-3.5 px-5 bg-accent-500 hover:bg-accent-600 active:scale-[0.99] text-white text-sm font-semibold rounded-xl shadow-[0_8px_20px_rgba(232,123,53,0.3)] transition focus:outline-none focus:ring-4 focus:ring-accent-500/30">
                    Reset password
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Remembered your password?
                <a href="{{ route('login') }}" class="font-semibold text-forest-700 hover:text-accent-600 transition">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
