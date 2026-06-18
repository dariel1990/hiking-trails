@extends('layouts.settings')

@section('title', 'Subscription')

@section('settings-content')
<div class="flex items-center justify-between mb-8">
    <h2 class="text-xl font-bold text-gray-900">Subscription</h2>
    @if($isPro && $isGooglePlay)
        <span class="text-xs font-medium text-gray-500">Google Play Store</span>
    @endif
</div>

@if($isPro && $subscription)
    <div>
        <p class="text-xs font-medium text-gray-500 mb-1">Current plan</p>
        @if($subscription->expires_at)
            <p class="text-sm text-gray-500 mb-4">Renews {{ $subscription->expires_at->format('M j, Y') }}</p>
        @endif

        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-forest-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/></svg>
            <span class="text-lg font-bold text-gray-900">XploreSmithers Pro</span>
        </div>

        @if($priceLabel)
            <p class="text-sm text-gray-600 mb-6">CA${{ $priceLabel }}</p>
        @endif

        <ul class="space-y-2 text-sm text-gray-700 mb-10">
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Offline maps for any trail
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Unique points of interest
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Pro video content
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                GPX file download
            </li>
        </ul>

        @if($isGooglePlay)
            <div class="border-t border-gray-100 pt-8">
                <p class="text-sm text-gray-500">Manage or cancel this subscription from the Google Play Store on your device.</p>
            </div>
        @else
            <div class="border-t border-gray-100 pt-8 flex items-center gap-3">
                <a href="{{ route('pro.portal') }}" class="rounded-full bg-forest-600 hover:bg-forest-700 text-white font-semibold py-2.5 px-7 transition-colors">
                    Manage subscription
                </a>
            </div>

            <div class="border-t border-gray-100 mt-8 pt-6">
                <a href="{{ route('pro.portal') }}" class="text-sm font-semibold text-red-600 hover:text-red-700">
                    Cancel subscription
                </a>
            </div>
        @endif
    </div>
@else
    <div class="border-t border-gray-100 pt-10 text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/></svg>
        <p class="text-gray-700 font-medium mb-1">You're not subscribed yet</p>
        <p class="text-sm text-gray-500 mb-6">Upgrade to XploreSmithers Pro for offline maps, points of interest, Pro videos, and GPX downloads.</p>
        <a href="{{ route('pro.show') }}" class="inline-flex rounded-full bg-forest-600 hover:bg-forest-700 text-white font-semibold py-2.5 px-7 transition-colors">
            See plans
        </a>
    </div>
@endif
@endsection
