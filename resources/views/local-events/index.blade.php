@extends('layouts.public')

@section('title', 'Events — Xplore Smithers')
@section('meta_description', 'Seasonal events around Smithers, BC — see every activity on the map, with times, places and details.')

@push('styles')
<style>
    .tour-card-img { transition: transform 0.5s ease; }
    .tour-card:hover .tour-card-img { transform: scale(1.06); }
    .tour-card { transition: box-shadow 0.2s ease, transform 0.2s ease; }
    .tour-card:hover { transform: translateY(-3px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }

    .events-hero { background: linear-gradient(160deg, #0F1B2D 0%, #13283f 45%, #1E5B3A 100%); }
    .events-snow {
        position: absolute; inset: 0; pointer-events: none;
        background-image:
            radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,0.9) 50%, transparent 51%),
            radial-gradient(2px 2px at 120px 80px, rgba(255,255,255,0.8) 50%, transparent 51%),
            radial-gradient(3px 3px at 60px 140px, rgba(255,255,255,0.7) 50%, transparent 51%),
            radial-gradient(2px 2px at 170px 170px, rgba(255,255,255,0.85) 50%, transparent 51%);
        background-size: 200px 200px;
        animation: events-snowfall 16s linear infinite;
        opacity: 0.7;
    }
    @keyframes events-snowfall {
        from { background-position: 0 0; }
        to { background-position: 40px 600px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .events-snow { animation: none; }
    }
</style>
@endpush

@section('content')

<!-- Hero -->
<section class="events-hero relative flex items-center justify-center overflow-hidden">
    <div class="events-snow" aria-hidden="true"></div>

    <div class="relative z-10 text-center text-white max-w-6xl mx-auto px-4 flex flex-col justify-center py-20 pt-32">
        <div class="mb-8">
            <span class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-semibold border border-white/30 shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $events->count() }} Event{{ $events->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        <h1 class="text-5xl md:text-7xl font-bold leading-tight mb-8">
            <span class="text-white">Events in</span><br>
            <span class="bg-gradient-to-r from-red-300 via-sand-200 to-emerald-300 bg-clip-text text-transparent">Smithers</span>
        </h1>

        <p class="text-xl md:text-2xl text-white/80 leading-relaxed max-w-3xl mx-auto">
            Find what's happening around town — every activity on the map, with times, places and details.
        </p>
    </div>
</section>

<!-- Events Grid -->
<div class="bg-gray-50 min-h-screen py-14">
    <div class="max-w-7xl mx-auto px-4">

        @if($events->isEmpty())
            <div class="text-center py-24 text-gray-400">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-600 mb-2">No events right now</h2>
                <p class="text-sm">Check back soon for upcoming events.</p>
            </div>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($events as $event)
                    @include('tours._card', ['tour' => $event])
                @endforeach
            </div>
        @endif

    </div>
</div>

@endsection
