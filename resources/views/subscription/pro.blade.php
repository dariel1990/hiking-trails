@extends('layouts.public')

@section('title', 'XploreSmithers Pro')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-forest-900 via-forest-800 to-[#1a2e2e] text-white">
    <div class="max-w-3xl mx-auto px-4 py-16 sm:py-20">

        {{-- Header --}}
        <div class="text-center">
            <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="XploreSmithers" class="h-16 w-16 object-contain mx-auto mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">
                Get offline maps and more with <span class="text-primary-200">XploreSmithers Pro</span>
            </h1>
            <p class="mt-3 text-white/60">90% of backcountry trails have spotty service.</p>
        </div>

        @if($isPro)
            {{-- Already subscribed --}}
            <div class="mt-12 bg-white/5 ring-1 ring-white/10 rounded-2xl p-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/20 ring-1 ring-primary-300/30 text-primary-100 text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    You're Pro
                </div>
                <p class="text-white/70 mb-6">Your XploreSmithers Pro subscription is active across the website and the app.</p>
                <a href="{{ route('pro.portal') }}" class="inline-flex items-center gap-2 bg-white text-forest-800 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition">
                    Manage subscription
                </a>
            </div>
        @else
            {{-- Features --}}
            <div class="mt-12 grid sm:grid-cols-2 gap-4">
                @foreach([
                    ['Offline maps for any trail', 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'Mobile app only'],
                    ['Unique points of interest', 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z', null],
                    ['Pro video content', 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z', null],
                    ['GPX file download', 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4', null],
                ] as $feature)
                    <div class="flex items-center gap-3 bg-white/5 ring-1 ring-white/10 rounded-xl px-4 py-4">
                        <span class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feature[1] }}"/></svg>
                        </span>
                        <span class="font-medium">
                            {{ $feature[0] }}
                            @if($feature[2])
                                <span class="ml-1 inline-block text-[10px] font-semibold text-white/60 bg-white/10 ring-1 ring-white/15 rounded-full px-2 py-0.5 align-middle whitespace-nowrap">{{ $feature[2] }}</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>

            @guest
                {{-- Guests: sign in first --}}
                <div class="mt-10 bg-white/5 ring-1 ring-white/10 rounded-2xl p-6 text-center">
                    <h2 class="text-lg font-semibold">Sign in to subscribe</h2>
                    <p class="mt-1 text-sm text-white/60">Your subscription is tied to your account, so it works across the website and the app.</p>
                    <a href="{{ route('login') }}" class="mt-5 inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                        Sign in or create account
                    </a>
                </div>
            @else
                {{-- Plan selection --}}
                <div x-data="{ plan: 'annual' }" class="mt-10">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <label :class="plan === 'monthly' ? 'ring-2 ring-primary-300 bg-white/10' : 'ring-1 ring-white/10 bg-white/5'"
                               class="cursor-pointer rounded-2xl p-6 transition">
                            <input type="radio" name="plan" value="monthly" x-model="plan" class="sr-only">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">Monthly</span>
                                <span class="text-2xl font-bold">${{ $priceMonthly }}<span class="text-sm font-normal text-white/50">/mo</span></span>
                            </div>
                        </label>
                        <label :class="plan === 'annual' ? 'ring-2 ring-primary-300 bg-white/10' : 'ring-1 ring-white/10 bg-white/5'"
                               class="relative cursor-pointer rounded-2xl p-6 transition">
                            <input type="radio" name="plan" value="annual" x-model="plan" class="sr-only">
                            <span class="absolute -top-2 right-4 bg-accent-500 text-white text-[11px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full">Best value</span>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">Annual</span>
                                <span class="text-2xl font-bold">${{ $priceAnnual }}<span class="text-sm font-normal text-white/50">/yr</span></span>
                            </div>
                        </label>
                    </div>

                    {{-- Trial timeline --}}
                    <div class="mt-8 bg-white/5 ring-1 ring-white/10 rounded-2xl p-6">
                        <h3 class="font-semibold mb-4">How your trial works</h3>
                        <ol class="space-y-4 text-sm">
                            <li class="flex gap-3"><span class="text-primary-200 font-semibold w-16 flex-shrink-0">Today</span><span class="text-white/70">Start your free {{ $trialDays }}-day trial</span></li>
                            <li class="flex gap-3"><span class="text-primary-200 font-semibold w-16 flex-shrink-0">Day {{ max(1, $trialDays - 2) }}</span><span class="text-white/70">Get a trial reminder</span></li>
                            <li class="flex gap-3"><span class="text-primary-200 font-semibold w-16 flex-shrink-0">Day {{ $trialDays }}</span><span class="text-white/70">Your subscription begins</span></li>
                        </ol>
                    </div>

                    <form method="POST" action="{{ route('pro.checkout') }}" class="mt-8">
                        @csrf
                        <input type="hidden" name="plan" :value="plan">
                        <button type="submit" @if(!$stripeEnabled) disabled @endif
                                class="w-full bg-accent-500 hover:bg-accent-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-lg px-6 py-4 rounded-xl transition shadow-lg">
                            @if($stripeEnabled)
                                Start {{ $trialDays }}-day free trial
                            @else
                                Payments coming soon
                            @endif
                        </button>
                        @unless($stripeEnabled)
                            <p class="mt-3 text-center text-xs text-white/40">Checkout isn't live yet — payment setup is in progress.</p>
                        @endunless
                        <p class="mt-3 text-center text-xs text-white/40">Cancel anytime. Apple Pay, Google Pay & cards accepted.</p>
                    </form>
                </div>
            @endguest
        @endif
    </div>
</div>
@endsection
