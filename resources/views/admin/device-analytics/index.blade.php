@extends('layouts.admin')

@section('title', 'Device Analytics')
@section('page-title', 'Device Analytics')

<link href="https://fonts.bunny.net/css?family=fraunces:400,500,600,700&family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet">

<style>
    .lux {
        --paper: #FAF6EF;
        --paper-2: #F3ECDF;
        --ink: #1B2521;
        --ink-2: #3C4842;
        --muted: #83908A;
        --line: rgba(27,37,33,.09);
        --line-2: rgba(27,37,33,.14);
        --teal: #2C5F5D;
        --teal-br: #4A9B8E;
        --amber: #E87B35;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        color: var(--ink);
        -webkit-font-smoothing: antialiased;
    }
    .lux .display { font-family: 'Fraunces', Georgia, serif; letter-spacing: -.021em; font-optical-sizing: auto; }
    .lux .num { font-variant-numeric: tabular-nums; font-feature-settings: "tnum" 1; }

    .lux .grain {
        position: absolute; inset: 0; z-index: 0; pointer-events: none;
        opacity: .04; mix-blend-mode: multiply;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    }

    .lux .bezel {
        background: linear-gradient(180deg, rgba(255,255,255,.55), rgba(255,255,255,.18));
        border: 1px solid var(--line);
        border-radius: 2rem; padding: .5rem;
        box-shadow: 0 1px 0 rgba(255,255,255,.7) inset, 0 30px 60px -38px rgba(27,37,33,.45), 0 10px 26px -22px rgba(27,37,33,.3);
    }
    .lux .core {
        position: relative; height: 100%;
        background: var(--paper);
        border-radius: calc(2rem - .5rem);
        box-shadow: inset 0 1px 1px rgba(255,255,255,.6), inset 0 0 0 1px rgba(27,37,33,.04);
    }
    .lux .core-teal { background: radial-gradient(120% 140% at 0% 0%, #34706D 0%, #234E4C 55%, #1C4140 100%); color: #EAF3F1; }
    .lux .core-teal .display { color: #fff; }

    .lux .tile {
        background: linear-gradient(180deg, rgba(255,255,255,.5), rgba(255,255,255,.15));
        border: 1px solid var(--line);
        border-radius: 1.4rem;
        box-shadow: 0 14px 34px -28px rgba(27,37,33,.55), inset 0 1px 0 rgba(255,255,255,.5);
        transition: transform .6s cubic-bezier(.16,1,.3,1), box-shadow .6s cubic-bezier(.16,1,.3,1);
    }
    .lux .tile:hover { transform: translateY(-4px); box-shadow: 0 26px 46px -28px rgba(27,37,33,.55), inset 0 1px 0 rgba(255,255,255,.6); }

    .lux .reveal {
        opacity: 0; transform: translateY(2rem); filter: blur(6px);
        transition: opacity .9s cubic-bezier(.16,1,.3,1), transform .9s cubic-bezier(.16,1,.3,1), filter .9s cubic-bezier(.16,1,.3,1);
        transition-delay: var(--d, 0s);
    }
    .lux .reveal.in { opacity: 1; transform: none; filter: blur(0); }

    .lux .bar-fill { transform: scaleX(0); transform-origin: left; transition: transform 1.1s cubic-bezier(.16,1,.3,1); transition-delay: var(--d, 0s); }
    .lux .reveal.in .bar-fill { transform: scaleX(1); }

    .lux .rank {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 999px;
        font-size: .7rem; font-weight: 700; color: var(--teal);
        background: rgba(44,95,93,.09);
    }

    @media (prefers-reduced-motion: reduce) {
        .lux .reveal, .lux .bar-fill { transition: none !important; opacity: 1 !important; transform: none !important; filter: none !important; }
    }
</style>

@section('content')
<div class="lux relative -m-6 min-h-[calc(100dvh-4rem)] px-4 sm:px-6 lg:px-10 py-12 lg:py-16 overflow-hidden"
     style="background:
        radial-gradient(90% 60% at 100% 0%, rgba(74,155,142,.10), transparent 60%),
        radial-gradient(70% 50% at 0% 100%, rgba(232,123,53,.06), transparent 55%),
        var(--paper);">
    <div class="grain"></div>

    <div class="relative max-w-[1400px] mx-auto space-y-16 lg:space-y-24">

        {{-- ══════════════════ OVERVIEW ══════════════════ --}}
        <section class="space-y-8">
            <div class="reveal flex items-center gap-4">
                <h2 class="display text-2xl sm:text-3xl font-normal">Device Analytics</h2>
                <span class="h-px flex-1" style="background:var(--line-2)"></span>
                <span class="text-xs uppercase tracking-[.18em]" style="color:var(--muted)">Trail page visits</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6">
                <div class="reveal bezel" style="--d:.05s">
                    <div class="core core-teal p-8 lg:p-10 flex flex-col justify-between min-h-[220px]">
                        <p class="text-[11px] uppercase tracking-[.16em]" style="color:#AFCFC9">Total Visits</p>
                        <div class="display num text-6xl font-light leading-none mt-4">{{ number_format($totalVisits) }}</div>
                        <p class="mt-4 text-sm" style="color:#AFCFC9">across {{ $trailsWithVisits }} trails</p>
                    </div>
                </div>

                <div class="reveal bezel" style="--d:.12s">
                    <div class="core p-7 lg:p-8">
                        <p class="text-xs uppercase tracking-[.16em] mb-4" style="color:var(--muted)">By Device Type</p>
                        @include('admin.analytics._bar-list', ['data' => $byDeviceType])
                    </div>
                </div>

                <div class="reveal bezel" style="--d:.19s">
                    <div class="core p-7 lg:p-8">
                        <p class="text-xs uppercase tracking-[.16em] mb-4" style="color:var(--muted)">By Platform</p>
                        @include('admin.analytics._bar-list', ['data' => $byPlatform, 'accent' => 'var(--amber), #F0A868'])
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
                <div class="reveal lg:col-span-5 bezel">
                    <div class="core p-7 lg:p-8">
                        <div class="flex items-baseline justify-between mb-6">
                            <h3 class="display text-xl font-normal">By Browser</h3>
                            <span class="num text-xs" style="color:var(--muted)">{{ $totalVisits }} total</span>
                        </div>
                        @include('admin.analytics._bar-list', ['data' => $byBrowser])
                    </div>
                </div>

                {{-- ══════════════════ MOST VISITED TRAILS ══════════════════ --}}
                <div class="reveal lg:col-span-7 bezel" style="--d:.1s">
                    <div class="core p-7 lg:p-8">
                        <div class="flex items-baseline justify-between mb-6">
                            <h3 class="display text-xl font-normal">Most Visited Trails</h3>
                            <span class="text-xs uppercase tracking-[.16em]" style="color:var(--muted)">Top 10</span>
                        </div>
                        <div class="space-y-1">
                            @forelse ($topTrails as $i => $trail)
                                <div class="flex items-center gap-4 py-2.5 {{ ! $loop->last ? 'border-b' : '' }}" style="border-color:var(--line)">
                                    <span class="rank">{{ $i + 1 }}</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate" style="color:var(--ink)">{{ $trail->name }}</p>
                                        <p class="text-xs" style="color:var(--muted)">{{ $trail->location_type === 'fishing_lake' ? 'Fishing Lake' : 'Trail' }}</p>
                                    </div>
                                    <span class="num shrink-0 text-sm font-semibold" style="color:var(--teal)">{{ $trail->visits_count }} <span class="font-normal" style="color:var(--muted)">visits</span></span>
                                </div>
                            @empty
                                <p class="text-sm" style="color:var(--muted)">No trail visits recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const targets = document.querySelectorAll('.lux .reveal');
        if (!('IntersectionObserver' in window)) {
            targets.forEach(el => el.classList.add('in'));
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
        targets.forEach(el => io.observe(el));
    });
</script>
@endpush
