@extends('layouts.admin')

@section('title', 'Analytics')
@section('page-title', 'Analytics')

@php
    $productLabels = [
        'xs_offline_monthly' => 'Play · Monthly',
        'xs_offline_annual' => 'Play · Annual',
        'xs_pro_web_monthly' => 'Web · Monthly',
        'xs_pro_web_annual' => 'Web · Annual',
    ];
    $convDeg = min(360, ($subscriptions['conversion_rate'] / 100) * 360);
@endphp

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

    /* Grain — scoped, absolute, non-interactive, no scroll repaint */
    .lux .grain {
        position: absolute; inset: 0; z-index: 0; pointer-events: none;
        opacity: .04; mix-blend-mode: multiply;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    }

    /* Double-bezel: aluminium tray + glass plate */
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

    .lux .eyebrow {
        display: inline-flex; align-items: center; gap: .5rem;
        border-radius: 999px; padding: .3rem .75rem;
        font-size: 10px; letter-spacing: .2em; text-transform: uppercase; font-weight: 600;
        color: var(--teal); background: rgba(44,95,93,.07); border: 1px solid rgba(44,95,93,.16);
    }

    /* Island CTA — button-in-button */
    .lux .island {
        display: inline-flex; align-items: center; gap: .75rem;
        border-radius: 999px; padding: .5rem .55rem .5rem 1.35rem;
        font-weight: 600; font-size: .9rem; color: #EAF3F1;
        background: linear-gradient(180deg, #34706D, #234E4C);
        box-shadow: 0 14px 30px -16px rgba(27,37,33,.6), inset 0 1px 0 rgba(255,255,255,.18);
        transition: transform .5s cubic-bezier(.32,.72,0,1), box-shadow .5s cubic-bezier(.32,.72,0,1);
    }
    .lux .island:hover { box-shadow: 0 22px 40px -16px rgba(27,37,33,.7), inset 0 1px 0 rgba(255,255,255,.22); }
    .lux .island:active { transform: scale(.975); }
    .lux .island .knob {
        display: flex; align-items: center; justify-content: center;
        width: 2rem; height: 2rem; border-radius: 999px; background: rgba(255,255,255,.14);
        transition: transform .5s cubic-bezier(.32,.72,0,1);
    }
    .lux .island:hover .knob { transform: translate(3px,-1px) scale(1.06); }

    /* Scroll reveal */
    .lux .reveal {
        opacity: 0; transform: translateY(2rem); filter: blur(6px);
        transition: opacity .9s cubic-bezier(.16,1,.3,1), transform .9s cubic-bezier(.16,1,.3,1), filter .9s cubic-bezier(.16,1,.3,1);
        transition-delay: var(--d, 0s);
    }
    .lux .reveal.in { opacity: 1; transform: none; filter: blur(0); }

    /* Chart growth (children animate once ancestor .reveal gains .in) */
    .lux .col-bar { transform: scaleY(0); transform-origin: bottom; transition: transform 1s cubic-bezier(.16,1,.3,1); transition-delay: var(--d, 0s); }
    .lux .reveal.in .col-bar { transform: scaleY(1); }
    .lux .bar-fill { transform: scaleX(0); transform-origin: left; transition: transform 1.1s cubic-bezier(.16,1,.3,1); transition-delay: var(--d, 0s); }
    .lux .reveal.in .bar-fill { transform: scaleX(1); }

    .lux .conv-ring { background: conic-gradient(var(--teal-br) var(--deg), var(--line-2) 0); }

    @media (prefers-reduced-motion: reduce) {
        .lux .reveal, .lux .col-bar, .lux .bar-fill { transition: none !important; opacity: 1 !important; transform: none !important; filter: none !important; }
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

        {{-- ══════════════════ SUBSCRIPTIONS ══════════════════ --}}
        <section class="space-y-8">
            <div class="reveal flex items-center gap-4">
                <h2 class="display text-2xl sm:text-3xl font-normal">Subscriptions</h2>
                <span class="h-px flex-1" style="background:var(--line-2)"></span>
                <a href="{{ route('admin.subscriptions.index') }}" class="island group shrink-0">
                    Manage
                    <span class="knob">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H8M17 7v9"/>
                        </svg>
                    </span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 lg:gap-6">
                {{-- Hero MRR — col-span-6, row-span-2 --}}
                <div class="reveal md:col-span-6 md:row-span-2 bezel" style="--d:.05s">
                    <div class="core core-teal p-8 lg:p-10 flex flex-col justify-between min-h-[300px]">
                        <div class="flex items-start justify-between">
                            <span class="eyebrow" style="color:#CFE7E2; background:rgba(255,255,255,.08); border-color:rgba(255,255,255,.16)">Est. Monthly Recurring</span>
                            <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8M21 7h-5m5 0v5"/>
                            </svg>
                        </div>
                        <div class="mt-8">
                            <div class="display num text-6xl lg:text-7xl font-light leading-none">
                                <span class="text-3xl align-top opacity-60">$</span>{{ number_format($subscriptions['mrr'], 2) }}
                            </div>
                            <p class="mt-3 text-sm" style="color:#AFCFC9">CAD · estimated from active entitlements</p>
                        </div>
                        <div class="mt-8 flex flex-wrap items-center gap-2.5">
                            <span class="num inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-semibold" style="background:rgba(255,255,255,.1)">
                                ${{ number_format($subscriptions['arr'], 0) }} <span class="font-normal opacity-70">ARR</span>
                            </span>
                            <span class="num inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm" style="background:rgba(255,255,255,.1)">
                                {{ $subscriptions['active_monthly'] }} <span class="opacity-70">monthly</span>
                            </span>
                            <span class="num inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm" style="background:rgba(255,255,255,.1)">
                                {{ $subscriptions['active_annual'] }} <span class="opacity-70">annual</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Active subs --}}
                <div class="reveal md:col-span-3 bezel" style="--d:.12s">
                    <div class="core p-6">
                        <p class="text-[11px] uppercase tracking-[.16em]" style="color:var(--muted)">Active</p>
                        <div class="num display text-5xl font-light mt-3">{{ $subscriptions['active'] }}</div>
                        <div class="mt-5 space-y-2 text-xs" style="color:var(--ink-2)">
                            <div class="flex items-center justify-between"><span>Android</span><span class="num font-semibold">{{ $subscriptions['android'] }}</span></div>
                            <div class="flex items-center justify-between"><span>Web</span><span class="num font-semibold">{{ $subscriptions['web'] }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Conversion ring --}}
                <div class="reveal md:col-span-3 bezel" style="--d:.19s">
                    <div class="core p-6 flex items-center gap-5">
                        <div class="conv-ring relative shrink-0 w-16 h-16 rounded-full" style="--deg:{{ $convDeg }}deg">
                            <div class="absolute inset-[5px] rounded-full flex items-center justify-center" style="background:var(--paper)">
                                <span class="num text-sm font-bold" style="color:var(--teal)">{{ round($subscriptions['conversion_rate']) }}%</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase tracking-[.16em]" style="color:var(--muted)">Free → Pro</p>
                            <p class="text-sm mt-1 leading-snug" style="color:var(--ink-2)">of users hold an entitlement</p>
                        </div>
                    </div>
                </div>

                {{-- Expiring --}}
                <div class="reveal md:col-span-3 bezel" style="--d:.26s">
                    <div class="core p-6">
                        <p class="text-[11px] uppercase tracking-[.16em]" style="color:var(--muted)">Expiring ≤ 7d</p>
                        <div class="num display text-5xl font-light mt-3" style="color:{{ $subscriptions['expiring_soon'] > 0 ? 'var(--amber)' : 'var(--ink)' }}">{{ $subscriptions['expiring_soon'] }}</div>
                        <p class="mt-4 text-xs" style="color:var(--muted)">of {{ $subscriptions['active'] }} active renewals</p>
                    </div>
                </div>

                {{-- Lifetime --}}
                <div class="reveal md:col-span-3 bezel" style="--d:.33s">
                    <div class="core p-6">
                        <p class="text-[11px] uppercase tracking-[.16em]" style="color:var(--muted)">Lifetime</p>
                        <div class="num display text-5xl font-light mt-3">{{ $subscriptions['total'] }}</div>
                        <p class="mt-4 text-xs" style="color:var(--muted)">subscriptions, any status</p>
                    </div>
                </div>
            </div>

            {{-- Charts row --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
                <div class="reveal lg:col-span-8 bezel">
                    <div class="core p-7 lg:p-8">
                        <div class="flex items-baseline justify-between mb-8">
                            <h3 class="display text-xl font-normal">New Subscriptions</h3>
                            <span class="text-xs uppercase tracking-[.16em]" style="color:var(--muted)">Trailing 12 months</span>
                        </div>
                        @include('admin.analytics._column-chart', ['series' => $subscriptions['new_by_month']])
                    </div>
                </div>
                <div class="reveal lg:col-span-4 bezel" style="--d:.1s">
                    <div class="core p-7 lg:p-8 space-y-7">
                        <div>
                            <p class="text-xs uppercase tracking-[.16em] mb-4" style="color:var(--muted)">By status</p>
                            @include('admin.analytics._bar-list', ['data' => $subscriptions['by_status']])
                        </div>
                        <div class="h-px" style="background:var(--line)"></div>
                        <div>
                            <p class="text-xs uppercase tracking-[.16em] mb-4" style="color:var(--muted)">By product</p>
                            @include('admin.analytics._bar-list', ['data' => $subscriptions['by_product'], 'labels' => $productLabels, 'accent' => 'var(--amber), #F0A868'])
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════ USERS ══════════════════ --}}
        <section class="space-y-8">
            <div class="reveal flex items-center gap-4">
                <h2 class="display text-2xl sm:text-3xl font-normal">Members</h2>
                <span class="h-px flex-1" style="background:var(--line-2)"></span>
                <span class="text-xs uppercase tracking-[.18em]" style="color:var(--muted)">Accounts &amp; growth</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
                <div class="reveal lg:col-span-4 bezel">
                    <div class="core p-7 lg:p-8 flex flex-col justify-between min-h-[220px]">
                        <p class="text-[11px] uppercase tracking-[.16em]" style="color:var(--muted)">Total Users</p>
                        <div class="num display text-6xl font-light">{{ $users['total'] }}</div>
                        <div class="flex gap-3 text-xs">
                            <span class="rounded-full px-3 py-1.5" style="background:var(--paper-2); color:var(--ink-2)">{{ $users['members'] }} members</span>
                            <span class="rounded-full px-3 py-1.5" style="background:var(--paper-2); color:var(--ink-2)">{{ $users['admins'] }} admins</span>
                        </div>
                    </div>
                </div>
                <div class="reveal lg:col-span-8 bezel" style="--d:.1s">
                    <div class="core p-7 lg:p-8">
                        <div class="flex items-baseline justify-between mb-8">
                            <h3 class="display text-xl font-normal">New Members</h3>
                            <span class="text-xs uppercase tracking-[.16em]" style="color:var(--muted)">Trailing 12 months</span>
                        </div>
                        @include('admin.analytics._column-chart', ['series' => $users['new_by_month'], 'accent' => 'var(--amber), #D9691F'])
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════ CONTENT ══════════════════ --}}
        <section class="space-y-8">
            <div class="reveal flex items-center gap-4">
                <h2 class="display text-2xl sm:text-3xl font-normal">The Library</h2>
                <span class="h-px flex-1" style="background:var(--line-2)"></span>
                <span class="text-xs uppercase tracking-[.18em]" style="color:var(--muted)">Everything published</span>
            </div>

            {{-- Count tiles --}}
            <div class="reveal grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-5">
                @foreach ([
                    ['label' => 'Trails', 'value' => $content['trails'], 'sub' => $content['trails_active'].' active · '.$content['trails_closed'].' closed'],
                    ['label' => 'Fishing Lakes', 'value' => $content['fishing_lakes'], 'sub' => 'point locations'],
                    ['label' => 'Businesses', 'value' => $content['businesses'], 'sub' => $content['businesses_active'].' active · '.$content['businesses_featured'].' featured'],
                    ['label' => 'Facilities', 'value' => $content['facilities'], 'sub' => 'points of interest'],
                    ['label' => 'Trail Networks', 'value' => $content['networks'], 'sub' => 'grouped areas'],
                    ['label' => 'Tours', 'value' => $content['tours'], 'sub' => 'guided routes'],
                    ['label' => 'Events', 'value' => $content['events'], 'sub' => 'scraped + manual'],
                    ['label' => 'Media Files', 'value' => $content['media'], 'sub' => $content['photos_approved'].' community photos'],
                ] as $ti => $tile)
                    <div class="tile p-6" style="transition-delay: {{ $ti * 40 }}ms">
                        <p class="text-[11px] uppercase tracking-[.16em]" style="color:var(--muted)">{{ $tile['label'] }}</p>
                        <div class="num display text-4xl font-light mt-2">{{ $tile['value'] }}</div>
                        <p class="mt-2 text-xs leading-snug" style="color:var(--muted)">{{ $tile['sub'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Distributions --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
                <div class="reveal lg:col-span-5 bezel">
                    <div class="core p-7 lg:p-8">
                        <div class="flex items-baseline justify-between mb-6">
                            <h3 class="display text-xl font-normal">By Difficulty</h3>
                            <span class="num text-xs" style="color:var(--muted)">{{ $content['trails_total'] }} total</span>
                        </div>
                        @include('admin.analytics._bar-list', ['data' => $content['by_difficulty']])
                    </div>
                </div>
                <div class="reveal lg:col-span-7 bezel" style="--d:.1s">
                    <div class="core p-7 lg:p-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div>
                            <h3 class="display text-xl font-normal mb-6">Data Source</h3>
                            @include('admin.analytics._bar-list', ['data' => [
                                'GPX' => $content['by_source']['gpx'],
                                'Manual' => $content['by_source']['manual'],
                                'Mixed' => $content['by_source']['mixed'],
                            ]])
                        </div>
                        <div>
                            <h3 class="display text-xl font-normal mb-6">Business Types</h3>
                            @include('admin.analytics._bar-list', ['data' => $content['by_business_type'], 'accent' => 'var(--amber), #F0A868'])
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <p class="reveal text-xs pt-4" style="color:var(--muted)">
            Revenue figures are estimates based on baseline CAD pricing ($4.99&#8202;/&#8202;mo, $39.99&#8202;/&#8202;yr). Actual charges vary by region.
        </p>
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
