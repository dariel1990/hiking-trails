@extends('layouts.public')

{{-- Event page layout: Modern Booking Experience. Data comes from App\View\Composers\EventPageComposer. --}}


@php
    $directionsUrl = $place ? 'https://www.google.com/maps/search/?api=1&query='.urlencode(collect([$event->venue, $event->location])->filter()->unique()->implode(', ')) : null;
    $hostInitials = $event->organizer ? Str::of($event->organizer)->explode(' ')->reject(fn ($word) => in_array(Str::lower($word), ['of', 'the', 'and', '&'], true))->take(2)->map(fn ($word) => Str::substr($word, 0, 1))->implode('') : null;
@endphp

@section('title', $event->title.' — Xplore Smithers')
@section('meta_description', $summary)

@include('local-events.partials._head')

@push('styles')
<style>
    /* Room for the fixed action bar on phones and tablets so it never covers the footer. */
    @media (max-width: 1023px) { body { padding-bottom: calc(4.75rem + env(safe-area-inset-bottom)); } }
</style>
@endpush

@section('content')
<div data-layout="booking" class="event-page event-surface bg-white pb-12 lg:pb-16" data-theme="{{ $event->theme }}">
    @include('local-events.partials._christmas-lights')
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between py-5">
            <a href="{{ route('local-events.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-gray-900">
                @include('local-events.partials._icon', ['name' => 'back'])
                All Events
            </a>
            <button type="button" data-share-open="event-share"
                class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                @include('local-events.partials._icon', ['name' => 'share'])
                Share
            </button>
        </div>

        @if($event->cover_image_url)
            <div class="relative overflow-hidden rounded-2xl">
                <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}"
                    class="aspect-[16/10] w-full bg-gray-100 object-cover sm:aspect-[2/1] lg:aspect-[5/2]">
                @include('local-events.partials._christmas-snow')
            </div>
        @else
            <div class="event-accent-soft flex aspect-[2/1] w-full items-center justify-center rounded-2xl text-7xl lg:aspect-[5/2]" aria-hidden="true">{{ $event->marker_icon }}</div>
        @endif

        <div class="mt-8 lg:mt-10 lg:grid lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-14 xl:grid-cols-[minmax(0,1fr)_24rem]">

            {{-- Event content --}}
            <div class="min-w-0">
                <p class="event-accent-text text-sm font-semibold">
                    {{ $event->is_multi_day ? $event->date_range_label : $event->event_date->format('l, F j') }}@if($event->formatted_time) · {{ $event->formatted_time }}@endif
                </p>
                <h1 class="{{ $isChristmas ? 'event-title-festive text-4xl sm:text-5xl lg:text-6xl' : 'font-sans text-3xl font-bold tracking-tight sm:text-4xl lg:text-[2.75rem]' }} mt-2 leading-tight text-forest-900">{{ $event->title }}</h1>
                @if($event->tagline)
                    <p class="mt-4 text-lg leading-relaxed text-gray-600">{{ $event->tagline }}</p>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700"><span aria-hidden="true">{{ $event->marker_icon }}</span> {{ $event->category ?: 'Event' }}</span>
                    @if($event->is_featured)
                        <span class="event-featured-badge rounded-full bg-accent-500 px-3 py-1 text-xs font-semibold text-white">★ Featured event</span>
                    @endif
                    @if($event->has_passed)
                        <span class="rounded-full bg-forest-800 px-3 py-1 text-xs font-semibold text-white">This event has ended</span>
                    @endif
                </div>

                @if($event->organizer)
                    <div class="mt-8 flex items-center gap-4 rounded-xl bg-gray-50 p-4">
                        <span class="event-accent-bg flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full text-sm font-bold text-white">{{ $hostInitials }}</span>
                        <div>
                            <p class="text-xs text-gray-500">Hosted by</p>
                            <p class="font-semibold text-gray-900">{{ $event->organizer }}</p>
                        </div>
                    </div>
                @endif

                {{-- When & where --}}
                <section class="mt-10">
                    <h2 class="font-sans text-xl font-bold tracking-tight text-forest-900 lg:text-xl">Date and location</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="flex gap-4 rounded-xl border border-gray-200 p-5">
                            <span class="event-accent-soft event-accent-text flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-lg">@include('local-events.partials._icon', ['name' => 'calendar', 'class' => 'h-5 w-5'])</span>
                            <div>
                                <p class="font-semibold text-gray-900"><time datetime="{{ $event->event_date->toDateString() }}">{{ $event->date_range_label }}</time></p>
                                @if($event->time_range_label)<p class="mt-0.5 text-sm text-gray-600">{{ $event->time_range_label }}</p>@endif
                                <a href="{{ route('events.calendar', $event->id) }}" class="event-accent-text mt-2 inline-block text-sm font-semibold hover:underline">Add to Calendar</a>
                            </div>
                        </div>
                        @if($place)
                            <div class="flex gap-4 rounded-xl border border-gray-200 p-5">
                                <span class="event-accent-soft event-accent-text flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-lg">@include('local-events.partials._icon', ['name' => 'pin', 'class' => 'h-5 w-5'])</span>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $place }}</p>
                                    @if($event->venue && $event->location && $event->location !== $event->venue)<p class="mt-0.5 text-sm text-gray-600">{{ $event->location }}</p>@endif
                                    <a href="{{ $directionsUrl }}" target="_blank" rel="noopener" class="event-accent-text mt-2 inline-block text-sm font-semibold hover:underline">Get directions</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                @if($event->description)
                    <section class="mt-12 border-t border-gray-200 pt-10">
                        <h2 class="font-sans text-xl font-bold tracking-tight text-forest-900 lg:text-xl">About this event</h2>
                        <div class="mt-4">
                            @include('local-events.partials._description', ['textClass' => 'text-base leading-7 text-gray-700'])
                        </div>
                    </section>
                @endif

                @if($event->video_url)
                    <section class="mt-12 border-t border-gray-200 pt-10">
                        <h2 class="mb-4 font-sans text-xl font-bold tracking-tight text-forest-900 lg:text-xl">Video</h2>
                        @include('partials.video-embed', ['model' => $event, 'embedId' => 'event-video'])
                    </section>
                @endif

                @if($hasMap || $roamingStops->isNotEmpty())
                    <section class="mt-12 border-t border-gray-200 pt-10">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="font-sans text-xl font-bold tracking-tight text-forest-900 lg:text-xl">What's happening</h2>
                            <p class="text-sm text-gray-500">
                                @if($stopCount){{ $stopCount }} {{ $stopNoun }}@endif
                                @if($event->route_distance_km) · {{ number_format($event->route_distance_km, 1) }} km route @endif
                            </p>
                        </div>
                        @if($hasMap)
                            <div class="mt-5">
                                @include('local-events.partials._map', ['frameClass' => 'h-[320px] rounded-xl border border-gray-200 sm:h-[420px]'])
                            </div>
                        @endif
                        @if($mappedStops->isNotEmpty())
                            <div class="mt-5">
                                @include('local-events.partials._stops', ['listClass' => 'grid gap-3 md:grid-cols-2'])
                            </div>
                        @endif
                        @if($roamingStops->isNotEmpty())
                            <h3 class="mt-8 font-sans text-base font-semibold tracking-normal text-gray-900 lg:text-base">Around downtown</h3>
                            <p class="mb-4 mt-1 text-sm text-gray-500">These happen all around the event — keep an eye out!</p>
                            @include('local-events.partials._roaming', ['listClass' => 'grid gap-3 md:grid-cols-2'])
                        @endif
                    </section>
                @endif


                @if($galleryMedia->isNotEmpty())
                    <div class="mt-12 border-t border-gray-200 pt-10">
                        @include('local-events.partials._gallery', ['headingClass' => 'font-sans text-xl font-bold tracking-tight text-forest-900 lg:text-xl'])
                    </div>
                @endif
            </div>

            {{-- Booking panel --}}
            <aside class="hidden lg:block">
                <div class="sticky top-28 rounded-2xl border border-gray-200 bg-white p-6 shadow-[0_12px_40px_-16px_rgba(15,23,42,0.18)]">
                    <div class="flex items-center gap-4">
                        <div class="w-16 flex-shrink-0 overflow-hidden rounded-xl border border-gray-200 text-center">
                            <p class="event-accent-bg py-1 text-[11px] font-bold uppercase tracking-wider text-white">{{ $event->event_date->format('M') }}</p>
                            <p class="py-1.5 text-2xl font-bold leading-none text-gray-900">{{ $event->event_date->format('j') }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold leading-snug text-gray-900"><time datetime="{{ $event->event_date->toDateString() }}">{{ $event->date_range_label }}</time></p>
                            @if($event->time_range_label)<p class="text-sm text-gray-600">{{ $event->time_range_label }}</p>@endif
                        </div>
                    </div>

                    <dl class="mt-6 space-y-4 border-t border-gray-100 pt-6 text-sm">
                        @if($place)
                            <div class="flex gap-3">
                                <dt class="text-gray-400">@include('local-events.partials._icon', ['name' => 'pin', 'class' => 'h-5 w-5'])<span class="sr-only">Venue</span></dt>
                                <dd><span class="font-medium text-gray-900">{{ $place }}</span>@if($event->venue && $event->location && $event->location !== $event->venue)<span class="block text-gray-500">{{ $event->location }}</span>@endif</dd>
                            </div>
                        @endif
                        @if($event->organizer)
                            <div class="flex gap-3">
                                <dt class="text-gray-400">@include('local-events.partials._icon', ['name' => 'user', 'class' => 'h-5 w-5'])<span class="sr-only">Organizer</span></dt>
                                <dd class="font-medium text-gray-900">{{ $event->organizer }}</dd>
                            </div>
                        @endif
                        @if($stopCount)
                            <div class="flex gap-3">
                                <dt class="text-gray-400">@include('local-events.partials._icon', ['name' => 'list', 'class' => 'h-5 w-5'])<span class="sr-only">Program</span></dt>
                                <dd class="font-medium text-gray-900">{{ $stopCount }} {{ $stopNoun }}@if($event->route_distance_km) · {{ number_format($event->route_distance_km, 1) }} km route @endif</dd>
                            </div>
                        @endif
                    </dl>

                    @if($event->has_passed)
                        <p class="mt-6 rounded-lg bg-gray-100 px-4 py-3 text-sm font-medium text-gray-700">This event has ended.</p>
                    @endif

                    <div class="mt-6 grid gap-3">
                        @if($event->external_url)
                            <a href="{{ $event->external_url }}" target="_blank" rel="noopener"
                                class="event-accent-bg inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3.5 text-[15px] font-semibold text-white">
                                More info
                                @include('local-events.partials._icon', ['name' => 'external'])
                            </a>
                        @endif
                        <a href="{{ route('events.calendar', $event->id) }}"
                            class="{{ $event->external_url ? 'border border-gray-300 text-gray-900 hover:bg-gray-50' : 'event-accent-bg text-white' }} inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3.5 text-[15px] font-semibold">
                            @include('local-events.partials._icon', ['name' => 'calendar'])
                            Add to Calendar
                        </a>
                        <button type="button" data-share-open="event-share" class="inline-flex items-center justify-center gap-2 py-1 text-sm font-semibold text-gray-600 hover:text-gray-900">
                            @include('local-events.partials._icon', ['name' => 'share'])
                            Share with friends
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- Mobile action bar --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] lg:hidden">
        <div class="mx-auto flex max-w-xl items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-gray-900">{{ $event->is_multi_day ? $event->date_range_label : $event->event_date->format('D, M j') }}</p>
                @if($event->time_range_label)<p class="truncate text-xs text-gray-500">{{ $event->time_range_label }}</p>@endif
            </div>
            @if($event->external_url)
                <a href="{{ $event->external_url }}" target="_blank" rel="noopener" class="event-accent-bg inline-flex flex-shrink-0 items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white">
                    More info @include('local-events.partials._icon', ['name' => 'external'])
                </a>
            @else
                <a href="{{ route('events.calendar', $event->id) }}" class="event-accent-bg inline-flex flex-shrink-0 items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white">
                    @include('local-events.partials._icon', ['name' => 'calendar']) Add to Calendar
                </a>
            @endif
        </div>
    </div>
    @include('local-events.partials._christmas-signoff')
</div>

@if($hasMap)
    @include('tours._map-script')
@endif
@include('local-events.partials._share')
@include('local-events.partials._layout-preview-bar', ['barOffset' => 'bottom-24 lg:bottom-4'])
@endsection
