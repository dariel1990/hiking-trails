{{-- XploreSmithers Pro upgrade modal — shown when a non-subscriber taps a gated
     feature in the browser. Triggered imperatively via window.xsShowProModal(featureKey).
     Styles are inlined (not @push'd) because this partial is included at the end of
     the body, after the head's @stack('styles') has already rendered. --}}
<style>
    .xs-pro-modal { position: fixed; inset: 0; z-index: 10000; display: none; align-items: flex-end; justify-content: center; }
    .xs-pro-modal.is-visible { display: flex; }
    @media (min-width: 640px) { .xs-pro-modal { align-items: center; } }

    .xs-pro-backdrop { position: absolute; inset: 0; background: rgba(10, 20, 19, 0.66); backdrop-filter: blur(3px); opacity: 0; transition: opacity .35s ease; }
    .xs-pro-modal.is-open .xs-pro-backdrop { opacity: 1; }

    .xs-pro-dialog {
        position: relative; width: 100%; max-width: 440px; max-height: 92vh; overflow-y: auto;
        background: #fff; color: #1f2937; border-radius: 24px 24px 0 0;
        box-shadow: 0 -20px 60px rgba(0,0,0,.35);
        transform: translateY(100%); opacity: 0;
        transition: transform .5s cubic-bezier(.16,1,.3,1), opacity .4s ease;
    }
    .xs-pro-modal.is-open .xs-pro-dialog { transform: translateY(0); opacity: 1; }
    @media (min-width: 640px) {
        .xs-pro-dialog { border-radius: 24px; transform: translateY(36px) scale(.98); box-shadow: 0 30px 70px rgba(0,0,0,.4); }
        .xs-pro-modal.is-open .xs-pro-dialog { transform: translateY(0) scale(1); }
    }

    .xs-pro-head { position: relative; padding: 32px 28px 24px; text-align: center; color: #fff;
        background: linear-gradient(150deg, #2C5F5D 0%, #1a2e2e 100%); border-radius: 24px 24px 0 0; overflow: hidden; }
    .xs-pro-head::after { content: ''; position: absolute; top: -40%; right: -20%; width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(232,123,53,.35), transparent 70%); }
    .xs-pro-logo { height: 52px; width: auto; max-width: 220px; object-fit: contain; margin: 0 auto 14px; display: block; position: relative; }
    .xs-pro-kicker { font-size: 11px; letter-spacing: .22em; text-transform: uppercase; color: #E87B35; font-weight: 700; position: relative; }
    .xs-pro-title { font-size: 22px; font-weight: 700; color: #fff; margin-top: 8px; line-height: 1.25; position: relative; }
    .xs-pro-sub { margin-top: 10px; font-size: 14px; color: rgba(255,255,255,.78); line-height: 1.5; position: relative; }

    .xs-pro-x { position: absolute; top: 14px; right: 16px; width: 32px; height: 32px; border-radius: 999px;
        background: rgba(255,255,255,.15); color: #fff; font-size: 20px; line-height: 1; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s; z-index: 2; }
    .xs-pro-x:hover { background: rgba(255,255,255,.28); }

    .xs-pro-features { list-style: none; margin: 0; padding: 22px 28px 6px; display: grid; gap: 12px; }
    .xs-pro-features li { display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 500; color: #374151; }
    .xs-pro-features svg { flex: 0 0 auto; width: 22px; height: 22px; color: #2C5F5D; }
    .xs-pro-note { display: inline-block; font-size: 10px; font-weight: 600; letter-spacing: .02em; color: #6b7280; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 999px; padding: 1px 8px; margin-left: 8px; vertical-align: middle; white-space: nowrap; }

    .xs-pro-actions { padding: 16px 28px 8px; display: grid; gap: 10px; }
    .xs-pro-cta { display: block; width: 100%; text-align: center; background: #E87B35; color: #fff; font-weight: 600;
        font-size: 15px; padding: 14px 18px; border-radius: 12px; border: 0; cursor: pointer; text-decoration: none;
        box-shadow: 0 8px 20px rgba(232,123,53,.3); transition: background .2s, transform .1s; }
    .xs-pro-cta:hover { background: #d96f2c; }
    .xs-pro-cta:active { transform: translateY(1px); }
    .xs-pro-cta-secondary { display: block; width: 100%; text-align: center; background: #fff; color: #2C5F5D;
        font-weight: 600; font-size: 15px; padding: 13px 18px; border-radius: 12px; border: 1px solid #d1d5db; cursor: pointer; }
    .xs-pro-cta-secondary:hover { border-color: #2C5F5D; }
    .xs-pro-later { display: block; width: 100%; text-align: center; background: transparent; color: #6b7280;
        font-size: 13px; padding: 6px; border: 0; cursor: pointer; }
    .xs-pro-foot { text-align: center; font-size: 12px; color: #9ca3af; padding: 4px 28px 26px; }
</style>

<div id="xs-pro-modal" class="xs-pro-modal" aria-hidden="true">
    <div class="xs-pro-backdrop" data-xs-pro-close></div>
    <div class="xs-pro-dialog" role="dialog" aria-modal="true" aria-labelledby="xs-pro-title">
        <div class="xs-pro-head">
            <button type="button" class="xs-pro-x" data-xs-pro-close aria-label="Close">&times;</button>
            <img src="{{ asset('images/xploresmithers_white.png') }}" alt="XploreSmithers" class="xs-pro-logo">
            <p class="xs-pro-kicker">XploreSmithers Pro</p>
            <h2 id="xs-pro-title" class="xs-pro-title" data-xs-pro-title>Unlock more with XploreSmithers Pro</h2>
            <p class="xs-pro-sub" data-xs-pro-sub>Subscribe to access offline maps, points of interest, Pro videos, and GPX downloads.</p>
        </div>

        <ul class="xs-pro-features">
            <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg><span>Offline maps for any trail <span class="xs-pro-note">Mobile app only</span></span></li>
            <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Unique points of interest</li>
            <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Pro video content</li>
            <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>GPX file download</li>
        </ul>

        <div class="xs-pro-actions">
            @auth
                @if(config('services.stripe.enabled'))
                    <form method="POST" action="{{ route('pro.checkout') }}">
                        @csrf
                        <input type="hidden" name="plan" value="annual">
                        <button type="submit" class="xs-pro-cta">Start {{ (int) config('services.stripe.trial_days') }}-day free trial · $39.99/yr</button>
                    </form>
                    <form method="POST" action="{{ route('pro.checkout') }}">
                        @csrf
                        <input type="hidden" name="plan" value="monthly">
                        <button type="submit" class="xs-pro-cta-secondary">Or go monthly · $4.99/mo</button>
                    </form>
                @else
                    <a href="{{ route('pro.show') }}" class="xs-pro-cta">See plans</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="xs-pro-cta">Sign in to subscribe</a>
                <p class="xs-pro-foot" style="padding-bottom:0">Your subscription works across the website and the app.</p>
            @endauth
            <button type="button" class="xs-pro-later" data-xs-pro-close>Maybe later</button>
        </div>

        <p class="xs-pro-foot">Cancel anytime · Apple Pay, Google Pay &amp; cards accepted</p>
    </div>
</div>

<script>
    (function () {
        var COPY = {
            gpx:   { title: 'Download the GPX file with Pro', sub: 'Export this route and navigate offline. GPX downloads are part of XploreSmithers Pro.' },
            video: { title: 'Watch Pro video content',        sub: 'Pro members unlock video for trails and points of interest across the map.' },
            poi:   { title: 'See points of interest with Pro', sub: 'Unlock curated points of interest across the map with XploreSmithers Pro.' },
            'default': { title: 'Unlock more with XploreSmithers Pro', sub: 'Subscribe to access offline maps, points of interest, Pro videos, and GPX downloads.' }
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
            if (t) { t.textContent = c.title; }
            if (s) { s.textContent = c.sub; }

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
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            setTimeout(function () { modal.classList.remove('is-visible'); }, 500);
        };

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-xs-pro-close]')) { window.xsHideProModal(); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { window.xsHideProModal(); }
        });
    })();
</script>
