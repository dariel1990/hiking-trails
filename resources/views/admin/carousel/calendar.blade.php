@extends('layouts.admin')

@section('title', 'Slide Calendar Details')
@section('page-title', 'Slide Calendar Details')

@section('content')
@php
    $inputClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';
    // A slide not yet on the calendar starts from its slideshow window, to save typing.
    $hasCalendarDate = $slide->calendar_date !== null;
@endphp

<div class="px-4 lg:px-8 py-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.carousel.index') }}" aria-label="Back to carousel slides"
           class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10 shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="w-24 h-14 rounded-lg overflow-hidden bg-gray-100 shrink-0 border border-gray-200">
            <img src="{{ $slide->url }}" alt="{{ $slide->caption }}" class="w-full h-full object-cover">
        </div>
        <div class="space-y-1 min-w-0">
            <h2 class="text-2xl font-semibold tracking-tight">Events calendar</h2>
            <p class="text-sm text-muted-foreground truncate">{{ $slide->caption }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @if(! $slide->is_active)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 text-sm">
            @if($slide->calendar_ended_at)
                This slide was switched off automatically after its event finished, so it is hidden from the slideshow and the events calendar.
            @else
                This slide is switched off, so it is hidden from the slideshow and the events calendar.
            @endif
            Turn <strong>Active</strong> back on for it on the <a href="{{ route('admin.carousel.index', ['status' => 'inactive']) }}" class="underline font-medium">carousel page</a> to show it again.
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm" role="alert">
            @foreach($errors->all() as $message)
                <p>{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.carousel.calendar.update', $slide) }}" method="POST" class="space-y-6 max-w-4xl"
          x-data="{ onCalendar: @js((bool) old('show_on_calendar', $slide->show_on_calendar)) }">
        @csrf
        @method('PUT')

        {{-- On / off --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-5">
            <input type="hidden" name="show_on_calendar" value="0">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="show_on_calendar" value="1" x-model="onCalendar"
                       class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="space-y-1">
                    <span class="block text-sm font-medium">Show on the events calendar</span>
                    <span class="block text-sm text-muted-foreground">
                        Adds this slide to the calendar on the Events page on the dates below; clicking it shows these details.
                        The slideshow is not affected. The entry stays on the calendar after the slide stops showing in the slideshow.
                    </span>
                </span>
            </label>
        </div>

        {{-- Disabled (and so not sent or required) while the calendar is off --}}
        <fieldset :disabled="!onCalendar" class="space-y-6 transition-opacity" :class="onCalendar ? '' : 'opacity-50'">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="px-5 py-4 border-b">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Details</h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5 sm:col-span-2">
                        <label for="calendar_title" class="text-sm font-medium leading-none">Calendar title</label>
                        <input type="text" name="calendar_title" id="calendar_title" maxlength="255"
                               value="{{ old('calendar_title', $slide->calendar_display_title) }}"
                               class="{{ $inputClass }}">
                        <p class="text-xs text-muted-foreground">Filled in from the slide caption. Change it to show a different title on the calendar, or clear it to go back to the caption.</p>
                    </div>
                    <div class="space-y-1.5">
                        <label for="calendar_category" class="text-sm font-medium leading-none">Category</label>
                        <input type="text" name="calendar_category" id="calendar_category" maxlength="100" list="calendar-categories"
                               value="{{ old('calendar_category', $slide->calendar_category) }}"
                               placeholder="e.g., Community" class="{{ $inputClass }}">
                        <datalist id="calendar-categories">
                            @foreach($categories as $category)
                                <option value="{{ $category }}"></option>
                            @endforeach
                        </datalist>
                        <p class="text-xs text-muted-foreground">Shown as a badge on top of the event and used by the category filter.</p>
                    </div>
                    <div class="space-y-1.5">
                        <label for="calendar_location" class="text-sm font-medium leading-none">Location</label>
                        <input type="text" name="calendar_location" id="calendar_location" maxlength="255"
                               value="{{ old('calendar_location', $slide->calendar_location) }}"
                               placeholder="e.g., Main Street, Smithers" class="{{ $inputClass }}">
                    </div>
                    <div class="space-y-1.5">
                        <label for="calendar_url" class="text-sm font-medium leading-none">More info link</label>
                        <input type="url" name="calendar_url" id="calendar_url" maxlength="500"
                               value="{{ old('calendar_url', $slide->calendar_url) }}"
                               placeholder="https://" class="{{ $inputClass }}">
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label for="calendar_description" class="text-sm font-medium leading-none">Description</label>
                        <textarea name="calendar_description" id="calendar_description" rows="5" maxlength="5000"
                                  class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">{{ old('calendar_description', $slide->calendar_description) }}</textarea>
                    </div>
                </div>
            </div>

            @include('admin.partials.schedule-days', [
                'scheduleCardId' => 'slide-when',
                'scheduleHint' => 'When the event happens. This can differ from when the slide shows in the slideshow.',
                'scheduleValues' => [
                    'event_date' => ($hasCalendarDate ? $slide->calendar_date : $slide->starts_at)?->toDateString() ?? '',
                    'end_date' => ($hasCalendarDate ? $slide->calendar_end_date : $slide->ends_at)?->toDateString() ?? '',
                    'event_time' => $slide->calendar_start_time?->format('H:i') ?? '',
                    'end_time' => $slide->calendar_end_time?->format('H:i') ?? '',
                ],
                'scheduleExistingDays' => $slide->days,
            ])
        </fieldset>

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.carousel.index') }}"
               class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                Back
            </a>
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                Save calendar details
            </button>
        </div>
    </form>
</div>
@endsection
