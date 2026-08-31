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
                    @include('tours._card', ['tour' => $tour])
                @endforeach
            </div>
        @endif

    </div>
</div>

@endsection
