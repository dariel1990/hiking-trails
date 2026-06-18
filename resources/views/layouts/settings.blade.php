@extends('layouts.public')

@section('content')
<div class="bg-white min-h-screen">
    <div class="max-w-6xl mx-auto px-6 sm:px-10 py-12">
        <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] items-start">
            <div class="pb-8 md:pb-0 md:pr-8 md:border-r md:border-gray-100 md:sticky md:top-24">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Settings</h1>

                <nav class="space-y-1">
                    @php
                        $settingsNav = [
                            ['route' => 'settings.profile', 'label' => 'Profile', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                            ['route' => 'settings.account', 'label' => 'Account', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z'],
                            ['route' => 'settings.subscription', 'label' => 'Subscription', 'icon' => 'M9 12h6m-6 4h6M9 8h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z'],
                        ];
                    @endphp

                    @foreach($settingsNav as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs($item['route']) ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="md:pl-10 pt-8 md:pt-0">
                @yield('settings-content')
            </div>
        </div>
    </div>
</div>
@endsection
