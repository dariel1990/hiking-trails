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

    .xs-pro-backdrop { position: absolute; inset: 0; background: rgba(10, 20, 19, 0.7); backdrop-filter: blur(3px); opacity: 0; }
    .xs-pro-modal.is-open .xs-pro-backdrop { animation: xsProFadeIn 0.5s ease forwards; }

    .xs-pro-dialog {
        position: relative; width: 100%; max-width: 600px; max-height: 92vh; overflow-y: auto;
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
    @media (prefers-reduced-motion: reduce) {
        .xs-pro-modal.is-open .xs-pro-backdrop,
        .xs-pro-modal.is-open .xs-pro-dialog { animation: none; opacity: 1; transform: none; }
    }

    .xs-pro-head { position: relative; padding: 28px 24px 22px; text-align: center; color: #fff;
        background: linear-gradient(150deg, #2C5F5D 0%, #1a2e2e 100%); overflow: hidden; }
    .xs-pro-head::after { content: ''; position: absolute; top: -40%; right: -20%; width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(232,123,53,.35), transparent 70%); }
    .xs-pro-logo { height: 40px; width: auto; max-width: 200px; object-fit: contain; margin: 0 auto 10px; display: block; position: relative; }
    .xs-pro-title { font-size: 26px; font-weight: 800; color: #fff; line-height: 1; letter-spacing: -0.01em; position: relative; }
    .xs-pro-sub { margin-top: 8px; font-size: 12px; font-weight: 600; letter-spacing: .16em; text-transform: uppercase; color: #86efac; position: relative; }

    .xs-pro-x { position: absolute; top: 14px; right: 16px; width: 32px; height: 32px; border-radius: 999px;
        background: rgba(255,255,255,.15); color: #fff; font-size: 20px; line-height: 1; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s; z-index: 2; }
    .xs-pro-x:hover { background: rgba(255,255,255,.28); }

    .xs-pro-body { padding: 22px 22px 26px; }

    .xs-pro-table { border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,.1); }

    .xs-pro-table-head { display: flex; align-items: center; gap: 12px; padding: 10px 18px; background: rgba(255,255,255,.05); border-bottom: 1px solid rgba(255,255,255,.1); }
    .xs-pro-table-head-spacer { flex: 0 0 34px; }
    .xs-pro-table-head-cols { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .xs-pro-table-head-free, .xs-pro-table-head-pro { font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; display: flex; align-items: center; gap: 4px; }
    .xs-pro-table-head-free { color: rgba(255,255,255,.4); }
    .xs-pro-table-head-pro { color: #fdba74; }
    .xs-pro-table-head-pro svg { width: 11px; height: 11px; }

    .xs-pro-row { padding: 16px 18px; border-top: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.03); }
    .xs-pro-row:first-child { border-top: 0; }
    .xs-pro-row-head { display: flex; align-items: center; gap: 12px; }
    .xs-pro-row-icon { flex: 0 0 auto; width: 34px; height: 34px; border-radius: 999px; background: rgba(45,95,93,.25); display: flex; align-items: center; justify-content: center; }
    .xs-pro-row-icon svg { width: 17px; height: 17px; color: #bbf7d0; }
    .xs-pro-row-label { font-size: 14px; font-weight: 600; color: #fff; }
    .xs-pro-row-sub { font-size: 11.5px; color: rgba(255,255,255,.45); margin-top: 1px; }
    .xs-pro-row-cols { margin-top: 10px; padding-left: 46px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .xs-pro-col { display: flex; align-items: center; gap: 6px; font-size: 11.5px; border-radius: 9px; padding: 7px 9px; }
    .xs-pro-col svg { flex: 0 0 auto; width: 14px; height: 14px; }
    .xs-pro-col-free { color: rgba(255,255,255,.5); background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07); }
    .xs-pro-col-free svg { color: rgba(255,255,255,.3); }
    .xs-pro-col-pro { color: #fff; font-weight: 600; background: rgba(45,95,93,.35); border: 1px solid rgba(167,243,208,.25); }
    .xs-pro-col-pro svg { color: #34d399; }
    .xs-pro-badge { display: block; margin-top: 6px; padding-left: 46px; font-size: 10px; font-weight: 600; color: rgba(255,255,255,.4); }

    .xs-pro-trial { display: flex; align-items: center; gap: 10px; border-radius: 12px; border: 1px solid rgba(167,243,208,.25); background: rgba(45,95,93,.18); padding: 12px 16px; margin-top: 18px; }
    .xs-pro-trial svg { flex: 0 0 auto; width: 18px; height: 18px; color: #bbf7d0; }
    .xs-pro-trial p { font-size: 13px; color: rgba(255,255,255,.8); }
    .xs-pro-trial strong { color: #fff; }

    .xs-pro-plans { margin-top: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .xs-pro-plan { position: relative; cursor: pointer; border-radius: 16px; padding: 14px; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.04); transition: background .15s, border-color .15s; }
    .xs-pro-plan.is-selected { border-color: #86efac; background: rgba(255,255,255,.1); }
    .xs-pro-plan-label { display: block; font-size: 10.5px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.5); margin-bottom: 6px; }
    .xs-pro-plan-price { font-size: 20px; font-weight: 700; color: #fff; }
    .xs-pro-plan-price span { font-size: 12px; font-weight: 400; color: rgba(255,255,255,.5); }
    .xs-pro-plan-best { position: absolute; top: -9px; right: 12px; background: #E87B35; color: #fff; font-size: 9.5px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; padding: 2px 8px; border-radius: 999px; }

    .xs-pro-cta { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; text-align: center; background: #E87B35; color: #fff; font-weight: 700;
        font-size: 16px; padding: 15px 18px; border-radius: 999px; border: 0; cursor: pointer; text-decoration: none; margin-top: 18px;
        box-shadow: 0 8px 20px rgba(232,123,53,.3); transition: background .2s, transform .1s; }
    .xs-pro-cta:hover { background: #d96f2c; }
    .xs-pro-cta:active { transform: translateY(1px); }
    .xs-pro-cta:disabled { opacity: .5; cursor: not-allowed; }
    .xs-pro-cta-secondary { display: block; width: 100%; text-align: center; background: #fff; color: #2C5F5D;
        font-weight: 600; font-size: 15px; padding: 13px 18px; border-radius: 999px; border: 1px solid #d1d5db; cursor: pointer; margin-top: 18px; text-decoration: none; }
    .xs-pro-cta-secondary:hover { border-color: #2C5F5D; }

    .xs-pro-trust { margin-top: 16px; display: flex; flex-wrap: wrap; justify-content: center; gap: 16px; font-size: 11px; color: rgba(255,255,255,.45); }
    .xs-pro-trust span { display: inline-flex; align-items: center; gap: 6px; }
    .xs-pro-trust svg { width: 14px; height: 14px; color: #86efac; }

    .xs-pro-later { display: block; width: 100%; text-align: center; background: transparent; color: rgba(255,255,255,.5);
        font-size: 12.5px; padding: 12px 6px 0; border: 0; cursor: pointer; }
    .xs-pro-foot { text-align: center; font-size: 11px; color: rgba(255,255,255,.3); margin-top: 14px; }
    .xs-pro-foot a { text-decoration: underline; }
    .xs-pro-foot a:hover { color: rgba(255,255,255,.55); }
</style>

<div id="xs-pro-modal" class="xs-pro-modal" aria-hidden="true">
    <div class="xs-pro-backdrop" data-xs-pro-close></div>
    <div class="xs-pro-dialog" role="dialog" aria-modal="true" aria-labelledby="xs-pro-title">
        <div class="xs-pro-head">
            <button type="button" class="xs-pro-x" data-xs-pro-close aria-label="Close">&times;</button>
            <img src="{{ asset('images/xploresmithers_white.png') }}" alt="XploreSmithers" class="xs-pro-logo">
            <h2 id="xs-pro-title" class="xs-pro-title" data-xs-pro-title>Free or <span style="color:#E87B35">Pro?</span></h2>
            <p class="xs-pro-sub" data-xs-pro-sub>More trails. More adventures. More you.</p>
        </div>

        <div class="xs-pro-body">
            @php($features = \App\Services\ProFeatureCatalog::all())

            <div class="xs-pro-table">
                <div class="xs-pro-table-head">
                    <span class="xs-pro-table-head-spacer"></span>
                    <div class="xs-pro-table-head-cols">
                        <span class="xs-pro-table-head-free">Free</span>
                        <span class="xs-pro-table-head-pro">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path d="M5 16L3 6l5 3 2-5 2 5 5-3-2 10H5zm0 2h10v1a1 1 0 01-1 1H6a1 1 0 01-1-1v-1z"/></svg>
                            Pro
                        </span>
                    </div>
                </div>
                @foreach($features as $feature)
                    <div class="xs-pro-row">
                        <div class="xs-pro-row-head">
                            <span class="xs-pro-row-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feature['icon'] }}"/></svg>
                            </span>
                            <div>
                                <p class="xs-pro-row-label">{{ $feature['label'] }}</p>
                                <p class="xs-pro-row-sub">{{ $feature['sub'] }}</p>
                            </div>
                        </div>
                        <div class="xs-pro-row-cols">
                            <span class="xs-pro-col xs-pro-col-free">
                                @if($feature['free']['available'])
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @endif
                                {{ $feature['free']['text'] }}
                            </span>
                            <span class="xs-pro-col xs-pro-col-pro">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                {{ $feature['pro']['text'] }}
                            </span>
                        </div>
                        @if($feature['pro']['badge'])
                            <span class="xs-pro-badge">{{ $feature['pro']['badge'] }} on Pro</span>
                        @endif
                    </div>
                @endforeach
            </div>

            @auth
                @if(config('services.stripe.enabled'))
                    <div class="xs-pro-trial">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM12 14l2 2"/></svg>
                        <p><strong>Try Pro free for {{ (int) config('services.stripe.trial_days') }} days.</strong> Pick a plan, cancel anytime.</p>
                    </div>

                    <div x-data="{ plan: 'annual' }">
                        <div class="xs-pro-plans">
                            <label class="xs-pro-plan" :class="plan === 'monthly' && 'is-selected'">
                                <input type="radio" name="plan" value="monthly" x-model="plan" class="sr-only">
                                <span class="xs-pro-plan-label">Monthly</span>
                                <span class="xs-pro-plan-price">$4.99<span>/mo</span></span>
                            </label>
                            <label class="xs-pro-plan" :class="plan === 'annual' && 'is-selected'">
                                <input type="radio" name="plan" value="annual" x-model="plan" class="sr-only">
                                <span class="xs-pro-plan-best">Best value</span>
                                <span class="xs-pro-plan-label">Annual</span>
                                <span class="xs-pro-plan-price">$39.99<span>/yr</span></span>
                            </label>
                        </div>

                        <form method="POST" action="{{ route('pro.checkout') }}">
                            @csrf
                            <input type="hidden" name="plan" :value="plan">
                            <button type="submit" class="xs-pro-cta">
                                Subscribe now
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </button>
                        </form>
                    </div>

                    <div class="xs-pro-trust">
                        <span><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Secure payment</span>
                        <span><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Cancel anytime</span>
                        <span><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11c0 1.657-1.343 3-3 3m3-3c0-1.657-1.343-3-3-3m3 3h6m-9 3a3 3 0 01-3-3m0 0a3 3 0 013-3m0 6v3m0-9V5m-6 7a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>Your data is safe</span>
                    </div>
                @else
                    <a href="{{ route('pro.show') }}" class="xs-pro-cta">See plans</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="xs-pro-cta">Sign in to subscribe</a>
                <p class="xs-pro-foot">Your subscription works across the website and the app.</p>
            @endauth

            <button type="button" class="xs-pro-later" data-xs-pro-close>Maybe later</button>
            <p class="xs-pro-foot">
                By subscribing you agree to our <a href="{{ route('terms') }}" target="_blank">Terms &amp; Conditions</a>.
            </p>
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
            'default': { title: 'Free or <span style="color:#E87B35">Pro?</span>', sub: 'More trails. More adventures. More you.' }
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

            // Cancel any pending hide from a previous close so it doesn't strip
            // is-visible out from under this newly-opened modal later.
            if (hideTimeout) { clearTimeout(hideTimeout); hideTimeout = null; }

            modal.classList.add('is-visible');
            // Force reflow so the transition runs from the hidden state.
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
