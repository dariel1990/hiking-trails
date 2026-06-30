@extends('layouts.public')

@section('title', 'Tours — Xplore Smithers')

@section('content')
<div class="min-h-screen bg-gray-50 pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4">

        <!-- Header -->
        <div class="mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">Tours</h1>
            <p class="text-lg text-gray-600 max-w-2xl">
                Curated experiences that take you through the best of Smithers BC — one unforgettable stop at a time.
            </p>
        </div>

        @if($tours->isEmpty())
            <div class="text-center py-20 text-gray-500">
                <div class="text-5xl mb-4">🗺️</div>
                <h2 class="text-xl font-semibold mb-2">No tours available yet</h2>
                <p class="text-sm">Check back soon for guided tour experiences.</p>
            </div>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($tours as $tour)
                    <a href="{{ route('tours.show', $tour->slug) }}"
                        class="group rounded-2xl overflow-hidden bg-white shadow-md hover:shadow-xl transition-all duration-300 flex flex-col">

                        <!-- Cover Image -->
                        <div class="relative h-52 bg-gradient-to-br from-blue-400 to-green-500 overflow-hidden">
                            @if($tour->cover_image_url)
                                <img src="{{ $tour->cover_image_url }}" alt="{{ $tour->title }}"
                                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-6xl opacity-40">🗺️</div>
                            @endif
                            <!-- Type badge -->
                            <div class="absolute top-3 left-3">
                                <span class="inline-flex items-center rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-xs font-semibold text-gray-800">
                                    {{ App\Models\Tour::getTourTypes()[$tour->tour_type] ?? ucfirst($tour->tour_type) }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex flex-col flex-1 p-5">
                            <h2 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-blue-700 transition-colors">
                                {{ $tour->title }}
                            </h2>
                            @if($tour->tagline)
                                <p class="text-sm text-gray-500 mb-4">{{ $tour->tagline }}</p>
                            @endif

                            <!-- Badges -->
                            <div class="flex flex-wrap gap-2 mt-auto">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                    📍 {{ $tour->stops_count }} stop{{ $tour->stops_count !== 1 ? 's' : '' }}
                                </span>
                                @if($tour->duration_estimate)
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                        🕐 {{ $tour->duration_estimate }}
                                    </span>
                                @endif
                                @if($tour->difficulty_summary)
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                        🥾 {{ $tour->difficulty_summary }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 inline-flex items-center text-sm font-medium text-blue-600 group-hover:gap-2 gap-1 transition-all">
                                Explore Tour
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
