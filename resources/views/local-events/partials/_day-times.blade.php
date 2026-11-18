{{--
    Hours per day for events whose hours differ by day ($event->day_times).

    Variables:
      $style       'pills' (one rounded chip per day, for hero banners) or 'list' (stacked lines)
      $pillClass   chip colours for 'pills' (default: light on dark)
      $mutedClass  colour of the hours in 'list' (default: gray)
--}}
@php $style ??= 'list'; @endphp

@if($style === 'pills')
    <ul class="flex flex-wrap items-center justify-center gap-2" aria-label="Hours by day">
        @foreach($event->day_times as $day)
            <li class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm {{ $pillClass ?? 'bg-white/10 text-white ring-1 ring-inset ring-white/20 backdrop-blur-sm' }}">
                <time datetime="{{ $day['date'] }}" class="font-semibold">{{ $day['weekday'] }}, {{ $day['short_date'] }}</time>
                <span aria-hidden="true" class="opacity-40">·</span>
                <span class="tabular-nums">{{ $day['time'] }}</span>
            </li>
        @endforeach
    </ul>
@else
    {{-- Stacked (day, then its hours) so it fits narrow fact boxes without breaking a time apart --}}
    <ul class="space-y-2" aria-label="Hours by day">
        @foreach($event->day_times as $day)
            <li class="leading-snug">
                <time datetime="{{ $day['date'] }}" class="block whitespace-nowrap">{{ $day['weekday'] }}, {{ $day['short_date'] }}</time>
                <span class="block whitespace-nowrap tabular-nums text-[0.9em] font-normal {{ $mutedClass ?? 'text-gray-600' }}">{{ $day['time'] }}</span>
            </li>
        @endforeach
    </ul>
@endif
