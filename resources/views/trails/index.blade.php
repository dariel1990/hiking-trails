@extends('layouts.public')

@section('title', 'All Trails')

@section('content')
<div class="bg-white">
    <!-- Hero Section -->
    <div class="relative bg-gray-900">
        <div class="absolute inset-0 bg-gradient-to-r from-green-800 to-blue-800 opacity-75"></div>
        <div class="relative max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                Discover Amazing Trails
            </h1>
            <p class="mt-6 text-xl text-gray-300 max-w-3xl">
                Explore {{ $trails->total() }} hiking trails with detailed information, photos, and maps.
            </p>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" action="{{ route('trails.index') }}" class="grid md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Trails</label>
                    <input type="text" name="search" placeholder="Trail name, location..." 
                           value="{{ request('search') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
                    <select name="difficulty" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">All Difficulties</option>
                        <option value="1" {{ request('difficulty') == '1' ? 'selected' : '' }}>1 - Very Easy</option>
                        <option value="2" {{ request('difficulty') == '2' ? 'selected' : '' }}>2 - Easy</option>
                        <option value="3" {{ request('difficulty') == '3' ? 'selected' : '' }}>3 - Moderate</option>
                        <option value="4" {{ request('difficulty') == '4' ? 'selected' : '' }}>4 - Hard</option>
                        <option value="5" {{ request('difficulty') == '5' ? 'selected' : '' }}>5 - Very Hard</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Distance</label>
                    <select name="distance" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Any Distance</option>
                        <option value="0-5" {{ request('distance') == '0-5' ? 'selected' : '' }}>Under 5km</option>
                        <option value="5-10" {{ request('distance') == '5-10' ? 'selected' : '' }}>5-10km</option>
                        <option value="10-20" {{ request('distance') == '10-20' ? 'selected' : '' }}>10-20km</option>
                        <option value="20+" {{ request('distance') == '20+' ? 'selected' : '' }}>Over 20km</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full">
                        Search
                    </button>
                </div>
            </form>
            
            @if(request()->hasAny(['search', 'difficulty', 'distance']))
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        {{ $trails->total() }} trails found
                    </span>
                    <a href="{{ route('trails.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
                        Clear filters
                    </a>
                </div>
            @endif
        </div>

        <!-- Trail Cards Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @forelse($trails as $trail)
            <div class="trail-card group cursor-pointer hover:shadow-lg transition-shadow duration-300" 
                 onclick="window.location.href='{{ route('trails.show', $trail->id) }}'">
                
                <!-- Trail Image -->
                <div class="w-full h-48 bg-gradient-to-br from-green-400 to-blue-600 rounded-t-lg mb-4 flex items-center justify-center relative overflow-hidden">
                    @if($trail->featuredPhoto)
                        <img src="{{ $trail->featuredPhoto->url }}" alt="{{ $trail->name }}" 
                             class="w-full h-full object-cover">
                    @else
                        <svg class="w-16 h-16 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    @endif
                    
                    <!-- Featured Badge -->
                    @if($trail->is_featured)
                        <div class="absolute top-2 left-2">
                            <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded-full text-xs font-medium">
                                Featured
                            </span>
                        </div>
                    @endif
                    
                    <!-- Difficulty Badge -->
                    <div class="absolute top-2 right-2">
                        <span class="bg-white bg-opacity-90 text-gray-900 px-2 py-1 rounded-full text-xs font-semibold">
                            {{ $trail->difficulty_level }}/5
                        </span>
                    </div>
                </div>

                <!-- Trail Info -->
                <div class="p-4">
                    <div class="mb-2">
                        <h3 class="text-xl font-semibold text-gray-900 mb-1 group-hover:text-primary-600 transition-colors">
                            {{ $trail->name }}
                        </h3>
                        @if($trail->location)
                            <p class="text-sm text-gray-500">{{ $trail->location }}</p>
                        @endif
                    </div>
                    
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                        {{ Str::limit($trail->description, 120) }}
                    </p>

                    <!-- Trail Stats -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="text-center">
                            <div class="text-lg font-bold text-primary-600">{{ $trail->distance_km }}km</div>
                            <div class="text-xs text-gray-500">Distance</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-green-600">{{ $trail->elevation_gain_m }}m</div>
                            <div class="text-xs text-gray-500">Elevation</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-yellow-600">{{ $trail->estimated_time_hours }}h</div>
                            <div class="text-xs text-gray-500">Time</div>
                        </div>
                    </div>

                    <!-- Trail Type and Status -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                            {{ ucwords(str_replace('-', ' ', $trail->trail_type)) }}
                        </span>
                        <div class="flex items-center text-xs text-gray-500">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $trail->view_count }} views
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="flex items-center justify-between">
                        <span class="text-primary-600 text-sm font-medium group-hover:text-primary-700">
                            View Details →
                        </span>
                        @if($trail->best_seasons)
                            <div class="text-xs text-gray-500">
                                Best: {{ implode(', ', array_slice($trail->best_seasons, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <!-- Empty State -->
            <div class="col-span-full text-center py-16">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No trails found</h3>
                <p class="mt-2 text-gray-500">
                    @if(request()->hasAny(['search', 'difficulty', 'distance']))
                        Try adjusting your search criteria or <a href="{{ route('trails.index') }}" class="text-primary-600 hover:text-primary-700">clear all filters</a>.
                    @else
                        Check back soon for new trail additions.
                    @endif
                </p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($trails->hasPages())
            <div class="flex justify-center">
                {{ $trails->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush
@endsection