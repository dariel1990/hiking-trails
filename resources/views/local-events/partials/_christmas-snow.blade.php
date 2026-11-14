{{--
    Gentle snowfall over a cover photo (Christmas-themed events only).
    Optional $class: stacking classes (e.g. '-z-10' to sit under the hero text).
--}}
@if($isChristmas)
    <div class="xmas-snow absolute inset-0 {{ $class ?? '' }}" aria-hidden="true"></div>
@endif
