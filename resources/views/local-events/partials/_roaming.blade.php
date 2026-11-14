{{--
    Roaming activities (no fixed spot), listed without a map pin.
    Expects $roamingStops. Optional $listClass, $titleClass (see _stops).
--}}
@php($titleClass = $titleClass ?? 'font-sans text-[15px] font-semibold tracking-normal')
<ul class="{{ $listClass ?? 'grid gap-3' }}">
    @foreach($roamingStops as $stop)
        <li class="flex gap-4 rounded-xl border border-dashed border-gray-300 bg-white p-4">
            @include('tours._stop-badge', ['stop' => $stop, 'number' => null, 'inline' => true])
            <div class="min-w-0 flex-1">
                <h3 class="{{ $titleClass }} text-gray-900">{{ $stop->displayName() }}</h3>
                @if($stop->time_label)
                    <p class="event-accent-text mt-0.5 text-xs font-semibold">{{ $stop->time_label }}</p>
                @endif
                @if($stop->description)
                    <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $stop->description }}</p>
                @endif
                @if($stop->notes)
                    <p class="mt-1 text-sm text-gray-500">{{ $stop->notes }}</p>
                @endif
            </div>
            @if($stop->image_url)
                <img src="{{ $stop->image_url }}" alt="{{ $stop->displayName() }}" loading="lazy"
                    class="h-16 w-16 flex-shrink-0 rounded-lg bg-gray-100 object-cover">
            @endif
        </li>
    @endforeach
</ul>
