@extends('layouts.public')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Page header --}}
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Account</p>
            <h1 class="text-2xl font-bold text-gray-900" style="font-family: 'Inter', sans-serif;">Settings</h1>
        </div>

        <div class="flex flex-col gap-6 md:flex-row md:items-start">

            {{-- Sidebar nav --}}
            <aside class="md:w-56 shrink-0">
                <nav class="space-y-0.5">
                    @php
                        $settingsNav = [
                            [
                                'route' => 'settings.profile',
                                'label' => 'Profile',
                                'desc'  => 'Name, photo, bio',
                                'icon'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                            ],
                            [
                                'route' => 'settings.account',
                                'label' => 'Account',
                                'desc'  => 'Email, password',
                                'icon'  => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                            ],
                            [
                                'route' => 'settings.subscription',
                                'label' => 'Subscription',
                                'desc'  => 'Plan & billing',
                                'icon'  => 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z',
                            ],
                        ];
                    @endphp

                    @foreach($settingsNav as $item)
                        @php $isActive = request()->routeIs($item['route']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm transition-all group
                                  {{ $isActive ? 'bg-white border border-gray-200 shadow-sm text-gray-900' : 'text-gray-500 hover:bg-white/70 hover:text-gray-900' }}">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                                         {{ $isActive ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200' }} transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold leading-none {{ $isActive ? 'text-gray-900' : '' }}">{{ $item['label'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $item['desc'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </nav>
            </aside>

            {{-- Content panel --}}
            <main class="flex-1 min-w-0">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                    @yield('settings-content')
                </div>
            </main>

        </div>
    </div>
</div>
@endsection
