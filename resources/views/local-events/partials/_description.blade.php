{{--
    The event description with a "Read more" toggle when it is long.
    Expects $event. Optional $textClass, $clampClass (e.g. 'line-clamp-8').
--}}
@php
    $clampClass = $clampClass ?? 'line-clamp-8';
@endphp
<div x-data="{ open: false, clamped: false }"
    x-init="$nextTick(() => clamped = $refs.desc.scrollHeight > $refs.desc.clientHeight + 1)">
    <p x-ref="desc" :class="{ '{{ $clampClass }}': !open }"
        class="whitespace-pre-line {{ $clampClass }} {{ $textClass ?? 'text-base leading-relaxed text-gray-700' }}">{{ $event->description }}</p>
    <button type="button" x-show="clamped" x-cloak @click="open = !open"
        class="event-accent-text mt-3 text-sm font-semibold hover:underline"
        x-text="open ? 'Show less' : 'Read more'"></button>
</div>
