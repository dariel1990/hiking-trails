@extends('layouts.public')

{{-- Event page layout: Modern Minimalist. Data comes from App\View\Composers\EventPageComposer. --}}

@section('title', $event->title.' — Xplore Smithers')
@section('meta_description', $summary)

@include('local-events.partials._head')

@section('content')
<div data-layout="minimal" class="event-page event-surface bg-white" data-theme="{{ $event->theme }}">
    @include('local-events.partials._christmas-lights')
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

        <header class="pb-10 pt-8 md:pb-14 md:pt-12">
            <a href="{{ route('local-events.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900">
                @include('local-events.partials._icon', ['name' => 'back'])
                All Events
            </a>

            <p class="mt-8 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                <span class="event-eyebrow">{{ $event->category ?: 'Event' }}</span>
                @if($event->is_featured)
                    <span class="text-gray-300" aria-hidden="true">/</span><span class="text-forest-700">★ Featured</span>
                @endif
                @if($event->has_passed)
                    <span class="text-gray-300" aria-hidden="true">/</span><span>This event has ended</span>
                @endif
            </p>

            <h1 class="{{ $isChristmas ? 'event-title-festive text-5xl sm:text-6xl lg:text-7xl' : 'font-sans text-4xl font-semibold tracking-tight sm:text-5xl lg:text-6xl' }} mt-4 max-w-4xl leading-[1.05] text-forest-900">
                {{ $event->title }}
            </h1>
            @if($event->tagline)
                <p class="mt-5 max-w-2xl text-lg leading-relaxed text-gray-500 md:text-xl">{{ $event->tagline }}</p>
            @endif

            <div class="mt-8 flex flex-wrap items-center gap-3">
                @if($event->external_url)
                    <a href="{{ $event->external_url }}" target="_blank" rel="noopener"
                        class="event-accent-bg inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white">
                        More info
                        @include('local-events.partials._icon', ['name' => 'external'])
                    </a>
                @endif
                <a href="{{ route('events.calendar', $event->id) }}"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-300 px-5 py-2.5 text-sm font-semibold text-forest-900 hover:border-forest-700">
                    @include('local-events.partials._icon', ['name' => 'calendar'])
                    Add to Calendar
                </a>
                <button type="button" data-share-open="event-share"
                    class="inline-flex items-center gap-2 rounded-full px-3 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900">
                    @include('local-events.partials._icon', ['name' => 'share'])
                    Share
                </button>
            </div>
        </header>

        @if($event->cover_image_url)
            <div class="relative overflow-hidden rounded-2xl">
                <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}"
                    class="aspect-[4/3] w-full bg-gray-100 object-cover sm:aspect-[16/9] lg:aspect-[21/9]">
                @include('local-events.partials._christmas-snow')
            </div>
        @endif

        <dl class="mt-10 grid grid-cols-1 gap-y-6 border-y border-gray-200 py-8 sm:grid-cols-2 md:mt-12 lg:grid-cols-4 lg:gap-y-0">
            <div class="lg:pr-6">
                <dt class="text-xs font-medium uppercase tracking-[0.14em] text-gray-400">Date</dt>
                <dd class="mt-2 text-[15px] font-medium text-gray-900">{{ $event->date_range_label }}</dd>
            </div>
            <div class="sm:pl-6 sm:border-l sm:border-gray-200 lg:px-6">
                <dt class="text-xs font-medium uppercase tracking-[0.14em] text-gray-400">Time</dt>
                <dd class="mt-2 text-[15px] font-medium text-gray-900">{{ $event->time_range_label ?: 'See details' }}</dd>
            </div>
            <div class="lg:border-l lg:border-gray-200 lg:px-6">
                <dt class="text-xs font-medium uppercase tracking-[0.14em] text-gray-400">Location</dt>
                <dd class="mt-2 text-[15px] font-medium text-gray-900">
                    {{ $place ?: 'Smithers, BC' }}
                    @if($event->venue && $event->location && $event->location !== $event->venue)
                        <span class="mt-0.5 block text-sm font-normal text-gray-500">{{ $event->location }}</span>
                    @endif
                </dd>
            </div>
            <div class="sm:pl-6 sm:border-l sm:border-gray-200">
                <dt class="text-xs font-medium uppercase tracking-[0.14em] text-gray-400">Organizer</dt>
                <dd class="mt-2 text-[15px] font-medium text-gray-900">{{ $event->organizer ?: 'Xplore Smithers' }}</dd>
            </div>
        </dl>

        @if($event->description)
            <section class="grid gap-4 py-12 md:grid-cols-12 md:gap-8 md:py-16">
                <h2 class="font-sans text-base font-semibold tracking-normal text-forest-900 md:col-span-3 lg:text-base">About</h2>
                <div class="md:col-span-9">
                    @include('local-events.partials._description', ['textClass' => 'text-lg leading-8 text-gray-700', 'clampClass' => 'line-clamp-[12]'])
                </div>
            </section>
        @endif

        @if($event->video_url)
            <section class="grid gap-4 border-t border-gray-200 py-12 md:grid-cols-12 md:gap-8 md:py-16">
                <h2 class="font-sans text-base font-semibold tracking-normal text-forest-900 md:col-span-3 lg:text-base">Video</h2>
                <div class="md:col-span-9">
                    @include('partials.video-embed', ['model' => $event, 'embedId' => 'event-video'])
                </div>
            </section>
        @endif

        @if($hasMap || $roamingStops->isNotEmpty())
            <section class="grid gap-4 border-t border-gray-200 py-12 md:grid-cols-12 md:gap-8 md:py-16">
                <div class="md:col-span-3">
                    <h2 class="font-sans text-base font-semibold tracking-normal text-forest-900 lg:text-base">{{ $hasMap ? 'Map & '.$stopsHeading : $stopsHeading }}</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        @if($stopCount){{ $stopCount }} {{ $stopNoun }}@endif
                        @if($event->route_distance_km)<span class="block">{{ number_format($event->route_distance_km, 1) }} km route</span>@endif
                    </p>
                    @if($mappedStops->isNotEmpty())
                        <p class="mt-4 hidden text-xs text-gray-400 md:block">Select {{ $isActivityMap ? 'an activity' : 'a stop' }} to find it on the map.</p>
                    @endif
                </div>
                <div class="space-y-8 md:col-span-9">
                    @if($hasMap)
                        @include('local-events.partials._map', ['frameClass' => 'h-[340px] rounded-2xl border border-gray-200 md:h-[460px]'])
                    @endif
                    @if($mappedStops->isNotEmpty())
                        @include('local-events.partials._stops', ['listClass' => 'grid gap-3 sm:grid-cols-2'])
                    @endif
                    @if($roamingStops->isNotEmpty())
                        <div>
                            <h3 class="font-sans text-sm font-semibold tracking-normal text-gray-900 lg:text-sm">Around downtown</h3>
                            <p class="mb-4 mt-1 text-sm text-gray-500">These happen all around the event — keep an eye out!</p>
                            @include('local-events.partials._roaming', ['listClass' => 'grid gap-3 sm:grid-cols-2'])
                        </div>
                    @endif
                </div>
            </section>
        @endif


        @if($galleryMedia->isNotEmpty())
            <div class="grid gap-4 border-t border-gray-200 py-12 md:grid-cols-12 md:gap-8 md:py-16">
                <p class="text-base font-semibold text-forest-900 md:col-span-3" aria-hidden="true">{{ $galleryTitle }}</p>
                <div class="md:col-span-9">
                    @include('local-events.partials._gallery', ['heading' => false, 'gridClass' => null])
                </div>
            </div>
        @endif
    </div>
    @include('local-events.partials._christmas-signoff')
</div>

@if($hasMap)
    @include('tours._map-script')
@endif
@include('local-events.partials._share')
@include('local-events.partials._layout-preview-bar')
@endsection
