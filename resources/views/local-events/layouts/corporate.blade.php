@extends('layouts.public')

{{-- Event page layout: Professional Corporate. Data comes from App\View\Composers\EventPageComposer. --}}


@section('title', $event->title.' — Xplore Smithers')
@section('meta_description', $summary)

@include('local-events.partials._head')

@section('content')
<div data-layout="corporate" class="event-page event-surface bg-gray-50 pb-16" data-theme="{{ $event->theme }}">
    @include('local-events.partials._christmas-lights', ['class' => 'relative bg-white'])

    {{-- Page header --}}
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 pb-8 pt-6 sm:px-6 lg:px-8">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('local-events.index') }}" class="inline-flex items-center gap-1 hover:text-gray-900">
                    @include('local-events.partials._icon', ['name' => 'back', 'class' => 'h-3.5 w-3.5'])
                    All Events
                </a>
                <span class="text-gray-300" aria-hidden="true">/</span>
                <span class="truncate text-gray-900" aria-current="page">{{ $event->title }}</span>
            </nav>

            <div class="mt-6 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0 max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700">
                            <span aria-hidden="true">{{ $event->marker_icon }}</span>{{ $event->category ?: 'Event' }}
                        </span>
                        @if($event->is_featured)
                            <span class="event-featured-badge rounded-md bg-accent-500 px-2.5 py-1 text-xs font-semibold text-white">★ Featured</span>
                        @endif
                        @if($event->has_passed)
                            <span class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">This event has ended</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>Upcoming
                            </span>
                        @endif
                    </div>
                    <h1 class="{{ $isChristmas ? 'event-title-festive text-4xl lg:text-5xl' : 'font-sans text-3xl font-bold tracking-tight lg:text-4xl' }} mt-4 text-forest-900">{{ $event->title }}</h1>
                    @if($event->tagline)
                        <p class="mt-3 text-base leading-relaxed text-gray-600 lg:text-lg">{{ $event->tagline }}</p>
                    @endif
                </div>

                <div class="flex flex-shrink-0 flex-wrap gap-2">
                    <button type="button" data-share-open="event-share"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        @include('local-events.partials._icon', ['name' => 'share'])
                        Share
                    </button>
                    <a href="{{ route('events.calendar', $event->id) }}"
                        class="hidden items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 lg:inline-flex">
                        @include('local-events.partials._icon', ['name' => 'calendar'])
                        Add to Calendar
                    </a>
                    @if($event->external_url)
                        <a href="{{ $event->external_url }}" target="_blank" rel="noopener"
                            class="event-accent-bg hidden items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold text-white lg:inline-flex">
                            More info
                            @include('local-events.partials._icon', ['name' => 'external'])
                        </a>
                    @endif
                </div>
            </div>

            <dl class="mt-8 hidden flex-wrap divide-x divide-gray-200 border-t border-gray-100 pt-6 text-sm lg:flex">
                @foreach(array_filter([
                    ['calendar', 'Date', $event->date_range_label],
                    ['clock', 'Time', $event->time_range_label],
                    ['pin', 'Venue', $place],
                    ['user', 'Organizer', $event->organizer],
                ], fn ($fact) => filled($fact[2])) as [$icon, $label, $value])
                    <div class="flex items-center gap-3 px-6 first:pl-0">
                        <span class="text-gray-400">@include('local-events.partials._icon', ['name' => $icon, 'class' => 'h-5 w-5'])</span>
                        <div>
                            <dt class="sr-only">{{ $label }}</dt>
                            <dd class="font-medium text-gray-900">{{ $value }}</dd>
                        </div>
                    </div>
                @endforeach
            </dl>
        </div>
    </header>

    <div class="mx-auto grid max-w-7xl gap-6 px-4 pt-8 sm:px-6 lg:grid-cols-12 lg:px-8">

        {{-- Main column --}}
        <div class="min-w-0 space-y-6 lg:col-span-8">
            @if($event->cover_image_url)
                <div class="relative overflow-hidden rounded-lg border border-gray-200">
                    <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}"
                        class="aspect-[16/9] w-full bg-gray-100 object-cover">
                    @include('local-events.partials._christmas-snow')
                </div>
            @endif

            @if($event->description)
                <section class="rounded-lg border border-gray-200 bg-white">
                    <h2 class="border-b border-gray-200 px-6 py-4 font-sans text-base font-semibold tracking-normal text-forest-900 lg:text-base">Overview</h2>
                    <div class="px-6 py-5">
                        @include('local-events.partials._description', ['textClass' => 'text-[15px] leading-7 text-gray-700'])
                    </div>
                </section>
            @endif

            @if($event->video_url)
                <section class="rounded-lg border border-gray-200 bg-white">
                    <h2 class="border-b border-gray-200 px-6 py-4 font-sans text-base font-semibold tracking-normal text-forest-900 lg:text-base">Video</h2>
                    <div class="p-6">@include('partials.video-embed', ['model' => $event, 'embedId' => 'event-video'])</div>
                </section>
            @endif

            @if($hasMap)
                <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-6 py-4">
                        <h2 class="font-sans text-base font-semibold tracking-normal text-forest-900 lg:text-base">Event map</h2>
                        <p class="text-sm text-gray-500">
                            @if($stopCount){{ $stopCount }} {{ $stopNoun }}@endif
                            @if($event->route_distance_km)<span class="mx-1 text-gray-300">|</span>{{ number_format($event->route_distance_km, 1) }} km route @endif
                        </p>
                    </div>
                    @include('local-events.partials._map', ['frameClass' => 'h-[340px] sm:h-[440px]'])
                </section>
            @endif

            @if($mappedStops->isNotEmpty() || $roamingStops->isNotEmpty())
                <section class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 px-6 py-4">
                        <h2 class="font-sans text-base font-semibold tracking-normal text-forest-900 lg:text-base">{{ $isActivityMap ? 'Schedule' : $stopsHeading }}</h2>
                        @if($mappedStops->isNotEmpty())
                            <p class="text-sm text-gray-500">Select a row to locate it on the map</p>
                        @endif
                    </div>
                    @if($mappedStops->isNotEmpty())
                        <div class="hidden grid-cols-[7rem_1fr_auto] gap-x-4 border-b border-gray-100 bg-gray-50 px-5 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 sm:grid">
                            <span class="pl-0.5">Time</span><span>{{ $isActivityMap ? 'Activity' : 'Stop' }}</span><span>Directions</span>
                        </div>
                        @include('local-events.partials._stops', ['variant' => 'schedule'])
                    @endif
                    @if($roamingStops->isNotEmpty())
                        <div class="border-t border-gray-200 bg-gray-50 px-6 py-5">
                            <h3 class="font-sans text-sm font-semibold tracking-normal text-gray-900 lg:text-sm">Around downtown</h3>
                            <p class="mb-4 mt-1 text-sm text-gray-500">These happen all around the event — keep an eye out!</p>
                            @include('local-events.partials._roaming', ['listClass' => 'grid gap-3 md:grid-cols-2'])
                        </div>
                    @endif
                </section>
            @endif


            @if($galleryMedia->isNotEmpty())
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    @include('local-events.partials._gallery', ['headingClass' => 'font-sans text-base font-semibold tracking-normal text-forest-900 lg:text-base'])
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="order-first space-y-6 lg:order-none lg:col-span-4">
            <div class="space-y-6 lg:sticky lg:top-24">
                <section class="rounded-lg border border-gray-200 bg-white">
                    <h2 class="border-b border-gray-200 px-6 py-4 font-sans text-base font-semibold tracking-normal text-forest-900 lg:text-base">Event information</h2>
                    <dl class="divide-y divide-gray-100 text-sm">
                        @foreach(array_filter([
                            'Date' => $event->date_range_label,
                            'Time' => $event->time_range_label,
                            'Venue' => $event->venue,
                            'Address' => $event->location !== $event->venue ? $event->location : null,
                            'Category' => $event->category,
                            'Organizer' => $event->organizer,
                        ]) as $label => $value)
                            <div class="grid grid-cols-[6rem_1fr] gap-3 px-6 py-3">
                                <dt class="text-gray-500">{{ $label }}</dt>
                                <dd class="font-medium text-gray-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <div class="grid gap-2 border-t border-gray-200 p-6">
                        @if($event->external_url)
                            <a href="{{ $event->external_url }}" target="_blank" rel="noopener"
                                class="event-accent-bg inline-flex items-center justify-center gap-2 rounded-md px-4 py-2.5 text-sm font-semibold text-white">
                                More info
                                @include('local-events.partials._icon', ['name' => 'external'])
                            </a>
                        @endif
                        <a href="{{ route('events.calendar', $event->id) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            @include('local-events.partials._icon', ['name' => 'calendar'])
                            Add to Calendar
                        </a>
                    </div>
                </section>

                @if($event->organizer)
                    <section class="hidden rounded-lg border border-gray-200 bg-white p-6 lg:block">
                        <h2 class="font-sans text-xs font-semibold uppercase tracking-wider text-gray-500 lg:text-xs">Organized by</h2>
                        <div class="mt-4 flex items-center gap-3">
                            <span class="event-accent-bg flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-md text-sm font-bold text-white">
                                {{ Str::of($event->organizer)->explode(' ')->reject(fn ($word) => in_array(Str::lower($word), ['of', 'the', 'and', '&'], true))->take(2)->map(fn ($word) => Str::substr($word, 0, 1))->implode('') }}
                            </span>
                            <p class="font-semibold leading-snug text-gray-900">{{ $event->organizer }}</p>
                        </div>
                    </section>
                @endif
            </div>
        </aside>
    </div>
    @include('local-events.partials._christmas-signoff')
</div>

@if($hasMap)
    @include('tours._map-script')
@endif
@include('local-events.partials._share')
@include('local-events.partials._layout-preview-bar')
@endsection
