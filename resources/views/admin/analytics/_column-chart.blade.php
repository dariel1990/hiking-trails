{{-- Editorial column chart for a 12-month series. GPU-safe: bars grow via scaleY.
     Expects: $series (array<string,int>), optional $accent (css gradient stops). --}}
@php
    $accent = $accent ?? 'var(--teal-br), var(--teal)';
    $max = max(1, ...(count($series) ? array_values($series) : [0]));
    $peak = array_sum($series);
    $i = 0;
@endphp

@if ($peak === 0)
    <div class="flex items-center justify-center h-44 text-sm" style="color:var(--muted)">
        No records in this period yet.
    </div>
@else
    <div class="flex items-end justify-between gap-2 h-44">
        @foreach ($series as $label => $count)
            @php
                $h = $count > 0 ? max(($count / $max) * 100, 4) : 2;
                $delay = ($i++) * 45;
            @endphp
            <div class="group relative flex-1 h-full flex flex-col justify-end items-center">
                <span class="num absolute -top-1 text-[11px] font-semibold opacity-0 -translate-y-1 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500"
                      style="color:var(--ink); transition-timing-function:cubic-bezier(.16,1,.3,1)">{{ $count }}</span>
                <div class="col-bar w-full rounded-t-[7px]"
                     style="height: {{ $h }}%; --d: {{ $delay }}ms; background: linear-gradient(180deg, {{ $accent }}); box-shadow: inset 0 1px 0 rgba(255,255,255,.35);"></div>
            </div>
        @endforeach
    </div>
    <div class="flex justify-between gap-2 mt-3">
        @foreach (array_keys($series) as $label)
            <div class="flex-1 text-center text-[10px] uppercase tracking-wider" style="color:var(--muted)">{{ \Illuminate\Support\Str::before($label, ' ') }}</div>
        @endforeach
    </div>
@endif
