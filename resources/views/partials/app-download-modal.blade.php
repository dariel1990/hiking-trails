{{--
    "Get the app" modal — shown to phone/tablet visitors instead of the Interactive Map.
    Styled to match the XploreSmithers Pro upgrade modal (subscription/_upgrade-modal.blade.php):
    bottom-sheet on mobile, centered dialog on larger screens, dark gradient header, slide/fade transitions.

    Relies on window.xsAppDownloadUrl (set in layouts.public) for the Google Play link.
    If that config is empty, the modal stays hidden and map links behave normally.
--}}
<style>
    @keyframes xsAppFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes xsAppSlideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes xsAppSlideUpDesktop { from { transform: translateY(36px) scale(.98); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

    .xs-app-modal { position: fixed; inset: 0; z-index: 10000; display: none; align-items: flex-end; justify-content: center; pointer-events: none; }
    .xs-app-modal.is-visible { display: flex; }
    .xs-app-modal.is-open { pointer-events: auto; }
    @media (min-width: 640px) { .xs-app-modal { align-items: center; } }

    .xs-app-backdrop { position: absolute; inset: 0; background: rgba(10, 20, 19, 0.66); backdrop-filter: blur(3px); opacity: 0; }
    .xs-app-modal.is-open .xs-app-backdrop { animation: xsAppFadeIn 0.5s ease forwards; }

    .xs-app-dialog {
        position: relative; width: 100%; max-width: 440px; max-height: 92vh; overflow-y: auto;
        background: #fff; color: #1f2937; border-radius: 24px 24px 0 0;
        box-shadow: 0 -20px 60px rgba(0,0,0,.35);
        transform: translateY(100%); opacity: 0;
    }
    .xs-app-modal.is-open .xs-app-dialog { animation: xsAppSlideUp 0.5s cubic-bezier(.22,1,.36,1) forwards; }
    @media (min-width: 640px) {
        .xs-app-dialog { border-radius: 24px; transform: translateY(36px) scale(.98); box-shadow: 0 30px 70px rgba(0,0,0,.4); }
        .xs-app-modal.is-open .xs-app-dialog { animation: xsAppSlideUpDesktop 0.5s cubic-bezier(.22,1,.36,1) forwards; }
    }

    .xs-app-head { position: relative; padding: 32px 28px 24px; text-align: center; color: #fff;
        background: linear-gradient(150deg, #2C5F5D 0%, #1a2e2e 100%); border-radius: 24px 24px 0 0; overflow: hidden; }
    .xs-app-head::after { content: ''; position: absolute; top: -40%; right: -20%; width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(232,123,53,.35), transparent 70%); }
    .xs-app-logo { height: 52px; width: auto; max-width: 220px; object-fit: contain; margin: 0 auto 14px; display: block; position: relative; }
    .xs-app-kicker { font-size: 11px; letter-spacing: .22em; text-transform: uppercase; color: #E87B35; font-weight: 700; position: relative; }
    .xs-app-title { font-size: 22px; font-weight: 700; color: #fff; margin-top: 8px; line-height: 1.25; position: relative; }
    .xs-app-sub { margin-top: 10px; font-size: 14px; color: rgba(255,255,255,.78); line-height: 1.5; position: relative; }

    .xs-app-x { position: absolute; top: 14px; right: 16px; width: 32px; height: 32px; border-radius: 999px;
        background: rgba(255,255,255,.15); color: #fff; font-size: 20px; line-height: 1; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s; z-index: 2; }
    .xs-app-x:hover { background: rgba(255,255,255,.28); }

    .xs-app-features { list-style: none; margin: 0; padding: 22px 28px 6px; display: grid; gap: 12px; }
    .xs-app-features li { display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 500; color: #374151; }
    .xs-app-features svg { flex: 0 0 auto; width: 22px; height: 22px; color: #2C5F5D; }

    .xs-app-actions { padding: 16px 28px 8px; display: grid; gap: 10px; }
    .xs-app-cta { display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; text-align: center;
        background: #000; color: #fff; font-weight: 600; font-size: 15px; padding: 12px 18px; border-radius: 12px;
        border: 0; cursor: pointer; text-decoration: none; transition: background .2s, transform .1s; }
    .xs-app-cta:hover { background: #1a1a1a; }
    .xs-app-cta:active { transform: translateY(1px); }
    .xs-app-later { display: block; width: 100%; text-align: center; background: transparent; color: #6b7280;
        font-size: 13px; padding: 6px; border: 0; cursor: pointer; }
    .xs-app-foot { text-align: center; font-size: 12px; color: #9ca3af; padding: 4px 28px 26px; }
</style>

<div id="app-download-modal" class="xs-app-modal" aria-hidden="true">
    <div class="xs-app-backdrop" data-xs-app-close></div>
    <div class="xs-app-dialog" role="dialog" aria-modal="true" aria-labelledby="xs-app-title">
        <div class="xs-app-head">
            <button type="button" class="xs-app-x" data-xs-app-close aria-label="Close">&times;</button>
            <img src="{{ asset('images/xploresmithers_white.png') }}" alt="XploreSmithers" class="xs-app-logo">
            <p class="xs-app-kicker">Get the App</p>
            <h2 id="xs-app-title" class="xs-app-title">Get the {{ setting('site_name') }} App</h2>
            <p class="xs-app-sub">The Interactive Map is built for the app — download it for a faster, offline-ready map of trails, lakes &amp; more in your pocket.</p>
        </div>

        <ul class="xs-app-features">
            <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>Offline-ready interactive map</li>
            <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Faster, app-optimized experience</li>
            <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Trails, lakes &amp; points of interest</li>
        </ul>

        <div class="xs-app-actions">
            <a id="app-download-modal-link" href="#" target="_blank" rel="noopener" class="xs-app-cta">
                <svg class="w-7 h-7 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 0 1-.61-.92V2.734a1 1 0 0 1 .609-.92z" fill="#34A853"/>
                    <path d="M16.81 15.013L6.05 21.21l8.13-8.131 2.63 1.934z" fill="#EA4335"/>
                    <path d="M20.16 10.81a1 1 0 0 1 0 1.74l-3.35 1.93-2.63-2.48 2.63-2.48 3.35 1.29z" fill="#FBBC04"/>
                    <path d="M14.18 12l-8.13-8.13L16.81 8.97l-2.63 3.03z" fill="#4285F4"/>
                </svg>
                <span style="display:flex;flex-direction:column;align-items:flex-start;line-height:1.2;">
                    <span style="font-size:10px;color:#d1d5db;text-transform:uppercase;letter-spacing:.05em;">Get it on</span>
                    <span style="font-size:15px;font-weight:600;">Google Play</span>
                </span>
            </a>
            <button type="button" class="xs-app-later" data-xs-app-close>Maybe later</button>
        </div>

        <p class="xs-app-foot">Free to download · Available on Android</p>
    </div>
</div>

<script>
    (function () {
        var hideTimeout = null;

        window.xsShowAppDownloadModal = function () {
            var modal = document.getElementById('app-download-modal');
            if (!modal) {
                return;
            }
            var modalLink = document.getElementById('app-download-modal-link');
            if (modalLink && window.xsAppDownloadUrl) {
                modalLink.href = window.xsAppDownloadUrl;
            }

            // Cancel any pending hide from a previous close so it doesn't strip
            // is-visible out from under this newly-opened modal later.
            if (hideTimeout) { clearTimeout(hideTimeout); hideTimeout = null; }

            modal.classList.add('is-visible');
            void modal.offsetWidth;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        window.xsHideAppDownloadModal = function () {
            var modal = document.getElementById('app-download-modal');
            if (!modal) {
                return;
            }
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
            if (e.target.closest('[data-xs-app-close]')) {
                window.xsHideAppDownloadModal();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                window.xsHideAppDownloadModal();
            }
        });
    })();
</script>
