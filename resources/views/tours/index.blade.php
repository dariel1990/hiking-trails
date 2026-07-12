@extends('layouts.public')

@section('title', 'Guided Tours — Xplore Smithers')

@push('styles')
<style>
    .tour-card-img { transition: transform 0.5s ease; }
    .tour-card:hover .tour-card-img { transform: scale(1.06); }
    .tour-card { transition: box-shadow 0.2s ease, transform 0.2s ease; }
    .tour-card:hover { transform: translateY(-3px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
</style>
@endpush

@section('content')

<!-- Hero -->
<section class="relative flex items-center justify-center hero-gradient overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-pattern-trees opacity-20 z-15"></div>

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-40 z-20"></div>

    <!-- Content -->
    <div class="relative z-30 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20 pt-32">
        <!-- Badge -->
        <div class="mb-8 fade-in">
            <span class="inline-flex items-center gap-2 px-6 py-3 bg-white/25 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                {{ $tours->count() }} Self-Guided Tour{{ $tours->count() !== 1 ? 's' : '' }} Available
            </span>
        </div>

        <!-- Headline -->
        <div class="slide-in-up mb-8">
            <h1 class="text-5xl md:text-7xl font-bold leading-tight">
                <span class="text-white">Explore the Best of</span><br>
                <span class="bg-gradient-to-r from-emerald-300 via-sand-200 to-accent-300 bg-clip-text text-transparent">
                    Smithers
                </span>
            </h1>
        </div>

        <!-- Subtitle -->
        <div class="slide-in-up mb-12" style="animation-delay: 0.2s;">
            <p class="text-xl md:text-2xl text-white/80 leading-relaxed max-w-3xl mx-auto">
                Curated driving tours that connect the region's most stunning natural landmarks — from roaring waterfalls to mountain vistas.
            </p>
        </div>

        @include('partials.app-promo-banner')
    </div>
</section>

<!-- Tours Grid -->
<div class="bg-gray-50 min-h-screen py-14">
    <div class="max-w-7xl mx-auto px-4">

        @if($tours->isEmpty())
            <div class="text-center py-24 text-gray-400">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-600 mb-2">No tours available yet</h2>
                <p class="text-sm">Check back soon for guided tour experiences.</p>
            </div>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($tours as $tour)
                    <a href="{{ route('tours.show', $tour->slug) }}" class="tour-card group block rounded-2xl overflow-hidden bg-white shadow-md">

                        <!-- Cover Image -->
                        <div class="relative h-56 overflow-hidden bg-gradient-to-br from-forest-700 to-forest-900">
                            @if($tour->cover_image_url)
                                <img src="{{ $tour->cover_image_url }}" alt="{{ $tour->title }}"
                                    class="tour-card-img absolute inset-0 w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-emerald-600">
                                    <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="Xplore Smithers"
                                        class="w-32 h-32 object-contain">
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                            @endif

                            <!-- Tour icon overlay -->
                            <div class="absolute top-3 left-3 flex items-start gap-2">
                                <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/90 backdrop-blur-sm shadow-md overflow-hidden">
                                    @if($tour->icon_image_url)
                                        <img src="{{ $tour->icon_image_url }}" alt="{{ $tour->title }} icon" class="w-full h-full object-contain p-0.5">
                                    @else
                                        <span class="text-2xl leading-none">{{ $tour->tour_icon }}</span>
                                    @endif
                                </span>
                                @if($tour->is_featured)
                                    <span class="inline-flex items-center rounded-full bg-accent-500 px-2.5 py-1 text-xs font-semibold text-white shadow-sm self-center">
                                        ★ Featured
                                    </span>
                                @endif
                            </div>

                            <!-- Stop count pill -->
                            <div class="absolute bottom-3 right-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-black/50 backdrop-blur-sm px-2.5 py-1 text-xs font-semibold text-white">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                    {{ $tour->stops_count }} stop{{ $tour->stops_count !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex flex-col">
                            <h2 class="text-base font-bold text-gray-900 mb-1 group-hover:text-forest-700 transition-colors leading-snug">
                                {{ $tour->title }}
                            </h2>
                            @if($tour->tagline)
                                <p class="text-sm text-gray-500 mb-4 leading-relaxed">{{ $tour->tagline }}</p>
                            @endif

                            <!-- Meta chips -->
                            <div class="flex flex-wrap gap-1.5 mt-auto">
                                @if($tour->duration_estimate)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-forest-50 border border-forest-100 px-2 py-0.5 text-xs font-medium text-forest-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $tour->duration_estimate }}
                                    </span>
                                @endif
                                @if($tour->difficulty_summary)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-forest-50 border border-forest-100 px-2 py-0.5 text-xs font-medium text-forest-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        {{ $tour->difficulty_summary }}
                                    </span>
                                @endif
                                @if($tour->total_driving_km)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-forest-50 border border-forest-100 px-2 py-0.5 text-xs font-medium text-forest-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                        {{ number_format($tour->total_driving_km, 0) }} km drive
                                    </span>
                                @endif
                            </div>

                            <!-- CTA -->
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <span class="text-sm font-semibold text-forest-700 group-hover:text-accent-600 transition-colors flex items-center gap-1">
                                    Explore Tour
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>
</div>

@endsection
