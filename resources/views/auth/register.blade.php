@extends('layouts.public')

@section('title', 'Create Account')

@push('styles')
<style>
    @keyframes drift {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(40px, -30px) scale(1.08); }
    }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }
    .orb { animation: drift 14s ease-in-out infinite; }
    .live-dot { animation: pulse-dot 2s ease-in-out infinite; }
    .topo-bg {
        background-image:
            radial-gradient(circle at 20% 30%, rgba(255,255,255,0.04) 0%, transparent 40%),
            radial-gradient(circle at 80% 70%, rgba(255,255,255,0.03) 0%, transparent 40%);
    }
</style>
@endpush

@section('content')
<div class="lg:h-screen lg:overflow-hidden min-h-screen w-full bg-[#fafaf7] grid grid-cols-1 lg:grid-cols-[1.1fr_1fr] font-sans">
    {{-- LEFT: brand / editorial panel --}}
    <div class="relative hidden lg:flex flex-col bg-forest-800 text-white overflow-hidden topo-bg">
        <div class="orb absolute -top-32 -left-32 w-[520px] h-[520px] rounded-full bg-gradient-to-br from-primary-500 via-emerald-400/40 to-transparent blur-3xl opacity-60"></div>
        <div class="orb absolute bottom-0 right-0 w-[420px] h-[420px] rounded-full bg-gradient-to-tr from-accent-500/30 via-primary-500/20 to-transparent blur-3xl opacity-50" style="animation-delay: -7s;"></div>

        <svg class="absolute inset-0 w-full h-full opacity-[0.08]" viewBox="0 0 800 800" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M0,200 Q200,140 400,180 T800,160"/>
            <path d="M0,260 Q200,200 400,240 T800,220"/>
            <path d="M0,320 Q200,260 400,300 T800,280"/>
            <path d="M0,400 Q200,340 400,380 T800,360"/>
            <path d="M0,480 Q200,420 400,460 T800,440"/>
            <path d="M0,560 Q200,500 400,540 T800,520"/>
            <path d="M0,640 Q200,580 400,620 T800,600"/>
        </svg>

        <div class="relative z-10 flex flex-col h-full p-8 xl:p-12">
            <a href="{{ route('home') }}" class="flex items-center gap-4">
                <div class="bg-white rounded-lg p-2 shadow-sm">
                    <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="XploreSmithers" class="h-12 w-12 object-contain"/>
                </div>
                <div class="leading-tight">
                    <p class="text-lg font-semibold text-white tracking-tight">XploreSmithers</p>
                    <p class="mt-0.5 text-[11px] text-white/65 font-mono uppercase tracking-[0.2em]">Discover Smithers BC</p>
                </div>
            </a>

            <div class="flex-1 flex flex-col justify-center max-w-xl">
                <p class="text-[11px] font-mono uppercase tracking-[0.3em] text-primary-200/80 mb-6 flex items-center gap-3">
                    <span class="h-px w-8 bg-primary-200/50"></span>
                    Join us
                </p>
                <h1 class="text-4xl xl:text-5xl font-semibold leading-[1.05] tracking-tight">
                    Explore with purpose.<br/>
                    <span class="text-primary-200">Travel with respect.</span>
                </h1>
                <p class="mt-6 text-base text-white/65 leading-relaxed max-w-md">
                    Create an account to save trails, fishing lakes, and adventures across Smithers and beyond.
                </p>
            </div>
        </div>
    </div>

    {{-- RIGHT: form panel --}}
    <div class="relative flex items-center justify-center px-6 py-8 sm:px-12 bg-[#fafaf7] lg:overflow-y-auto">
        <div class="orb absolute top-20 -right-32 w-[380px] h-[380px] rounded-full bg-gradient-to-br from-primary-100 to-transparent blur-3xl opacity-70 pointer-events-none"></div>

        <a href="{{ route('home') }}" class="absolute top-6 right-6 lg:top-8 lg:right-10 inline-flex items-center gap-1.5 text-xs font-mono uppercase tracking-wider text-gray-500 hover:text-forest-700 transition group">
            <svg class="h-3.5 w-3.5 transition group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to site
        </a>

        <div class="relative w-full max-w-md">
            <div class="lg:hidden mb-10 flex items-center gap-3">
                <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="XploreSmithers" class="h-12 w-12 object-contain"/>
                <div>
                    <p class="text-lg font-semibold text-gray-900 tracking-tight">XploreSmithers</p>
                    <p class="mt-0.5 text-[11px] text-gray-500 font-mono uppercase tracking-[0.2em]">Discover Smithers BC</p>
                </div>
            </div>

            <div class="flex items-center gap-3 mb-4">
                <span class="h-px w-8 bg-forest-300"></span>
                <p class="text-[11px] font-mono uppercase tracking-[0.25em] text-forest-600">Create account</p>
            </div>
            <h2 class="text-4xl font-semibold text-forest-800 tracking-tight leading-tight">
                Get started.
            </h2>
            <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                It only takes a moment.
            </p>

            {{-- Google sign-up --}}
            <a href="{{ route('google.redirect') }}"
               class="mt-8 w-full inline-flex items-center justify-center gap-3 py-3 px-5 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 shadow-[0_1px_2px_rgba(0,0,0,0.04)] hover:border-forest-400 hover:text-forest-700 transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.99.66-2.26 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/>
                </svg>
                Continue with Google
            </a>

            <div class="my-6 flex items-center gap-4">
                <span class="h-px flex-1 bg-gray-200"></span>
                <span class="text-[11px] font-mono uppercase tracking-wider text-gray-400">or with email</span>
                <span class="h-px flex-1 bg-gray-200"></span>
            </div>

            <form class="space-y-5" method="POST" action="{{ route('register.post') }}"
                  x-data="{ showPassword: false }">
                @csrf

                <div>
                    <label for="name" class="block text-[11px] font-mono uppercase tracking-wider text-gray-500 mb-2">Full name</label>
                    <input id="name" name="name" type="text" autocomplete="name" required
                           class="block w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition focus:outline-none focus:ring-4 focus:ring-forest-600/10 focus:border-forest-600 @error('name') border-red-400 @enderror"
                           placeholder="Jane Hiker" value="{{ old('name') }}">
                    @error('name')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-[11px] font-mono uppercase tracking-wider text-gray-500 mb-2">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="block w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition focus:outline-none focus:ring-4 focus:ring-forest-600/10 focus:border-forest-600 @error('email') border-red-400 @enderror"
                           placeholder="name@example.com" value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-[11px] font-mono uppercase tracking-wider text-gray-500 mb-2">Password</label>
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required
                               class="block w-full px-4 py-3 pr-11 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition focus:outline-none focus:ring-4 focus:ring-forest-600/10 focus:border-forest-600 @error('password') border-red-400 @enderror"
                               placeholder="At least 8 characters">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-forest-700 transition">
                            <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908A3 3 0 1115 12m-7.071 4.929L4 21m16-16l-4.929 4.929M9.88 9.88a3 3 0 014.243 4.243M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-[11px] font-mono uppercase tracking-wider text-gray-500 mb-2">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required
                           class="block w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition focus:outline-none focus:ring-4 focus:ring-forest-600/10 focus:border-forest-600"
                           placeholder="Re-enter your password">
                </div>

                <button type="submit"
                        class="group relative w-full flex items-center justify-between py-3.5 px-5 bg-forest-800 hover:bg-forest-900 text-white text-sm font-medium rounded-lg shadow-[0_4px_14px_rgba(44,95,93,0.25)] hover:shadow-[0_6px_20px_rgba(44,95,93,0.35)] transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-forest-600/20">
                    <span class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="live-dot absolute inline-flex h-full w-full rounded-full bg-primary-300"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-200"></span>
                        </span>
                        Create account
                    </span>
                    <span class="flex items-center justify-center h-7 w-7 rounded-md bg-white/10 group-hover:bg-white/15 transition">
                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-gray-500">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-forest-700 hover:text-accent-600 transition">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection
