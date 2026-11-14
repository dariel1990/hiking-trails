@extends('layouts.public')

{{-- Event page layout: Premium Event Landing Page. Data comes from App\View\Composers\EventPageComposer. --}}

@section('title', $event->title.' — Xplore Smithers')
@section('meta_description', $summary)

@include('local-events.partials._head')

@section('content')
<div data-layout="premium" class="event-page bg-white" data-theme="{{ $event->theme }}">

    {{-- Hero --}}
    <header class="event-hero relative isolate flex min-h-[600px] flex-col overflow-hidden bg-forest-900 lg:min-h-[86vh]">
        @include('local-events.partials._christmas-snow', ['class' => '-z-10'])
        @include('local-events.partials._christmas-lights', ['class' => 'absolute inset-x-0 top-0 z-10'])
        @if($event->cover_image_url)
            <img src="{{ $event->cover_image_url }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover">
            <div class="event-hero-tint absolute inset-0 -z-10 bg-forest-900/70"></div>
        @endif

        <div class="mx-auto w-full max-w-6xl px-4 pt-8 sm:px-6 lg:px-8 lg:pt-10">
            <a href="{{ route('local-events.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-white/75 hover:text-white">
                @include('local-events.partials._icon', ['name' => 'back'])
                All Events
            </a>
        </div>

        <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col items-center justify-center px-4 pb-32 pt-10 text-center sm:px-6">
            <div class="flex flex-wrap items-center justify-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-white">
                    <span aria-hidden="true">{{ $event->marker_icon }}</span>{{ $event->category ?: 'Event' }}
                </span>
                @if($event->is_featured)
                    <span class="event-featured-badge rounded-full bg-accent-500 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-white">★ Featured</span>
                @endif
                @if($event->has_passed)
                    <span class="rounded-full bg-forest-800 px-3 py-1 text-xs font-semibold text-white">This event has ended</span>
                @endif
            </div>

            <h1 class="{{ $isChristmas ? 'event-title-festive text-5xl sm:text-7xl lg:text-8xl' : 'font-display text-4xl font-semibold sm:text-6xl lg:text-7xl' }} mt-6 leading-[1.05] text-white">
                {{ $event->title }}
            </h1>
            @if($event->tagline)
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-sand-100/90 md:text-xl">{{ $event->tagline }}</p>
            @endif

            <p class="mt-6 text-sm font-medium text-white/90 md:text-base">
                {{ $event->date_range_label }}@if($event->time_range_label)<span class="mx-2 text-white/40">·</span>{{ $event->time_range_label }}@endif
            </p>

            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                @if($event->external_url)
                    <a href="{{ $event->external_url }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 rounded-full bg-sand-100 px-6 py-3 text-sm font-semibold text-forest-900 hover:bg-white">
                        More info
                        @include('local-events.partials._icon', ['name' => 'external'])
                    </a>
                @endif
                <a href="{{ route('events.calendar', $event->id) }}"
                    class="inline-flex items-center gap-2 rounded-full border border-white/60 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                    @include('local-events.partials._icon', ['name' => 'calendar'])
                    Add to Calendar
                </a>
                <button type="button" data-share-open="event-share" aria-label="Share this event"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/60 text-white hover:bg-white/10">
                    @include('local-events.partials._icon', ['name' => 'share', 'class' => 'h-5 w-5'])
                </button>
            </div>
        </div>
    </header>

    {{-- Key facts --}}
    <div class="relative z-10 mx-auto -mt-20 max-w-6xl px-4 sm:px-6 lg:px-8">
        <dl class="grid grid-cols-1 divide-y divide-gray-100 rounded-2xl border border-gray-100 bg-white shadow-[0_24px_60px_-28px_rgba(25,54,52,0.4)] sm:grid-cols-2 sm:divide-y-0 lg:grid-cols-4 lg:divide-x">
            @foreach(array_filter([
                ['calendar', 'Date', $event->date_range_label, null],
                ['clock', 'Time', $event->time_range_label, null],
                ['pin', 'Venue', $place, $event->venue && $event->location !== $event->venue ? $event->location : null],
                ['user', 'Hosted by', $event->organizer, null],
            ], fn ($fact) => filled($fact[2])) as [$icon, $label, $value, $detail])
                <div class="flex items-start gap-4 p-6">
                    <span class="event-accent-soft event-accent-text flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full">
                        @include('local-events.partials._icon', ['name' => $icon, 'class' => 'h-5 w-5'])
                    </span>
                    <div class="min-w-0">
                        <dt class="text-xs font-semibold uppercase tracking-[0.12em] text-forest-500">{{ $label }}</dt>
                        <dd class="mt-1 text-[15px] font-semibold leading-snug text-gray-900">{{ $value }}</dd>
                        @if($detail)
                            <dd class="mt-0.5 text-sm text-gray-500">{{ $detail }}</dd>
                        @endif
                    </div>
                </div>
            @endforeach
        </dl>
    </div>

    {{-- About --}}
    @if($event->description || $event->video_url)
        <section class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:py-28">
            <p class="event-eyebrow text-center text-xs font-semibold uppercase tracking-[0.2em]">About the event</p>
            <h2 class="mt-3 text-center font-display text-3xl font-semibold text-forest-900 sm:text-4xl lg:text-4xl">What to expect</h2>
            @if($event->description)
                <div class="mt-10">
                    @include('local-events.partials._description', ['textClass' => 'text-lg leading-8 text-gray-600', 'clampClass' => 'line-clamp-[14]'])
                </div>
            @endif
            @if($event->video_url)
                <div class="mt-10 overflow-hidden rounded-2xl">
                    @include('partials.video-embed', ['model' => $event, 'embedId' => 'event-video'])
                </div>
            @endif
        </section>
    @endif

    {{-- Map & activities --}}
    @if($hasMap || $roamingStops->isNotEmpty())
        <section class="event-band border-y border-sand-200 bg-sand-100 py-20 lg:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="event-eyebrow text-xs font-semibold uppercase tracking-[0.2em]">{{ $hasMap ? 'Plan your visit' : 'Around the event' }}</p>
                    <h2 class="mt-3 font-display text-3xl font-semibold text-forest-900 sm:text-4xl lg:text-4xl">{{ $stopsHeading }}</h2>
                    <p class="mt-4 text-gray-500">
                        @if($stopCount){{ $stopCount }} {{ $stopNoun }}@endif
                        @if($event->route_distance_km)<span class="mx-1.5 text-gray-300">·</span>{{ number_format($event->route_distance_km, 1) }} km route @endif
                    </p>
                </div>

                <div class="mt-12 {{ $hasMap && $mappedStops->isNotEmpty() ? 'lg:grid lg:grid-cols-5 lg:gap-8' : '' }}">
                    @if($hasMap)
                        <div class="lg:col-span-3">
                            @include('local-events.partials._map', ['frameClass' => 'h-[360px] rounded-2xl border border-sand-300 sm:h-[460px] lg:sticky lg:top-28 lg:h-[600px]'])
                        </div>
                    @endif
                    @if($mappedStops->isNotEmpty())
                        <div class="event-scroll mt-8 lg:col-span-2 lg:mt-0 lg:max-h-[600px] lg:overflow-y-auto lg:pr-2">
                            @include('local-events.partials._stops', ['titleClass' => 'text-[17px] font-medium tracking-wide lg:text-[17px]'])
                        </div>
                    @endif
                </div>

                @if($roamingStops->isNotEmpty())
                    <div class="mt-12">
                        <h3 class="text-xl font-medium tracking-wide text-forest-900 lg:text-xl">Around downtown</h3>
                        <p class="mb-4 mt-1 text-sm text-gray-500">These happen all around the event — keep an eye out!</p>
                        @include('local-events.partials._roaming', ['listClass' => 'grid gap-3 md:grid-cols-2', 'titleClass' => 'text-[17px] font-medium tracking-wide lg:text-[17px]'])
                    </div>
                @endif
            </div>
        </section>
    @endif


    {{-- Gallery --}}
    @if($galleryMedia->isNotEmpty())
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8 lg:py-24">
            @include('local-events.partials._gallery', ['layout' => 'mosaic', 'headingClass' => 'font-display text-3xl font-semibold text-forest-900 lg:text-3xl'])
        </div>
    @endif

    {{-- Closing call to action --}}
    @unless($event->has_passed)
        <section class="event-cta-band bg-forest-900">
            <div class="mx-auto flex max-w-6xl flex-col items-start gap-8 px-4 py-16 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div>
                    <p class="event-cta-eyebrow text-xs font-semibold uppercase tracking-[0.2em] text-accent-400">Save the date</p>
                    <h2 class="mt-3 font-display text-3xl font-semibold text-white lg:text-4xl">{{ $event->title }}</h2>
                    <p class="mt-3 text-sand-100/80">{{ $event->date_range_label }}@if($event->time_range_label) · {{ $event->time_range_label }}@endif @if($place) · {{ $place }}@endif</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('events.calendar', $event->id) }}"
                        class="inline-flex items-center gap-2 rounded-full bg-sand-100 px-6 py-3 text-sm font-semibold text-forest-900 hover:bg-white">
                        @include('local-events.partials._icon', ['name' => 'calendar'])
                        Add to Calendar
                    </a>
                    <button type="button" data-share-open="event-share"
                        class="inline-flex items-center gap-2 rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                        @include('local-events.partials._icon', ['name' => 'share'])
                        Share
                    </button>
                </div>
            </div>
        </section>
    @endunless
    @include('local-events.partials._christmas-signoff', ['class' => ''])
</div>

@if($hasMap)
    @include('tours._map-script')
@endif
@include('local-events.partials._share')
@include('local-events.partials._layout-preview-bar')
@endsection
