{{--
    Round badge for a stop in the tour page's lists: the stop's own icon
    (image or emoji) when it has one, otherwise its number.
    Expects $stop and $number (int|null). Optional $inline (bool): sit in a flex row
    instead of being absolutely positioned on the timeline.
--}}
@php
    $inline = $inline ?? false;
    $positionClasses = $inline ? 'relative flex-shrink-0' : 'absolute left-0 top-0';
@endphp
@if($stop->hasIcon())
    <div class="{{ $positionClasses }} flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-white shadow-md border-2 ring-2 ring-gray-100"
        style="border-color: var(--tour-primary);">
        @if($stop->icon_image_url)
            <img src="{{ $stop->icon_image_url }}" alt="" class="h-full w-full object-contain p-1">
        @else
            <span class="text-xl leading-none" aria-hidden="true">{{ $stop->icon }}</span>
        @endif
    </div>
@else
    <div class="stop-number-badge {{ $positionClasses }} flex h-11 w-11 items-center justify-center rounded-full text-white text-sm font-bold shadow-md border-2 border-white ring-2 ring-gray-100">
        {{ $number ?? '•' }}
    </div>
@endif
