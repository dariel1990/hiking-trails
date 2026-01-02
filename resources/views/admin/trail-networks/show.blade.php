@extends('layouts.admin')

@section('title', 'Trail Network Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.trail-networks.index') }}" class="text-gray-600 hover:text-gray-900 mb-4 inline-block">
            ← Back to Trail Networks
        </a>
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ $trailNetwork->network_name }}</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.trail-networks.edit', $trailNetwork) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Edit Network
                </a>
            </div>
        </div>
    </div>

    <!-- Network Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info Card -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Network Information</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Network Name</label>
                    <p class="text-gray-900">{{ $trailNetwork->network_name }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Slug</label>
                    <p class="text-gray-900 font-mono text-sm">{{ $trailNetwork->slug }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Type</label>
                    <p>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                            {{ $trailNetwork->type === 'nordic_skiing' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $trailNetwork->type === 'downhill_skiing' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $trailNetwork->type === 'hiking' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $trailNetwork->type === 'mountain_biking' ? 'bg-orange-100 text-orange-800' : '' }}">
                            {{ ucwords(str_replace('_', ' ', $trailNetwork->type)) }}
                        </span>
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Description</label>
                    <p class="text-gray-900">{{ $trailNetwork->description ?? 'No description provided.' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Latitude</label>
                        <p class="text-gray-900 font-mono text-sm">{{ $trailNetwork->latitude }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Longitude</label>
                        <p class="text-gray-900 font-mono text-sm">{{ $trailNetwork->longitude }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Address</label>
                    <p class="text-gray-900">{{ $trailNetwork->address ?? 'Not specified' }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Website</label>
                    <p class="text-gray-900">
                        @if($trailNetwork->website_url)
                            <a href="{{ $trailNetwork->website_url }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $trailNetwork->website_url }}
                            </a>
                        @else
                            Not specified
                        @endif
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">Always Visible on Map</label>
                    <p>
                        @if($trailNetwork->is_always_visible)
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Yes
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                No
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="space-y-6">
            <!-- Trail Count -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Total Trails</span>
                        <span class="text-2xl font-bold text-green-600">{{ $trailNetwork->trails->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Total Distance</span>
                        <span class="text-lg font-semibold text-gray-900">
                            {{ number_format($trailNetwork->trails->sum('distance_km'), 1) }} km
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Avg. Difficulty</span>
                        <span class="text-lg font-semibold text-gray-900">
                            {{ $trailNetwork->trails->avg('difficulty_level') ? number_format($trailNetwork->trails->avg('difficulty_level'), 1) : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('trail-networks.show', $trailNetwork->slug) }}" 
                        target="_blank"
                        class="block w-full text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        🗺️ View Network Map
                    </a>
                    <a href="{{ route('admin.trails.create') }}?network={{ $trailNetwork->id }}" 
                       class="block w-full text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Add Trail to Network
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Trails List -->
    <div class="mt-6 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Trails in this Network</h2>
        
        @if($trailNetwork->trails->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($trailNetwork->trails as $trail)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $trail->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">Level {{ $trail->difficulty_level }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $trail->distance_km }} km
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ucwords(str_replace('-', ' ', $trail->trail_type)) }}
                                </td>
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
            <div class="text-center py-8 text-gray-500">
                <p>No trails in this network yet.</p>
                <a href="{{ route('admin.trails.create') }}?network={{ $trailNetwork->id }}" 
                   class="text-green-600 hover:underline mt-2 inline-block">
                    Add the first trail
                </a>
            </div>
        @endif
    </div>
</div>
@endsection