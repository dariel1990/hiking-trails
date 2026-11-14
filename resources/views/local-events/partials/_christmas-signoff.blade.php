{{-- Holiday sign-off at the end of a Christmas-themed event page. Optional $class (spacing above). --}}
@if($isChristmas)
    <div class="xmas-signoff {{ $class ?? 'mt-12 lg:mt-16' }}">
        @include('local-events.partials._christmas-lights')
        <div class="mx-auto max-w-3xl px-4 pb-14 pt-8 text-center">
            <p class="event-title-festive text-4xl font-bold leading-tight md:text-5xl" style="color: var(--tour-primary);">Merry Christmas!</p>
            <p class="mt-3 text-base text-gray-600">Happy holidays from all of us at {{ setting('site_name') }} — see you in Smithers.</p>
        </div>
    </div>
@endif
