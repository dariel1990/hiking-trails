@extends('layouts.admin')

@section('title', 'Trail Network Details')
@section('page-title', $trailNetwork->network_name)

@section('content')
@php
    $typeStyles = [
        'nordic_skiing'   => 'bg-blue-100 text-blue-800',
        'downhill_skiing' => 'bg-purple-100 text-purple-800',
        'hiking'          => 'bg-green-100 text-green-800',
        'mountain_biking' => 'bg-orange-100 text-orange-800',
    ];
    $seasonStyles = [
        'summer' => ['label' => '☀️ Summer', 'class' => 'bg-amber-100 text-amber-800'],
        'winter' => ['label' => '❄️ Winter', 'class' => 'bg-sky-100 text-sky-800'],
        'both'   => ['label' => 'Both seasons', 'class' => 'bg-gray-100 text-gray-700'],
    ];
    $season = $seasonStyles[$trailNetwork->season] ?? $seasonStyles['both'];
@endphp

<div class="px-4 lg:px-8 py-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 mb-6 border-b border-gray-200">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Admin</a>
                <span>/</span>
                <a href="{{ route('admin.trail-networks.index') }}" class="hover:text-gray-700">Trail Networks</a>
                <span>/</span>
                <span class="text-gray-700 font-medium truncate max-w-xs">{{ $trailNetwork->network_name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-600 border-2 border-white shadow-md flex items-center justify-center text-lg select-none">
                    {{ $trailNetwork->icon ?: '🏔️' }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $trailNetwork->network_name }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ ucwords(str_replace('_', ' ', $trailNetwork->type)) }} ·
                        {{ $trailNetwork->trails->count() }} trail(s)
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.trail-networks.index') }}"
               class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                Back
            </a>
            <a href="{{ route('trail-networks.show', $trailNetwork->slug) }}" target="_blank"
               class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                🗺️ View Public Page
            </a>
            <a href="{{ route('admin.trail-networks.edit', $trailNetwork) }}"
               class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                Edit Network
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- LEFT: Identity, Cover, Details --}}
        <div class="xl:col-span-7 space-y-6">

            {{-- Identity --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Identity</h2>
                        <p class="text-xs text-gray-500">Name, slug, type, and season</p>
                    </div>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Network Name</p>
                        <p class="text-gray-900">{{ $trailNetwork->network_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Slug</p>
                        <p class="text-gray-900 font-mono text-sm">{{ $trailNetwork->slug }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Network Type</p>
                        <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full {{ $typeStyles[$trailNetwork->type] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucwords(str_replace('_', ' ', $trailNetwork->type)) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Season</p>
                        <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full {{ $season['class'] }}">
                            {{ $season['label'] }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Map Marker Icon</p>
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-lg bg-amber-600 border-2 border-white shadow-md flex items-center justify-center text-lg select-none">
                                {{ $trailNetwork->icon ?: '🏔️' }}
                            </div>
                            <span class="text-sm text-gray-500">{{ $trailNetwork->icon ?: 'Default' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cover Image --}}
            @if($trailNetwork->image)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Cover Image</h2>
                            <p class="text-xs text-gray-500">Used on the public network page</p>
                        </div>
                    </div>
                    <img src="{{ asset('storage/'.$trailNetwork->image) }}" alt="{{ $trailNetwork->network_name }}" class="w-full h-72 object-cover">
                </div>
            @endif

            {{-- Details --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Details</h2>
                        <p class="text-xs text-gray-500">Description and visibility</p>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Description</p>
                        <p class="text-gray-900 whitespace-pre-line leading-relaxed">{{ $trailNetwork->description ?: 'No description provided.' }}</p>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="mt-0.5 h-4 w-4 rounded border flex items-center justify-center {{ $trailNetwork->is_always_visible ? 'bg-green-600 border-green-600' : 'bg-white border-gray-300' }}">
                            @if($trailNetwork->is_always_visible)
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-700">Always visible on main map</span>
                            <span class="block text-xs text-gray-500 mt-0.5">
                                @if($trailNetwork->is_always_visible)
                                    Marker shows even when there are no active trails.
                                @else
                                    Marker is only shown when this network has active trails.
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trails --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900">Trails in this Network</h2>
                            <p class="text-xs text-gray-500">{{ $trailNetwork->trails->count() }} total</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.trails.create') }}?network={{ $trailNetwork->id }}"
                       class="text-xs font-medium text-green-700 hover:text-green-800">
                        + Add Trail
                    </a>
                </div>

                @if($trailNetwork->trails->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($trailNetwork->trails as $trail)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $trail->name }}</div>
                                            @if($trail->trail_type)
                                                <div class="text-xs text-gray-500">{{ ucwords(str_replace('-', ' ', $trail->trail_type)) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Level {{ $trail->difficulty_level }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $trail->distance_km }} km</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $trail->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($trail->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.trails.edit', $trail) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-10 text-center text-sm text-gray-500">
                        <p>No trails in this network yet.</p>
                        <a href="{{ route('admin.trails.create') }}?network={{ $trailNetwork->id }}"
                           class="text-green-600 hover:underline mt-2 inline-block">
                            Add the first trail
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: Stats, Map, Contact --}}
        <div class="xl:col-span-5 space-y-6">

            {{-- Statistics --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Statistics</h2>
                        <p class="text-xs text-gray-500">Aggregated from member trails</p>
                    </div>
                </div>
                <div class="px-6 py-5 grid grid-cols-3 gap-3">
                    <div class="rounded-lg bg-green-50 border border-green-100 p-3 text-center">
                        <div class="text-2xl font-bold text-green-700">{{ $trailNetwork->trails->count() }}</div>
                        <div class="text-[11px] uppercase tracking-wide text-gray-500 mt-0.5">Trails</div>
                    </div>
                    <div class="rounded-lg bg-blue-50 border border-blue-100 p-3 text-center">
                        <div class="text-2xl font-bold text-blue-700">{{ number_format($trailNetwork->trails->sum('distance_km'), 1) }}</div>
                        <div class="text-[11px] uppercase tracking-wide text-gray-500 mt-0.5">Total km</div>
                    </div>
                    <div class="rounded-lg bg-purple-50 border border-purple-100 p-3 text-center">
                        <div class="text-2xl font-bold text-purple-700">
                            {{ $trailNetwork->trails->avg('difficulty_level') ? number_format($trailNetwork->trails->avg('difficulty_level'), 1) : '—' }}
                        </div>
                        <div class="text-[11px] uppercase tracking-wide text-gray-500 mt-0.5">Avg level</div>
                    </div>
                </div>
            </div>

            {{-- Map Location --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-sm font-semibold text-gray-900">Map Location</h2>
                        <p class="text-xs text-gray-500 truncate">{{ $trailNetwork->address ?? 'No address provided' }}</p>
                    </div>
                </div>
                @if($trailNetwork->latitude && $trailNetwork->longitude)
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $trailNetwork->latitude }},{{ $trailNetwork->longitude }}"
                       target="_blank" rel="noopener"
                       class="block relative group">
                        <img src="https://api.mapbox.com/styles/v1/mapbox/satellite-streets-v12/static/pin-l-mountain+10b981({{ $trailNetwork->longitude }},{{ $trailNetwork->latitude }})/{{ $trailNetwork->longitude }},{{ $trailNetwork->latitude }},13,0/640x320@2x?access_token={{ config('services.mapbox.access_token') }}"
                             alt="Map preview" class="w-full h-60 object-cover border-y border-gray-100">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-white text-gray-700 text-xs font-medium px-3 py-1.5 rounded-md shadow">
                                Open in Google Maps ↗
                            </span>
                        </div>
                    </a>
                @endif
                <div class="px-6 py-4 grid grid-cols-2 gap-4 bg-gray-50/50">
                    <div>
                        <p class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Latitude</p>
                        <p class="text-sm font-mono text-gray-900">{{ $trailNetwork->latitude ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Longitude</p>
                        <p class="text-sm font-mono text-gray-900">{{ $trailNetwork->longitude ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Contact & Web --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 015.656 5.656l-4 4a4 4 0 01-5.656-5.656m1.414-1.414a4 4 0 015.656-5.656l4 4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Contact &amp; Web</h2>
                        <p class="text-xs text-gray-500">Address and website</p>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Address</p>
                        <p class="text-gray-900">{{ $trailNetwork->address ?: 'Not specified' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Website</p>
                        @if($trailNetwork->website_url)
                            <a href="{{ $trailNetwork->website_url }}" target="_blank" rel="noopener"
                               class="text-blue-600 hover:underline text-sm break-all">
                                {{ $trailNetwork->website_url }}
                            </a>
                        @else
                            <p class="text-sm text-gray-500">Not specified</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
