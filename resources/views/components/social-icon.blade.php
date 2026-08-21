@props(['icon' => 'link'])

@php
    $glyph = config("social-icons.{$icon}") ?? config('social-icons.link') ?? [];
@endphp

@if (! empty($glyph['path']))
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
    <path
        d="{{ $glyph['path'] }}"
        @if (! empty($glyph['fill_rule']))
            fill-rule="{{ $glyph['fill_rule'] }}" clip-rule="{{ $glyph['fill_rule'] }}"
        @endif
    />
</svg>
@endif
