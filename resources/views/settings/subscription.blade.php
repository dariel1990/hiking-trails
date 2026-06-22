@extends('layouts.settings')

@section('title', 'Subscription')

@section('settings-content')
{{-- Section header --}}
<div class="border-b border-gray-100 px-7 py-5 flex items-center justify-between">
    <div>
        <h2 class="text-base font-bold text-gray-900" style="font-family: 'Inter', sans-serif;">Subscription</h2>
        <p class="text-sm text-gray-400 mt-0.5">Manage your XploreSmithers Pro plan.</p>
    </div>
    @if($isPro)
        <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-xs font-bold text-emerald-700">
            <span class="relative flex h-1.5 w-1.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            </span>
            Pro active
        </span>
    @endif
</div>

<div class="px-7 py-8">

@if($isPro && $subscription)

    {{-- Current plan card --}}
    <div class="rounded-2xl border border-gray-200 bg-gray-50 overflow-hidden mb-6">
        <div class="h-1.5 bg-gradient-to-r from-forest-600 to-emerald-400"></div>
        <div class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-forest-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/>
                        </svg>
                        <span class="font-bold text-gray-900">XploreSmithers Pro</span>
                    </div>
                    @if($priceLabel)
                        <p class="text-sm text-gray-500">CA${{ $priceLabel }}</p>
                    @endif
                </div>
                <span class="shrink-0 rounded bg-forest-600/10 px-2.5 py-1 text-xs font-semibold text-forest-700">Active</span>
            </div>

            @if($subscription->expires_at)
                <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Renews {{ $subscription->expires_at->format('M j, Y') }}
                    @if($isGooglePlay)
                        via Google Play
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Features list --}}
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">Included in your plan</p>
        <ul class="space-y-3">
            @foreach([
                ['icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7', 'label' => 'Offline maps', 'sub' => 'Download any trail for offline use'],
                ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Points of interest', 'sub' => 'Curated spots across the map'],
                ['icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M3 8h12a2 2 0 012 2v4a2 2 0 01-2 2H3a2 2 0 01-2-2v-4a2 2 0 012-2z', 'label' => 'Pro video content', 'sub' => 'In-depth trail guides'],
                ['icon' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'GPX downloads', 'sub' => 'Export routes to your GPS device'],
            ] as $feature)
                <li class="flex items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $feature['icon'] }}"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $feature['label'] }}</p>
                        <p class="text-xs text-gray-400">{{ $feature['sub'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Manage / cancel --}}
    @if($isGooglePlay)
        <div class="rounded-xl bg-blue-50 border border-blue-100 px-5 py-4 text-sm text-blue-700">
            <p class="font-semibold mb-1">Managed via Google Play</p>
            <p class="text-xs text-blue-600">Open the Google Play Store on your device to manage or cancel this subscription.</p>
        </div>
    @else
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('pro.portal') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-gray-700 active:scale-95">
                Manage subscription
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            <a href="{{ route('pro.portal') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-medium text-red-500 transition-all hover:bg-red-50 hover:border-red-200 active:scale-95">
                Cancel subscription
            </a>
        </div>
    @endif

@else

    {{-- Not subscribed --}}
    <div class="text-center py-6">
        <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-forest-600 to-emerald-500 text-white shadow-lg shadow-forest-600/20 mb-5">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Upgrade to Pro</h3>
        <p class="text-sm text-gray-500 max-w-xs mx-auto leading-relaxed mb-7">Get offline maps, curated points of interest, Pro video guides, and GPX downloads.</p>

        {{-- Feature pills --}}
        <div class="flex flex-wrap justify-center gap-2 mb-8">
            @foreach(['Offline maps', 'Points of interest', 'Pro videos', 'GPX downloads'] as $feat)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ $feat }}
                </span>
            @endforeach
        </div>

        <button type="button" onclick="window.xsShowProModal('default')"
                class="inline-flex items-center gap-2 rounded-full bg-accent-500 hover:bg-accent-600 text-white font-bold px-8 py-3.5 text-base shadow-lg shadow-accent-500/25 transition-all hover:shadow-xl hover:shadow-accent-500/30 active:scale-95">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/>
            </svg>
            See plans
        </button>

        <p class="mt-4 text-xs text-gray-400">
            Try free for {{ config('services.stripe.trial_days', 7) }} days · Cancel anytime
        </p>
    </div>

@endif

</div>
@endsection
