{{--
    A string of Christmas lights (Christmas-themed events only).
    Optional $class: positioning classes for the strip.
--}}
@if($isChristmas)
    @php
        $lightsId = 'xmas-lights-'.Str::random(6);
    @endphp
    <div class="{{ $class ?? 'relative' }} h-8 w-full overflow-hidden" aria-hidden="true">
        <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="{{ $lightsId }}-wire" width="96" height="32" patternUnits="userSpaceOnUse">
                    <path d="M0 3 Q12 11 24 3 Q36 11 48 3 Q60 11 72 3 Q84 11 96 3" fill="none" stroke="#24452F" stroke-width="1.6"/>
                    @foreach([12, 36, 60, 84] as $x)
                        <rect x="{{ $x - 2.5 }}" y="6" width="5" height="4.5" rx="1" fill="#5B6B61"/>
                    @endforeach
                </pattern>
                <pattern id="{{ $lightsId }}-a" width="96" height="32" patternUnits="userSpaceOnUse">
                    <circle cx="12" cy="16.5" r="8" fill="#E0313B" opacity="0.22"/>
                    <ellipse cx="12" cy="16.5" rx="4.2" ry="5.8" fill="#E0313B"/>
                    <circle cx="60" cy="16.5" r="8" fill="#2FA34A" opacity="0.22"/>
                    <ellipse cx="60" cy="16.5" rx="4.2" ry="5.8" fill="#2FA34A"/>
                </pattern>
                <pattern id="{{ $lightsId }}-b" width="96" height="32" patternUnits="userSpaceOnUse">
                    <circle cx="36" cy="16.5" r="8" fill="#F2B705" opacity="0.25"/>
                    <ellipse cx="36" cy="16.5" rx="4.2" ry="5.8" fill="#F2B705"/>
                    <circle cx="84" cy="16.5" r="8" fill="#FFF4D6" opacity="0.3"/>
                    <ellipse cx="84" cy="16.5" rx="4.2" ry="5.8" fill="#FFF4D6" stroke="#E9D9A6" stroke-width="0.8"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#{{ $lightsId }}-wire)"/>
            <rect class="xmas-bulbs-a" width="100%" height="100%" fill="url(#{{ $lightsId }}-a)"/>
            <rect class="xmas-bulbs-b" width="100%" height="100%" fill="url(#{{ $lightsId }}-b)"/>
        </svg>
    </div>
@endif
