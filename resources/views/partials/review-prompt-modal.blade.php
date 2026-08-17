{{--
    Review prompt — asks the visitor to leave a review on whichever channel they
    arrived through. Inside the iOS app it targets the App Store, inside the
    Android app Google Play, and on the website the Google Business Profile
    listing. review_channel() / review_url() resolve that server-side; the
    native bridge (window.XploreReview, see docs/NATIVE_REVIEW_BRIDGE.md)
    upgrades the store channels to the real in-app review sheet when present.

    "Leave a review" and "Send us feedback" are deliberately equal-weight
    buttons: Google's In-App Review guidelines forbid asking an opinion question
    before offering the review, and Google Business Profile policy forbids
    review gating, so unhappy users must not be filtered out of the review path.

    Styles are inlined rather than pushed to @stack('styles') because this
    partial renders at the end of the body, after the head has already flushed —
    same reason as subscription/_upgrade-modal.blade.php.
--}}
@php($reviewChannel = review_channel())
@php($reviewUrl = review_url($reviewChannel))
@if(setting('review_prompt_enabled') && $reviewUrl)
@php($reviewFeedbackEmail = review_feedback_email())
<style>
    @keyframes xsReviewFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes xsReviewSlideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes xsReviewSlideUpDesktop { from { transform: translateY(36px) scale(.98); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

    .xs-review-modal { position: fixed; inset: 0; z-index: 10000; display: none; align-items: flex-end; justify-content: center; pointer-events: none; }
    .xs-review-modal.is-visible { display: flex; }
    .xs-review-modal.is-open { pointer-events: auto; }
    @media (min-width: 640px) { .xs-review-modal { align-items: center; } }

    .xs-review-backdrop { position: absolute; inset: 0; background: rgba(10, 20, 19, 0.66); backdrop-filter: blur(3px); opacity: 0; }
    .xs-review-modal.is-open .xs-review-backdrop { animation: xsReviewFadeIn 0.5s ease forwards; }

    .xs-review-dialog {
        position: relative; width: 100%; max-width: 440px; max-height: 92vh; overflow-y: auto;
        background: #fff; color: #1f2937; border-radius: 24px 24px 0 0;
        box-shadow: 0 -20px 60px rgba(0,0,0,.35);
        transform: translateY(100%); opacity: 0;
    }
    .xs-review-modal.is-open .xs-review-dialog { animation: xsReviewSlideUp 0.5s cubic-bezier(.22,1,.36,1) forwards; }
    @media (min-width: 640px) {
        .xs-review-dialog { border-radius: 24px; transform: translateY(36px) scale(.98); box-shadow: 0 30px 70px rgba(0,0,0,.4); }
        .xs-review-modal.is-open .xs-review-dialog { animation: xsReviewSlideUpDesktop 0.5s cubic-bezier(.22,1,.36,1) forwards; }
    }

    .xs-review-head { position: relative; padding: 32px 28px 24px; text-align: center; color: #fff;
        background: linear-gradient(150deg, #2C5F5D 0%, #1a2e2e 100%); border-radius: 24px 24px 0 0; overflow: hidden; }
    .xs-review-head::after { content: ''; position: absolute; top: -40%; right: -20%; width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(232,123,53,.35), transparent 70%); }
    .xs-review-logo { height: 52px; width: auto; max-width: 220px; object-fit: contain; margin: 0 auto 14px; display: block; position: relative; }
    .xs-review-kicker { font-size: 11px; letter-spacing: .22em; text-transform: uppercase; color: #E87B35; font-weight: 700; position: relative; }
    .xs-review-title { font-size: 22px; font-weight: 700; color: #fff; margin-top: 8px; line-height: 1.25; position: relative; }
    .xs-review-sub { margin-top: 10px; font-size: 14px; color: rgba(255,255,255,.78); line-height: 1.5; position: relative; }

    .xs-review-stars { display: flex; justify-content: center; gap: 6px; margin-top: 16px; position: relative; }
    .xs-review-stars svg { width: 24px; height: 24px; color: #F5B942; }

    .xs-review-x { position: absolute; top: 14px; right: 16px; width: 32px; height: 32px; border-radius: 999px;
        background: rgba(255,255,255,.15); color: #fff; font-size: 20px; line-height: 1; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s; z-index: 2; }
    .xs-review-x:hover { background: rgba(255,255,255,.28); }

    .xs-review-body { padding: 22px 28px 4px; font-size: 14px; line-height: 1.6; color: #4b5563; text-align: center; }

    {{-- Both choices share .xs-review-cta so neither is visually favoured. --}}
    .xs-review-actions { padding: 16px 28px 8px; display: grid; gap: 10px; }
    @media (min-width: 420px) { .xs-review-actions { grid-template-columns: 1fr 1fr; } }
    .xs-review-cta { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; text-align: center;
        font-weight: 600; font-size: 15px; padding: 12px 16px; border-radius: 12px; border: 1px solid transparent;
        cursor: pointer; text-decoration: none; transition: background .2s, transform .1s; }
    .xs-review-cta svg { width: 18px; height: 18px; flex: 0 0 auto; }
    .xs-review-cta-primary { background: #2C5F5D; color: #fff; }
    .xs-review-cta-primary:hover { background: #24504e; }
    .xs-review-cta-secondary { background: #fff; color: #2C5F5D; border-color: #d1d5db; }
    .xs-review-cta-secondary:hover { background: #f9fafb; }
    .xs-review-cta:active { transform: translateY(1px); }

    .xs-review-later { display: block; width: 100%; text-align: center; background: transparent; color: #6b7280;
        font-size: 13px; padding: 6px; border: 0; cursor: pointer; }
    .xs-review-foot { text-align: center; font-size: 12px; color: #9ca3af; padding: 4px 28px 26px; }
    @media (min-width: 420px) {
        .xs-review-later { grid-column: 1 / -1; }
    }

    @media (prefers-reduced-motion: reduce) {
        .xs-review-modal.is-open .xs-review-backdrop,
        .xs-review-modal.is-open .xs-review-dialog { animation-duration: .01ms; }
        .xs-review-dialog { transform: none; opacity: 1; }
    }
</style>

<div id="xs-review-modal" class="xs-review-modal" aria-hidden="true">
    <div class="xs-review-backdrop" data-xs-review-close></div>
    <div class="xs-review-dialog" role="dialog" aria-modal="true" aria-labelledby="xs-review-title">
        <div class="xs-review-head">
            <button type="button" class="xs-review-x" data-xs-review-close aria-label="Close">&times;</button>
            <img src="{{ asset('images/xploresmithers_white.png') }}" alt="{{ setting('site_name') }}" class="xs-review-logo">
            <p class="xs-review-kicker">Enjoying {{ setting('site_name') }}?</p>
            {{-- The data-title-* variants let the native bridge correct the copy when
                 the server's User-Agent guess about the platform was wrong. --}}
            <h2 id="xs-review-title" class="xs-review-title"
                data-title-ios="Rate us on the App Store"
                data-title-android="Rate us on Google Play"
                data-title-web="Review us on Google">
                @switch($reviewChannel)
                    @case('ios') Rate us on the App Store @break
                    @case('android') Rate us on Google Play @break
                    @default Review us on Google
                @endswitch
            </h2>
            <p class="xs-review-sub">
                @if($reviewChannel === 'web')
                    A quick Google review helps other people discover the trails, lakes and trips around Smithers.
                @else
                    A quick rating helps other hikers find the app. It only takes a moment.
                @endif
            </p>
            <div class="xs-review-stars" aria-hidden="true">
                @for($i = 0; $i < 5; $i++)
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.46L12 17.3l-5.8 3.06 1.11-6.46-4.7-4.58 6.49-.94L12 2.5z"/></svg>
                @endfor
            </div>
        </div>

        <p class="xs-review-body">
            Got something we should fix instead? Send us feedback and it comes straight to us.
        </p>

        <div class="xs-review-actions">
            <a id="xs-review-link" href="{{ $reviewUrl }}" target="_blank" rel="noopener" class="xs-review-cta xs-review-cta-primary">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.46L12 17.3l-5.8 3.06 1.11-6.46-4.7-4.58 6.49-.94L12 2.5z"/></svg>
                Leave a review
            </a>

            <a id="xs-review-feedback-link"
               href="{{ $reviewFeedbackEmail
                    ? 'mailto:'.$reviewFeedbackEmail.'?subject='.rawurlencode('Feedback for '.setting('site_name'))
                    : (setting('main_site_url') ?: url('/')) }}"
               @if(! $reviewFeedbackEmail) target="_blank" rel="noopener" @endif
               class="xs-review-cta xs-review-cta-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.9 9.9 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Send us feedback
            </a>

            <button type="button" class="xs-review-later" data-xs-review-close>Maybe later</button>
        </div>

        <p class="xs-review-foot">
            @if($reviewChannel === 'web')
                Opens Google Reviews in a new tab.
            @else
                We only ask once — thanks for helping out.
            @endif
        </p>
    </div>
</div>

<script>
    (function () {
        var STORAGE_KEY = 'xs.reviewPrompt.v1';
        var SESSION_GAP_MS = 30 * 60 * 1000;
        var MAX_DISMISSALS = 3;
        var DAY_MS = 24 * 60 * 60 * 1000;

        var config = window.xsReview || {};
        var hideTimeout = null;
        var autoTimer = null;

        {{-- localStorage throws outright in Safari private mode, so every access is guarded. --}}
        function readState() {
            try {
                return JSON.parse(window.localStorage.getItem(STORAGE_KEY)) || {};
            } catch (e) {
                return {};
            }
        }

        function writeState(state) {
            try {
                window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            } catch (e) {
                /* private browsing — the prompt simply won't be remembered */
            }
        }

        function daysSince(timestamp) {
            if (!timestamp) {
                return Infinity;
            }
            return (Date.now() - timestamp) / DAY_MS;
        }

        function recordVisit() {
            var state = readState();
            var now = Date.now();

            if (!state.firstVisitAt) {
                state.firstVisitAt = now;
            }
            if (!state.lastSeenAt || (now - state.lastSeenAt) > SESSION_GAP_MS) {
                state.sessionCount = (state.sessionCount || 0) + 1;
            }
            state.pageViews = (state.pageViews || 0) + 1;
            state.lastSeenAt = now;

            writeState(state);

            return state;
        }

        var SUPPRESSED_PATHS = ['/login', '/register', '/pro', '/settings', '/admin', '/forgot-password', '/reset-password', '/email/verify'];

        function onSuppressedPage() {
            var path = window.location.pathname;

            return SUPPRESSED_PATHS.some(function (prefix) {
                return path === prefix || path.indexOf(prefix + '/') === 0;
            });
        }

        function isEligible(state) {
            if (!config.enabled || !fallbackUrl()) {
                return false;
            }
            if (state.reviewedAt) {
                return false;
            }
            if ((state.dismissCount || 0) >= MAX_DISMISSALS) {
                return false;
            }
            if (state.dismissedAt && daysSince(state.dismissedAt) < config.snoozeDays) {
                return false;
            }
            if ((state.pageViews || 0) < config.minPageViews) {
                return false;
            }
            if ((state.sessionCount || 0) < config.minSessions) {
                return false;
            }
            if (daysSince(state.firstVisitAt) < config.minDays) {
                return false;
            }

            return true;
        }

        {{-- The native bridge, when the app has one, replaces the store link with
             the real in-app review sheet. See docs/NATIVE_REVIEW_BRIDGE.md.
             Its presence alone proves we are inside the app, so it also wins over
             the server's User-Agent guess about which store to fall back to. --}}
        function nativeBridge() {
            var bridge = window.XploreReview;

            if (!bridge || !bridge.isAvailable || typeof bridge.requestReview !== 'function') {
                return null;
            }

            return bridge;
        }

        function fallbackUrl() {
            var bridge = nativeBridge();
            var urls = config.urls || {};

            if (bridge && (bridge.platform === 'ios' || bridge.platform === 'android') && urls[bridge.platform]) {
                return urls[bridge.platform];
            }

            return config.url;
        }

        function track(action, trigger) {
            if (!config.eventUrl || typeof navigator.sendBeacon !== 'function') {
                return;
            }
            try {
                var payload = new Blob(
                    [JSON.stringify({ channel: config.channel, action: action, trigger: trigger || null, page_url: window.location.pathname })],
                    { type: 'application/json' }
                );
                navigator.sendBeacon(config.eventUrl, payload);
            } catch (e) {
                /* analytics is best-effort */
            }
        }

        function modal() {
            return document.getElementById('xs-review-modal');
        }

        {{-- Point the link (and the heading) at whatever the bridge corrected to. --}}
        function syncChannel() {
            var bridge = nativeBridge();
            var platform = bridge && bridge.platform ? bridge.platform : config.channel;
            var link = document.getElementById('xs-review-link');
            var heading = document.getElementById('xs-review-title');
            var url = fallbackUrl();

            if (link && url) {
                link.href = url;
            }
            if (heading && platform !== config.channel) {
                var corrected = heading.getAttribute('data-title-' + platform);

                if (corrected) {
                    heading.textContent = corrected;
                }
            }
        }

        window.xsShowReviewPrompt = function (trigger) {
            var el = modal();

            if (!el) {
                return;
            }

            syncChannel();

            if (hideTimeout) { clearTimeout(hideTimeout); hideTimeout = null; }

            el.classList.add('is-visible');
            void el.offsetWidth;
            el.classList.add('is-open');
            el.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            track('shown', trigger || 'manual');
        };

        window.xsHideReviewPrompt = function () {
            var el = modal();

            if (!el) {
                return;
            }
            if (hideTimeout) { clearTimeout(hideTimeout); }

            el.classList.remove('is-open');
            el.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            hideTimeout = setTimeout(function () {
                el.classList.remove('is-visible');
                hideTimeout = null;
            }, 1100);
        };

        window.xsMaybeShowReviewPrompt = function (trigger) {
            if (onSuppressedPage() || !isEligible(readState())) {
                return false;
            }
            {{-- Don't stack on top of the Pro upgrade modal, the photo uploader, etc. --}}
            if (document.body.style.overflow === 'hidden') {
                return false;
            }

            window.xsShowReviewPrompt(trigger || 'engagement');

            return true;
        };

        function markReviewed() {
            var state = readState();
            state.reviewedAt = Date.now();
            writeState(state);
        }

        function markDismissed() {
            var state = readState();
            state.dismissedAt = Date.now();
            state.dismissCount = (state.dismissCount || 0) + 1;
            writeState(state);
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-xs-review-close]')) {
                markDismissed();
                track('dismissed');
                window.xsHideReviewPrompt();

                return;
            }

            var reviewLink = e.target.closest('#xs-review-link');

            if (reviewLink) {
                markReviewed();
                track('review_clicked');

                var bridge = nativeBridge();

                if (bridge) {
                    e.preventDefault();
                    bridge.requestReview();
                }

                window.xsHideReviewPrompt();

                return;
            }

            if (e.target.closest('#xs-review-feedback-link')) {
                {{-- Not a dismissal: they took the other equal-weight option. --}}
                markReviewed();
                track('feedback_clicked');
                window.xsHideReviewPrompt();
            }
        });

        document.addEventListener('keydown', function (e) {
            var el = modal();

            if (e.key === 'Escape' && el && el.classList.contains('is-open')) {
                markDismissed();
                track('dismissed');
                window.xsHideReviewPrompt();
            }
        });

        {{-- Push-style hook the native side calls with the outcome of the in-app
             review flow, mirroring window.OfflineEvents.dispatch(...) from
             ANDROID_OFFLINE_MODE.md. A payload may arrive as a JSON string. --}}
        window.XploreReviewEvents = {
            dispatch: function (payload) {
                var data = payload;

                if (typeof data === 'string') {
                    try {
                        data = JSON.parse(data);
                    } catch (e) {
                        data = { event: data };
                    }
                }

                var url = fallbackUrl();

                if (data && data.event === 'review:unavailable' && url) {
                    window.open(url, '_blank', 'noopener');
                }
            }
        };

        recordVisit();

        autoTimer = setTimeout(function () {
            window.xsMaybeShowReviewPrompt('engagement');
        }, 8000);

        window.addEventListener('pagehide', function () {
            if (autoTimer) { clearTimeout(autoTimer); }
        });
    })();
</script>
@endif
