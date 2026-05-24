@extends('layouts.public')

@section('title', 'Admin Login')

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
    @keyframes marquee {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }
    .orb { animation: drift 14s ease-in-out infinite; }
    .live-dot { animation: pulse-dot 2s ease-in-out infinite; }
    .marquee-track { animation: marquee 40s linear infinite; }
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
        {{-- Animated gradient orb --}}
        <div class="orb absolute -top-32 -left-32 w-[520px] h-[520px] rounded-full bg-gradient-to-br from-primary-500 via-emerald-400/40 to-transparent blur-3xl opacity-60"></div>
        <div class="orb absolute bottom-0 right-0 w-[420px] h-[420px] rounded-full bg-gradient-to-tr from-accent-500/30 via-primary-500/20 to-transparent blur-3xl opacity-50" style="animation-delay: -7s;"></div>

        {{-- Topographic SVG lines --}}
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
            {{-- Top bar --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-white rounded-lg p-2 shadow-sm">
                        <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="XploreSmithers" class="h-12 w-12 object-contain"/>
                    </div>
                    <div class="leading-tight">
                        <p class="text-lg font-semibold text-white tracking-tight">XploreSmithers</p>
                        <p class="mt-0.5 text-[11px] text-white/65 font-mono uppercase tracking-[0.2em]">Admin Portal</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 ring-1 ring-white/10 backdrop-blur-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="live-dot absolute inline-flex h-full w-full rounded-full bg-emerald-300"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    <span class="text-[11px] font-mono uppercase tracking-wider text-white/80">System operational</span>
                </div>
            </div>

            {{-- Hero --}}
            <div class="flex-1 flex flex-col justify-center max-w-xl">
                <p class="text-[11px] font-mono uppercase tracking-[0.3em] text-primary-200/80 mb-6 flex items-center gap-3">
                    <span class="h-px w-8 bg-primary-200/50"></span>
                    01 — Secure sign in
                </p>
                <h1 class="text-4xl xl:text-5xl font-semibold leading-[1.05] tracking-tight">
                    The control room<br/>
                    <span class="text-primary-200">for every trail.</span>
                </h1>
                <p class="mt-6 text-base text-white/65 leading-relaxed max-w-md">
                    Manage routes, fishing lakes, and facilities across the region from a single, secure workspace.
                </p>

            </div>

        </div>
    </div>

    {{-- RIGHT: form panel --}}
    <div class="relative flex items-center justify-center px-6 py-8 sm:px-12 bg-[#fafaf7] lg:overflow-y-auto">
        {{-- Soft background orb --}}
        <div class="orb absolute top-20 -right-32 w-[380px] h-[380px] rounded-full bg-gradient-to-br from-primary-100 to-transparent blur-3xl opacity-70 pointer-events-none"></div>

        {{-- Back link --}}
        <a href="{{ route('home') }}" class="absolute top-6 right-6 lg:top-8 lg:right-10 inline-flex items-center gap-1.5 text-xs font-mono uppercase tracking-wider text-gray-500 hover:text-forest-700 transition group">
            <svg class="h-3.5 w-3.5 transition group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to site
        </a>

        <div class="relative w-full max-w-md">
            {{-- Mobile brand --}}
            <div class="lg:hidden mb-12 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="XploreSmithers" class="h-12 w-12 object-contain"/>
                    <div>
                        <p class="text-lg font-semibold text-gray-900 tracking-tight">XploreSmithers</p>
                        <p class="mt-0.5 text-[11px] text-gray-500 font-mono uppercase tracking-[0.2em]">Admin Portal</p>
                    </div>
                </div>
            </div>

            {{-- Heading --}}
            <div class="flex items-center gap-3 mb-4">
                <span class="h-px w-8 bg-forest-300"></span>
                <p class="text-[11px] font-mono uppercase tracking-[0.25em] text-forest-600">Sign in</p>
            </div>
            <h2 class="text-4xl font-semibold text-forest-800 tracking-tight leading-tight">
                Welcome back.
            </h2>
            <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                Enter your credentials to access the dashboard.
            </p>

            @if ($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
                <div class="mt-8 flex items-start gap-3 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <svg class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form class="mt-8 space-y-5" method="POST" action="{{ route('admin.login.post') }}"
                  x-data="{ showPassword: false }">
                @csrf

                <div>
                    <label for="email" class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-gray-500 mb-2">
                        <span>Email address</span>
                        <span class="text-gray-300">01</span>
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="block w-full px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition focus:outline-none focus:ring-4 focus:ring-forest-600/10 focus:border-forest-600 @error('email') border-red-400 @enderror"
                           placeholder="name@company.com" value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-gray-500 mb-2">
                        <span>Password</span>
                        <span class="text-gray-300">02</span>
                    </label>
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required
                               class="block w-full px-4 py-3 pr-11 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 shadow-[0_1px_2px_rgba(0,0,0,0.04)] transition focus:outline-none focus:ring-4 focus:ring-forest-600/10 focus:border-forest-600 @error('password') border-red-400 @enderror"
                               placeholder="••••••••••••">
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

                <div class="flex items-center justify-between pt-1">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember"
                               class="h-4 w-4 rounded border-gray-300 text-forest-600 focus:ring-forest-600/30">
                        Keep me signed in
                    </label>
                </div>

                <button type="submit"
                        class="group relative w-full flex items-center justify-between py-3.5 px-5 bg-forest-800 hover:bg-forest-900 text-white text-sm font-medium rounded-lg shadow-[0_4px_14px_rgba(44,95,93,0.25)] hover:shadow-[0_6px_20px_rgba(44,95,93,0.35)] transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-forest-600/20">
                    <span class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="live-dot absolute inline-flex h-full w-full rounded-full bg-primary-300"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-200"></span>
                        </span>
                        Sign in to dashboard
                    </span>
                    <span class="flex items-center justify-center h-7 w-7 rounded-md bg-white/10 group-hover:bg-white/15 transition">
                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
