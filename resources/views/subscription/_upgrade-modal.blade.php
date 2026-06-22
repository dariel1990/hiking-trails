{{-- XploreSmithers Pro upgrade modal, shown on any page when a non-subscriber taps a
     gated feature, or clicks "Go Pro" in the header. Triggered imperatively via
     window.xsShowProModal(featureKey). Mirrors the design of the full /pro page
     (resources/views/subscription/pro.blade.php), compacted into a scrollable dialog.
     Styles are inlined (not @push'd) because this partial is included at the end of
     the body, after the head's @stack('styles') has already rendered. --}}
@php
    $modalIsPro = (bool) auth()->user()?->hasActiveProEntitlement();
@endphp

@unless($modalIsPro)
<style>
    @keyframes xsProFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes xsProSlideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes xsProSlideUpDesktop { from { transform: translateY(36px) scale(.98); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

    .xs-pro-modal { position: fixed; inset: 0; z-index: 10000; display: none; align-items: flex-end; justify-content: center; pointer-events: none; }
    .xs-pro-modal.is-visible { display: flex; }
    .xs-pro-modal.is-open { pointer-events: auto; }
    @media (min-width: 640px) { .xs-pro-modal { align-items: center; padding: 24px; } }

    .xs-pro-backdrop { position: absolute; inset: 0; background: rgba(10, 20, 19, 0.75); backdrop-filter: blur(4px); opacity: 0; }
    .xs-pro-modal.is-open .xs-pro-backdrop { animation: xsProFadeIn 0.5s ease forwards; }

    .xs-pro-dialog {
        position: relative; width: 100%; max-width: 680px; max-height: 92vh; overflow-y: auto;
        background: #0d1c1b; color: #fff; border-radius: 24px 24px 0 0;
        box-shadow: 0 -20px 60px rgba(0,0,0,.35);
        transform: translateY(100%); opacity: 0;
        scrollbar-width: thin; scrollbar-color: #2C5F5D rgba(255,255,255,.04);
    }
    .xs-pro-dialog::-webkit-scrollbar { width: 9px; }
    .xs-pro-dialog::-webkit-scrollbar-track { background: rgba(255,255,255,.04); }
    .xs-pro-dialog::-webkit-scrollbar-thumb { background: #2C5F5D; border-radius: 999px; border: 2px solid #0d1c1b; }
    .xs-pro-dialog::-webkit-scrollbar-thumb:hover { background: #E87B35; }
    .xs-pro-dialog::-webkit-scrollbar-button { display: none; width: 0; height: 0; }
    .xs-pro-modal.is-open .xs-pro-dialog { animation: xsProSlideUp 0.5s cubic-bezier(.22,1,.36,1) forwards; }
    @media (min-width: 640px) {
        .xs-pro-dialog { border-radius: 24px; transform: translateY(36px) scale(.98); box-shadow: 0 30px 70px rgba(0,0,0,.4); max-height: 88vh; }
        .xs-pro-modal.is-open .xs-pro-dialog { animation: xsProSlideUpDesktop 0.5s cubic-bezier(.22,1,.36,1) forwards; }
    }
    @media (min-width: 900px) { .xs-pro-dialog { max-width: 60vw; } }
    @media (prefers-reduced-motion: reduce) {
        .xs-pro-modal.is-open .xs-pro-backdrop,
        .xs-pro-modal.is-open .xs-pro-dialog { animation: none; opacity: 1; transform: none; }
    }

    /* Header */
    .xs-pro-head { position: relative; padding: 34px 24px 28px; text-align: center; color: #fff; overflow: hidden; }
    .xs-pro-head-bg { position: absolute; inset: 0; pointer-events: none;
        background: url("{{ asset('images/login-background.jpg') }}") center 30% / cover no-repeat;
        filter: blur(2px) brightness(0.52); transform: scale(1.04); }
    .xs-pro-head-scrim { position: absolute; inset: 0; pointer-events: none;
        background: linear-gradient(180deg, rgba(10,20,19,.45) 0%, rgba(13,28,27,.92) 100%); }
    .xs-pro-logo { position: relative; height: 80px; width: auto; max-width: 180px; object-fit: contain; margin: 0 auto 14px; display: block; }
    .xs-pro-title { position: relative; font-size: 30px; font-weight: 800; letter-spacing: -0.02em; line-height: 1; }
    .xs-pro-sub { position: relative; margin-top: 10px; font-size: 11px; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: #86efac; }
    .xs-pro-x { position: absolute; top: 14px; right: 16px; width: 32px; height: 32px; border-radius: 999px;
        background: rgba(255,255,255,.15); color: #fff; font-size: 20px; line-height: 1; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s; z-index: 2; }
    .xs-pro-x:hover { background: rgba(255,255,255,.28); }

    /* Body */
    .xs-pro-body { padding: 22px 20px 28px; }

    /* Later / foot */
    .xs-pro-later { display: block; width: 100%; text-align: center; background: transparent; color: rgba(255,255,255,.45);
        font-size: 12.5px; padding: 14px 6px 0; border: 0; cursor: pointer; }
    .xs-pro-foot { text-align: center; font-size: 11px; color: rgba(255,255,255,.3); margin-top: 10px; }
    .xs-pro-foot a { text-decoration: underline; }
    .xs-pro-foot a:hover { color: rgba(255,255,255,.55); }
</style>

<div id="xs-pro-modal" class="xs-pro-modal" aria-hidden="true">
    <div class="xs-pro-backdrop" data-xs-pro-close></div>
    <div class="xs-pro-dialog" role="dialog" aria-modal="true" aria-labelledby="xs-pro-title">

        {{-- Header with background image matching /pro page --}}
        <div class="xs-pro-head">
            <div class="xs-pro-head-bg"></div>
            <div class="xs-pro-head-scrim"></div>
            <button type="button" class="xs-pro-x" data-xs-pro-close aria-label="Close">&times;</button>
            <img src="{{ asset('images/xploresmithers_white.png') }}" alt="XploreSmithers" class="xs-pro-logo">
            <h2 id="xs-pro-title" class="xs-pro-title" data-xs-pro-title>
                <span style="color:#ffffff">Free or </span><span style="color:#E87B35">Pro?</span>
            </h2>
            <p class="xs-pro-sub" data-xs-pro-sub>More trails. More adventures. More you.</p>
        </div>

        {{-- Body --}}
        <div class="xs-pro-body">
            @php
                $modalFeatures      = \App\Services\ProFeatureCatalog::all();
                $modalPriceMonthly  = config('services.stripe.price_monthly', '4.99');
                $modalPriceAnnual   = config('services.stripe.price_annual', '39.99');
                $modalTrialDays     = (int) config('services.stripe.trial_days', 7);
                $modalStripeEnabled = (bool) config('services.stripe.enabled');
            @endphp

            {{-- Feature comparison table — same 3-column grid layout as /pro page --}}
            <div class="rounded-2xl ring-1 ring-white/10 overflow-hidden">

                {{-- Desktop: 3-column grid --}}
                <div class="hidden sm:grid grid-cols-[1fr_200px_220px] bg-white/[0.03]">
                    <div class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-wider text-white/50">Features</div>
                    <div class="px-3 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-white/50 border-l border-white/10">Free</div>
                    <div class="px-3 py-3.5 text-center text-[10px] font-semibold uppercase tracking-wider text-white border-l border-white/10 bg-forest-600/40 flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-accent-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 16L3 6l5 3 2-5 2 5 5-3-2 10H5zm0 2h10v1a1 1 0 01-1 1H6a1 1 0 01-1-1v-1z"/>
                        </svg>
                        Pro
                    </div>

                    @foreach($modalFeatures as $feature)
                        <div class="px-5 py-4 border-t border-white/10 flex items-center gap-3">
                            <span class="flex-shrink-0 w-9 h-9 rounded-full bg-primary-500/15 flex items-center justify-center">
                                <svg class="w-4 h-4 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feature['icon'] }}"/>
                                </svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm text-white">{{ $feature['label'] }}</p>
                                <p class="text-xs text-white/45">{{ $feature['sub'] }}</p>
                            </div>
                        </div>
                        <div class="px-3 border-t border-l border-white/10 flex items-center justify-center gap-1.5">
                            @if($feature['free']['available'])
                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-4 h-4 text-white/30 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                            <span class="text-xs text-white/55">{{ $feature['free']['text'] }}</span>
                        </div>
                        <div class="px-3 border-t border-l border-white/10 bg-forest-600/15 flex flex-col justify-center gap-0.5 py-3">
                            <span class="inline-flex gap-1.5">
                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-xs font-medium text-white/85">{{ $feature['pro']['text'] }}</span>
                            </span>
                            @if($feature['pro']['badge'])
                                <span class="text-[10px] font-semibold text-white/45">{{ $feature['pro']['badge'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Mobile: stacked rows --}}
                <div class="sm:hidden divide-y divide-white/10 bg-white/[0.03]">
                    @foreach($modalFeatures as $feature)
                        <div class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex-shrink-0 w-9 h-9 rounded-full bg-primary-500/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feature['icon'] }}"/>
                                    </svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-white">{{ $feature['label'] }}</p>
                                    <p class="text-xs text-white/45">{{ $feature['sub'] }}</p>
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 pl-12">
                                <div class="flex items-center gap-1.5">
                                    @if($feature['free']['available'])
                                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-white/30 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                    <span class="text-xs text-white/55">{{ $feature['free']['text'] }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-xs font-medium text-white/85">{{ $feature['pro']['text'] }}</span>
                                </div>
                            </div>
                            @if($feature['pro']['badge'])
                                <p class="mt-1 pl-12 text-[10px] font-semibold text-white/40">{{ $feature['pro']['badge'] }} on Pro</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pricing & CTA --}}
            <div class="mt-6">
                @auth
                    @if($modalStripeEnabled)
                        {{-- Trial banner --}}
                        <div class="flex items-center gap-3 rounded-xl ring-1 ring-primary-300/25 bg-primary-500/10 px-5 py-4">
                            <svg class="w-5 h-5 text-primary-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM12 14l2 2"/></svg>
                            <p class="text-sm text-white/80">
                                <span class="font-semibold text-white">Try Pro free for {{ $modalTrialDays }} days.</span>
                                Pick a plan below, cancel anytime.
                            </p>
                        </div>

                        {{-- Plan cards + CTA --}}
                        <div x-data="{ plan: 'annual' }">
                            <div class="mt-5 grid sm:grid-cols-2 gap-4">
                                <label :class="plan === 'monthly' ? 'ring-2 ring-primary-300 bg-white/10' : 'ring-1 ring-white/10 bg-white/5'"
                                       class="cursor-pointer rounded-2xl p-6 transition">
                                    <input type="radio" name="modal_plan" value="monthly" x-model="plan" class="sr-only">
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-white/50 mb-2">Monthly</span>
                                    <span class="text-3xl font-bold">${{ $modalPriceMonthly }}<span class="text-sm font-normal text-white/50">/mo</span></span>
                                </label>
                                <label :class="plan === 'annual' ? 'ring-2 ring-primary-300 bg-white/10' : 'ring-1 ring-white/10 bg-white/5'"
                                       class="relative cursor-pointer rounded-2xl p-6 transition">
                                    <input type="radio" name="modal_plan" value="annual" x-model="plan" class="sr-only">
                                    <span class="absolute -top-2.5 right-5 bg-accent-500 text-white text-[11px] font-semibold uppercase tracking-wide px-2.5 py-0.5 rounded-full">Best value</span>
                                    <span class="block text-xs font-semibold uppercase tracking-wider text-white/50 mb-2">Annual</span>
                                    <span class="text-3xl font-bold">${{ $modalPriceAnnual }}<span class="text-sm font-normal text-white/50">/yr</span></span>
                                </label>
                            </div>

                            <form method="POST" action="{{ route('pro.checkout') }}" class="mt-7">
                                @csrf
                                <input type="hidden" name="plan" :value="plan">
                                <button type="submit"
                                        class="w-full flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold text-lg px-6 py-4 rounded-full transition shadow-lg shadow-accent-500/20">
                                    Subscribe now
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </button>
                            </form>
                        </div>

                        {{-- Trust strip --}}
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-white/45">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Secure payment
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Cancel anytime
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11c0 1.657-1.343 3-3 3m3-3c0-1.657-1.343-3-3-3m3 3h6m-9 3a3 3 0 01-3-3m0 0a3 3 0 013-3m0 6v3m0-9V5m-6 7a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
                                Your data is safe
                            </span>
                        </div>
                    @else
                        <a href="{{ route('pro.show') }}"
                           class="w-full flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold text-lg px-6 py-4 rounded-full transition mt-4">
                            See plans
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="w-full flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold text-lg px-6 py-4 rounded-full transition mt-4">
                        Sign in to subscribe
                    </a>
                    <p class="xs-pro-foot mt-3">Your subscription works across the website and the app.</p>
                @endauth

                <button type="button" class="xs-pro-later" data-xs-pro-close>Maybe later</button>
                <p class="xs-pro-foot">
                    By starting your subscription you agree to our
                    <a href="{{ route('terms') }}" target="_blank">Terms &amp; Conditions</a>,
                    including the billing, auto-renewal, and refund policies.
                </p>
            </div>
        </div>

    </div>
</div>

<script>
    (function () {
        var hideTimeout = null;
        var COPY = {
            gpx:   { title: 'Download the GPX file with <span style="color:#E87B35">Pro</span>', sub: 'Export this route and navigate offline' },
            video: { title: 'Watch Pro <span style="color:#E87B35">video content</span>', sub: 'Unlock in-depth guides for trails and points of interest' },
            poi:   { title: 'See points of interest with <span style="color:#E87B35">Pro</span>', sub: 'Unlock curated spots and hidden gems across the map' },
            'default': { title: '<span style="color:#ffffff">Free or</span> <span style="color:#E87B35">Pro?</span>', sub: 'More trails. More adventures. More you.' }
        };

        window.xsShowProModal = function (featureKey) {
            var modal = document.getElementById('xs-pro-modal');
            if (!modal) {
                window.location.href = (window.xsWeb && window.xsWeb.proUrl) ? window.xsWeb.proUrl : '/pro';
                return;
            }
            var c = COPY[featureKey] || COPY['default'];
            var t = modal.querySelector('[data-xs-pro-title]');
            var s = modal.querySelector('[data-xs-pro-sub]');
            if (t) { t.innerHTML = c.title; }
            if (s) { s.textContent = c.sub; }

            if (hideTimeout) { clearTimeout(hideTimeout); hideTimeout = null; }

            modal.classList.add('is-visible');
            void modal.offsetWidth;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        window.xsHideProModal = function () {
            var modal = document.getElementById('xs-pro-modal');
            if (!modal) { return; }
            if (hideTimeout) { clearTimeout(hideTimeout); }
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            hideTimeout = setTimeout(function () {
                modal.classList.remove('is-visible');
                hideTimeout = null;
            }, 1100);
        };

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-xs-pro-close]')) { window.xsHideProModal(); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { window.xsHideProModal(); }
        });
    })();
</script>
@endunless
