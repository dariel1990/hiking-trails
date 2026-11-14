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
                {{ $totalCount }} Upcoming Event{{ $totalCount !== 1 ? 's' : '' }}
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
<div id="events" class="bg-gray-50 min-h-screen py-14 scroll-mt-16">
    <div class="max-w-7xl mx-auto px-4">

        @if($months->count() > 1)
            <!-- Month filter -->
            <nav aria-label="Filter events by month" class="mb-10">
                <div class="flex items-center justify-between gap-4 mb-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Browse by month</h2>
                    @if($selectedMonth)
                        <a href="{{ route('local-events.index') }}#events" class="text-sm font-semibold text-forest-700 hover:text-accent-600 transition-colors">
                            Clear filter
                        </a>
                    @endif
                </div>
                <div class="relative flex items-center gap-2"
                    x-data="monthScroller()" x-init="init()">
                    <button type="button" @click="scroll(-1)" :disabled="! canPrev"
                        aria-label="Previous months"
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:border-forest-300 hover:text-forest-700 disabled:cursor-default disabled:opacity-30 disabled:hover:border-gray-200 disabled:hover:text-gray-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <div x-ref="track" @scroll.debounce.50ms="update()"
                        class="min-w-0 flex-1 overflow-x-auto scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <ul class="flex flex-nowrap gap-2 py-1">
                        <li>
                            <a href="{{ route('local-events.index') }}#events"
                                @if(! $selectedMonth) aria-current="page" @endif
                                class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border px-4 py-2 flex-shrink-0 text-sm font-semibold transition-colors {{ $selectedMonth ? 'border-gray-200 bg-white text-gray-700 hover:border-forest-300 hover:text-forest-700' : 'border-forest-700 bg-forest-700 text-white shadow-sm' }}">
                                All
                                <span class="rounded-full px-2 py-0.5 text-xs tabular-nums {{ $selectedMonth ? 'bg-gray-100 text-gray-600' : 'bg-white/20 text-white' }}">{{ $totalCount }}</span>
                            </a>
                        </li>
                        @foreach($months as $key => $month)
                            @php $isSelected = $selectedMonth === $key; @endphp
                            <li>
                                <a href="{{ route('local-events.index', ['month' => $key]) }}#events"
                                    @if($isSelected) aria-current="page" @endif
                                    class="inline-flex items-center gap-2 whitespace-nowrap rounded-full border px-4 py-2 flex-shrink-0 text-sm font-semibold transition-colors {{ $isSelected ? 'border-forest-700 bg-forest-700 text-white shadow-sm' : 'border-gray-200 bg-white text-gray-700 hover:border-forest-300 hover:text-forest-700' }}">
                                    {{ $month['label'] }}
                                    <span class="rounded-full px-2 py-0.5 text-xs tabular-nums {{ $isSelected ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $month['count'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    </div>

                    <button type="button" @click="scroll(1)" :disabled="! canNext"
                        aria-label="Next months"
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:border-forest-300 hover:text-forest-700 disabled:cursor-default disabled:opacity-30 disabled:hover:border-gray-200 disabled:hover:text-gray-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </nav>
        @endif

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
            @if($selectedMonth)
                <p class="mb-6 text-sm text-gray-500">
                    Showing <strong class="text-gray-900">{{ $events->count() }}</strong> event{{ $events->count() !== 1 ? 's' : '' }} in <strong class="text-gray-900">{{ $months[$selectedMonth]['label'] }}</strong>
                </p>
            @endif
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($events as $event)
                    @include('local-events._card', ['event' => $event])
                @endforeach
            </div>
        @endif

    </div>
</div>


@push('scripts')
<script>
    /**
     * One-row month filter: prev/next buttons scroll the row by most of its
     * width, are disabled at either end, and the selected month starts in view.
     */
    function monthScroller() {
        return {
            canPrev: false,
            canNext: false,
            init() {
                const track = this.$refs.track;
                const current = track.querySelector('[aria-current="page"]');
                if (current && current.offsetLeft + current.offsetWidth > track.clientWidth) {
                    track.scrollLeft = current.offsetLeft - track.clientWidth / 2 + current.offsetWidth / 2;
                }
                this.update();
                window.addEventListener('resize', () => this.update());
            },
            update() {
                const track = this.$refs.track;
                this.canPrev = track.scrollLeft > 4;
                this.canNext = track.scrollLeft + track.clientWidth < track.scrollWidth - 4;
            },
            scroll(direction) {
                const track = this.$refs.track;
                track.scrollBy({ left: direction * track.clientWidth * 0.8, behavior: 'smooth' });
            },
        };
    }
</script>
@endpush

@endsection
