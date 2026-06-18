@extends('layouts.public')

@section('title', 'XploreSmithers Pro')

@push('styles')
<style>
    .xs-pro-stage { position: relative; overflow: hidden; }
    .xs-pro-bg {
        position: absolute; inset: 0; pointer-events: none;
        background: url("{{ asset('images/login-background.jpg') }}") center 30% / cover no-repeat;
        filter: blur(2px) brightness(0.55);
        transform: scale(1.04);
    }
    .xs-pro-scrim {
        position: absolute; inset: 0; pointer-events: none;
        background: linear-gradient(180deg, rgba(10,20,19,.55) 0%, rgba(13,28,27,.88) 55%, #0d1c1b 100%);
    }

    @keyframes xsProRise { from { transform: translateY(18px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .xs-pro-rise { animation: xsProRise .6s cubic-bezier(.16,1,.3,1) both; }

    @media (prefers-reduced-motion: reduce) {
        .xs-pro-rise { animation: none; }
    }
</style>
@endpush

@section('content')
<div class="bg-[#0d1c1b] text-white">

    {{-- Hero --}}
    <div class="xs-pro-stage px-4 pt-16 pb-14 sm:pt-20 sm:pb-16 text-center">
        <div class="xs-pro-bg"></div>
        <div class="xs-pro-scrim"></div>

        <div class="relative max-w-2xl mx-auto xs-pro-rise">
            <img src="{{ asset('images/xploresmithers_white.png') }}" alt="XploreSmithers" class="h-14 w-auto mx-auto mb-7 object-contain">
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-none">
                <span class="text-white">Free or </span><span class="text-accent-500">Pro?</span>
            </h1>
            <p class="mt-4 text-sm font-semibold uppercase tracking-[0.18em] text-primary-300">
                More trails. More adventures. More you.
            </p>
        </div>
    </div>

    @if($isPro)
        {{-- Already subscribed --}}
        <div class="max-w-md mx-auto px-4 pb-20 -mt-2">
            <div class="bg-white/5 ring-1 ring-white/10 rounded-2xl p-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/20 ring-1 ring-primary-300/30 text-primary-100 text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    You're Pro
                </div>
                <p class="text-white/70 mb-6">Your XploreSmithers Pro subscription is active across the website and the app.</p>
                <a href="{{ route('pro.portal') }}" class="inline-flex items-center gap-2 bg-white text-forest-800 font-semibold px-6 py-3 rounded-full hover:bg-gray-100 transition">
                    Manage subscription
                </a>
            </div>
        </div>
    @else
        <div class="max-w-3xl mx-auto px-4 pb-20">

            {{-- Comparison table --}}
            @php($features = \App\Services\ProFeatureCatalog::all())

            <div class="rounded-2xl ring-1 ring-white/10 overflow-hidden xs-pro-rise">
                {{-- Desktop / tablet table --}}
                <div class="hidden sm:grid grid-cols-[1fr_160px_200px] bg-white/[0.03]">
                    <div class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-white/50">Features</div>
                    <div class="px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider text-white/50 border-l border-white/10">Free</div>
                    <div class="px-4 py-4 text-center text-xs font-semibold uppercase tracking-wider text-white border-l border-white/10 bg-forest-600/40 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-accent-400" fill="currentColor" viewBox="0 0 20 20"><path d="M5 16L3 6l5 3 2-5 2 5 5-3-2 10H5zm0 2h10v1a1 1 0 01-1 1H6a1 1 0 01-1-1v-1z"/></svg>
                        Pro
                    </div>

                    @foreach($features as $feature)
                        <div class="px-6 py-4 border-t border-white/10 flex items-center gap-3">
                            <span class="flex-shrink-0 w-9 h-9 rounded-full bg-primary-500/15 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feature['icon'] }}"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm">{{ $feature['label'] }}</p>
                                <p class="text-xs text-white/45">{{ $feature['sub'] }}</p>
                            </div>
                        </div>
                        <div class="px-3 border-t border-l border-white/10 flex items-center justify-center gap-1.5">
                            @if($feature['free']['available'])
                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-4 h-4 text-white/30 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                            <span class="text-xs text-white/55">{{ $feature['free']['text'] }}</span>
                        </div>
                        <div class="px-3 border-t border-l border-white/10 bg-forest-600/15 flex flex-col items-center justify-center gap-0.5 py-3">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-xs font-medium text-white/85">{{ $feature['pro']['text'] }}</span>
                            </span>
                            @if($feature['pro']['badge'])
                                <span class="text-[10px] font-semibold text-white/45">{{ $feature['pro']['badge'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Mobile fallback: stacked rows with inline Free/Pro labels --}}
                <div class="sm:hidden divide-y divide-white/10 bg-white/[0.03]">
                    @foreach($features as $feature)
                        <div class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex-shrink-0 w-9 h-9 rounded-full bg-primary-500/15 flex items-center justify-center">
                                    <svg class="w-4.5 h-4.5 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feature['icon'] }}"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm">{{ $feature['label'] }}</p>
                                    <p class="text-xs text-white/45">{{ $feature['sub'] }}</p>
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 pl-12">
                                <div class="flex items-center gap-1.5">
                                    @if($feature['free']['available'])
                                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-white/30 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                    <span class="text-xs text-white/55">{{ $feature['free']['text'] }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-xs font-medium text-white/85">{{ $feature['pro']['text'] }}</span>
                                </div>
                            </div>
                            @if($feature['pro']['badge'])
                                <p class="mt-1 pl-12 text-[10px] font-semibold text-white/40">{{ $feature['pro']['badge'] }} on Pro</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @guest
                {{-- Guests: sign in first --}}
                <div class="mt-8 bg-white/5 ring-1 ring-white/10 rounded-2xl p-6 text-center xs-pro-rise">
                    <h2 class="text-lg font-semibold">Sign in to subscribe</h2>
                    <p class="mt-1 text-sm text-white/60">Your subscription is tied to your account, so it works across the website and the app.</p>
                    <a href="{{ route('login') }}" class="mt-5 inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-semibold px-6 py-3 rounded-full transition">
                        Sign in or create account
                    </a>
                </div>
            @else
                <div x-data="{ plan: 'annual' }" class="mt-8 xs-pro-rise">
                    @include('subscription._pro-pricing')
                </div>
            @endguest
        </div>
    @endif
</div>
@endsection
