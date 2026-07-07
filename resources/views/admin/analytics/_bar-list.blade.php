{{-- Editorial horizontal bar list. GPU-safe: fills grow via scaleX.
     Expects: $data (array<string,int>), optional $labels (array<string,string>),
     optional $accent (css gradient stops). --}}
@php
    $labels = $labels ?? [];
    $accent = $accent ?? 'var(--teal), var(--teal-br)';
    $max = max(1, ...(count($data) ? array_values($data) : [0]));
    $total = array_sum($data);
    $i = 0;
@endphp

<div class="space-y-4">
    @forelse ($data as $key => $count)
        @php
            $label = $labels[$key] ?? \Illuminate\Support\Str::of((string) $key)->replace('_', ' ')->title();
            $pct = $max > 0 ? max(($count / $max) * 100, $count > 0 ? 5 : 0) : 0;
            $share = $total > 0 ? round(($count / $total) * 100) : 0;
            $delay = ($i++) * 70;
        @endphp
        <div>
            <div class="flex items-baseline justify-between text-sm mb-2">
                <span class="truncate pr-2" style="color:var(--ink-2)">{{ $label }}</span>
                <span class="num shrink-0 font-semibold" style="color:var(--ink)">{{ $count }}<span class="font-normal" style="color:var(--muted)"> · {{ $share }}%</span></span>
            </div>
            <div class="h-[7px] rounded-full overflow-hidden" style="background:var(--line-2)">
                <div class="bar-fill h-full rounded-full" style="width: {{ $pct }}%; --d: {{ $delay }}ms; background: linear-gradient(90deg, {{ $accent }});"></div>
            </div>
        </div>
    @empty
        <p class="text-sm" style="color:var(--muted)">No data yet.</p>
    @endforelse
</div>
