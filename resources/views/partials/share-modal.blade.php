{{--
    Share modal — replica of the trail share modal (trails/show.blade.php),
    parameterized so it can be reused on any public page.

    Include params:
    - $shareId      : unique id for this instance; open the modal with any element
                      carrying data-share-open="{{ $shareId }}"
    - $kicker       : header eyebrow text (e.g. "Share This Tour")
    - $title        : dialog title
    - $subtitle     : dialog subtitle (optional)
    - $shareUrl     : URL to share (defaults to the current URL)
    - $shareText    : text for X/WhatsApp shares
    - $emailSubject : email subject
    - $emailBody    : email body text (link is appended automatically)
--}}
@php
    $shareId = $shareId ?? 'xs-share';
    $kicker = $kicker ?? 'Share This Page';
    $title = $title ?? config('app.name');
    $subtitle = $subtitle ?? '';
    $shareUrl = $shareUrl ?? url()->current();
    $shareText = $shareText ?? ('Check out '.$title.'! 🥾⛰️');
    $emailSubject = $emailSubject ?? ('Check this out: '.$title);
    $emailBody = $emailBody ?? ('I found this and thought you might be interested!'."\n\n".$title);
@endphp

@once
<style>
    @keyframes xsShareFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes xsShareSlideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes xsShareSlideUpDesktop { from { transform: translateY(36px) scale(.98); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

    /* z-index sits above the trail-network mobile sidebar (9999) */
    .xs-share-modal { position: fixed; inset: 0; z-index: 10500; display: none; align-items: flex-end; justify-content: center; pointer-events: none; }
    .xs-share-modal.is-visible { display: flex; }
    .xs-share-modal.is-open { pointer-events: auto; }
    @media (min-width: 640px) { .xs-share-modal { align-items: center; } }

    .xs-share-backdrop { position: absolute; inset: 0; background: rgba(10, 20, 19, 0.66); backdrop-filter: blur(3px); opacity: 0; }
    .xs-share-modal.is-open .xs-share-backdrop { animation: xsShareFadeIn 0.5s ease forwards; }

    .xs-share-dialog {
        position: relative; width: 100%; max-width: 440px; max-height: 92vh; overflow-y: auto;
        background: #fff; color: #1f2937; border-radius: 24px 24px 0 0;
        box-shadow: 0 -20px 60px rgba(0,0,0,.35);
        transform: translateY(100%); opacity: 0;
    }
    .xs-share-modal.is-open .xs-share-dialog { animation: xsShareSlideUp 0.5s cubic-bezier(.22,1,.36,1) forwards; }
    @media (min-width: 640px) {
        .xs-share-dialog { border-radius: 24px; transform: translateY(36px) scale(.98); box-shadow: 0 30px 70px rgba(0,0,0,.4); }
        .xs-share-modal.is-open .xs-share-dialog { animation: xsShareSlideUpDesktop 0.5s cubic-bezier(.22,1,.36,1) forwards; }
    }

    .xs-share-head { position: relative; padding: 28px 28px 22px; text-align: center; color: #fff;
        background: linear-gradient(150deg, #2C5F5D 0%, #1a2e2e 100%); border-radius: 24px 24px 0 0; overflow: hidden; }
    .xs-share-head::after { content: ''; position: absolute; top: -40%; right: -20%; width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(232,123,53,.35), transparent 70%); }
    .xs-share-kicker { font-size: 11px; letter-spacing: .22em; text-transform: uppercase; color: #E87B35; font-weight: 700; position: relative; }
    .xs-share-title { font-size: 20px; font-weight: 700; color: #fff; margin-top: 8px; line-height: 1.25; position: relative; }
    .xs-share-sub { margin-top: 8px; font-size: 13px; color: rgba(255,255,255,.78); line-height: 1.5; position: relative; }

    .xs-share-x { position: absolute; top: 14px; right: 16px; width: 32px; height: 32px; border-radius: 999px;
        background: rgba(255,255,255,.15); color: #fff; font-size: 20px; line-height: 1; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s; z-index: 2; }
    .xs-share-x:hover { background: rgba(255,255,255,.28); }

    .xs-share-body { padding: 22px 28px 6px; }
    .xs-share-link-row { display: flex; gap: 8px; }
    .xs-share-link-input { flex: 1 1 auto; min-width: 0; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 12px;
        background: #f9fafb; color: #374151; font-size: 13px; }
    .xs-share-link-input:focus { outline: none; box-shadow: 0 0 0 2px rgba(44,95,93,.25); }
    .xs-share-copy-btn { flex: 0 0 auto; display: flex; align-items: center; gap: 8px; padding: 10px 16px;
        background: #2C5F5D; color: #fff; border-radius: 12px; border: 0; cursor: pointer; font-weight: 600; font-size: 13px;
        transition: background .2s; white-space: nowrap; }
    .xs-share-copy-btn:hover { background: #234a48; }
    .xs-share-copy-btn.is-copied { background: #2f9e63; }

    .xs-share-actions { padding: 18px 28px 26px; display: grid; grid-template-columns: 1fr; gap: 10px; }
    .xs-share-cta { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; text-align: center;
        font-weight: 600; font-size: 14px; padding: 12px 14px; border-radius: 12px; border: 0; cursor: pointer;
        color: #fff; white-space: nowrap; transition: filter .2s, transform .1s; }
    .xs-share-cta:hover { filter: brightness(.92); }
    .xs-share-cta:active { transform: translateY(1px); }
</style>
@endonce

<div id="{{ $shareId }}-modal" class="xs-share-modal" aria-hidden="true">
    <div class="xs-share-backdrop" data-xs-share-close></div>
    <div class="xs-share-dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $shareId }}-title">
        <div class="xs-share-head">
            <button type="button" class="xs-share-x" data-xs-share-close aria-label="Close">&times;</button>
            <p class="xs-share-kicker">{{ $kicker }}</p>
            <h2 id="{{ $shareId }}-title" class="xs-share-title">{{ $title }}</h2>
            @if($subtitle)
                <p class="xs-share-sub">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="xs-share-body">
            <div class="xs-share-link-row">
                <input type="text"
                       id="{{ $shareId }}-url"
                       readonly
                       value="{{ $shareUrl }}"
                       class="xs-share-link-input">
                <button type="button" id="{{ $shareId }}-copy-btn" class="xs-share-copy-btn">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span id="{{ $shareId }}-copy-text">Copy</span>
                </button>
            </div>
        </div>

        <div class="xs-share-actions">
            <button type="button" data-share-action="facebook" class="xs-share-cta" style="background:#1877F2;">
                <svg style="width:20px;height:20px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Facebook
            </button>
            <button type="button" data-share-action="twitter" class="xs-share-cta" style="background:#000;">
                <svg style="width:20px;height:20px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                X (Twitter)
            </button>
            <button type="button" data-share-action="whatsapp" class="xs-share-cta" style="background:#25D366;">
                <svg style="width:20px;height:20px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                WhatsApp
            </button>
            <button type="button" data-share-action="email" class="xs-share-cta" style="background:#4b5563;">
                <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const shareId = @json($shareId);
    const shareText = @json($shareText);
    const emailSubject = @json($emailSubject);
    const emailBody = @json($emailBody);

    const modal = document.getElementById(shareId + '-modal');
    const urlInput = document.getElementById(shareId + '-url');
    const copyBtn = document.getElementById(shareId + '-copy-btn');
    const copyText = document.getElementById(shareId + '-copy-text');
    if (!modal || !urlInput) { return; }

    let hideTimeout = null;

    function openModal() {
        // Cancel any pending hide from a previous close so it doesn't strip
        // is-visible out from under this newly-opened modal later.
        if (hideTimeout) { clearTimeout(hideTimeout); hideTimeout = null; }
        modal.classList.add('is-visible', 'is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (hideTimeout) { clearTimeout(hideTimeout); }
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        hideTimeout = setTimeout(() => {
            modal.classList.remove('is-visible');
            hideTimeout = null;
        }, 1100);
    }

    document.querySelectorAll('[data-share-open="' + shareId + '"]').forEach(function (btn) {
        btn.addEventListener('click', openModal);
    });

    modal.addEventListener('click', function (e) {
        if (e.target.closest('[data-xs-share-close]')) { closeModal(); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) { closeModal(); }
    });

    copyBtn?.addEventListener('click', async function () {
        const url = urlInput.value;
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
            } else {
                urlInput.select();
                document.execCommand('copy');
            }
            copyText.textContent = 'Copied!';
            copyBtn.classList.add('is-copied');
            setTimeout(() => {
                copyText.textContent = 'Copy';
                copyBtn.classList.remove('is-copied');
            }, 2000);
        } catch (err) {
            console.error('Failed to copy:', err);
            copyText.textContent = 'Failed';
            setTimeout(() => { copyText.textContent = 'Copy'; }, 2000);
        }
    });

    modal.querySelectorAll('[data-share-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = urlInput.value;
            const encodedUrl = encodeURIComponent(url);

            switch (btn.dataset.shareAction) {
                case 'facebook':
                    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl, 'facebook-share', 'width=600,height=400');
                    break;
                case 'twitter':
                    window.open('https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodeURIComponent(shareText), 'twitter-share', 'width=600,height=400');
                    break;
                case 'whatsapp':
                    window.open('https://wa.me/?text=' + encodeURIComponent(shareText + '\n\n') + encodedUrl, 'whatsapp-share');
                    break;
                case 'email':
                    window.location.href = 'mailto:?subject=' + encodeURIComponent(emailSubject)
                        + '&body=' + encodeURIComponent(emailBody + '\nLink: ' + url + '\n\nHappy exploring! 🥾⛰️');
                    break;
            }
        });
    });
})();
</script>
