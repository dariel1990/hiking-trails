{{--
    Click-to-play video embed for models using App\Models\Concerns\HasVideoEmbed.

    Include params:
    - $model   : model with a video_url column + HasVideoEmbed accessors
    - $embedId : unique id for this embed instance on the page
--}}
@php
    $embedId = $embedId ?? 'xs-video-embed';
@endphp

@if($model->video_url)
    <button type="button"
            id="{{ $embedId }}"
            class="xs-ve-tile"
            data-video-url="{{ $model->video_url }}"
            onclick="xsVeOpen(this.dataset.videoUrl)"
            aria-label="Play video">
        @if($model->video_thumbnail_url)
            <img src="{{ $model->video_thumbnail_url }}" alt="Video thumbnail" loading="lazy">
        @endif
        <span class="xs-ve-play">
            <span>
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
            </span>
        </span>
    </button>

    @once
        <style>
            .xs-ve-tile { position: relative; display: block; width: 100%; aspect-ratio: 16 / 9; border-radius: 12px;
                overflow: hidden; background: #111827; border: 0; padding: 0; cursor: pointer; }
            .xs-ve-tile img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
            .xs-ve-play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
                background: rgba(0,0,0,.15); transition: background .2s; }
            .xs-ve-tile:hover .xs-ve-play { background: rgba(0,0,0,.3); }
            .xs-ve-play > span { width: 56px; height: 56px; border-radius: 999px; background: rgba(255,255,255,.92);
                display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,.35);
                transition: transform .2s; }
            .xs-ve-tile:hover .xs-ve-play > span { transform: scale(1.08); }
            .xs-ve-play svg { width: 22px; height: 22px; color: #111827; margin-left: 3px; }

            .xs-ve-modal { position: fixed; inset: 0; z-index: 10400; display: none; align-items: center;
                justify-content: center; background: rgba(10, 20, 19, 0.85); backdrop-filter: blur(3px); padding: 16px; }
            .xs-ve-modal.is-open { display: flex; }
            .xs-ve-dialog { position: relative; width: 100%; max-width: 960px; }
            .xs-ve-close { position: fixed; top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 999px;
                background: rgba(255,255,255,.15); color: #fff; font-size: 22px; line-height: 1; border: 0; cursor: pointer;
                display: flex; align-items: center; justify-content: center; transition: background .2s; }
            .xs-ve-close:hover { background: rgba(255,255,255,.3); }
            .xs-ve-frame { position: relative; width: 100%; padding-bottom: 56.25%; background: #000;
                border-radius: 12px; overflow: hidden; box-shadow: 0 30px 70px rgba(0,0,0,.5); }
            .xs-ve-frame > div, .xs-ve-frame > iframe { position: absolute; inset: 0; width: 100%; height: 100%; }
        </style>

        <div id="xs-ve-modal" class="xs-ve-modal" aria-hidden="true">
            <div class="xs-ve-dialog" role="dialog" aria-modal="true" aria-label="Video player">
                <button type="button" class="xs-ve-close" onclick="xsVeClose()" aria-label="Close">&times;</button>
                <div class="xs-ve-frame" id="xs-ve-frame"></div>
            </div>
        </div>

        <script>
            // Loads the YouTube IFrame Player API once, resolving when YT.Player is ready.
            let _xsVeYtApiPromise = null;
            function _xsVeEnsureYouTubeApi() {
                if (window.YT && window.YT.Player) { return Promise.resolve(); }
                if (_xsVeYtApiPromise) { return _xsVeYtApiPromise; }
                _xsVeYtApiPromise = new Promise(function (resolve) {
                    const prev = window.onYouTubeIframeAPIReady;
                    window.onYouTubeIframeAPIReady = function () { if (prev) { prev(); } resolve(); };
                    const tag = document.createElement('script');
                    tag.src = 'https://www.youtube.com/iframe_api';
                    document.head.appendChild(tag);
                });
                return _xsVeYtApiPromise;
            }

            // Mounts an autoplaying YouTube player using the JS API (origin-aware, works in the app WebView).
            function _xsVeMountYouTube(targetId, videoId) {
                _xsVeEnsureYouTubeApi().then(function () {
                    const el = document.getElementById(targetId);
                    if (!el) { return; } // modal already closed/navigated
                    new YT.Player(targetId, {
                        videoId: videoId,
                        host: 'https://www.youtube.com',
                        playerVars: {
                            autoplay: 1, playsinline: 1, rel: 0, modestbranding: 1,
                            origin: window.location.origin
                        },
                        events: { onReady: function (e) { e.target.playVideo(); } }
                    });
                });
            }

            function xsVeOpen(videoUrl) {
                if (!videoUrl) { return; }

                // Inside the native app: YouTube refuses plain embeds in the WebView (error 152),
                // so hand playback to the native player when available.
                if (window.Offline && typeof window.Offline.playVideo === 'function') {
                    window.Offline.playVideo(videoUrl);
                    return;
                }

                const youtubeMatch = videoUrl.match(/(?:youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/|live\/)|youtu\.be\/)([\w-]{11})/i);
                const vimeoMatch = videoUrl.match(/vimeo\.com\/(?:video\/)?(\d+)/);
                const modal = document.getElementById('xs-ve-modal');
                const frame = document.getElementById('xs-ve-frame');
                if (!modal || !frame) { return; }

                if (youtubeMatch) {
                    frame.innerHTML = '<div id="xs-ve-yt-target"></div>';
                    _xsVeMountYouTube('xs-ve-yt-target', youtubeMatch[1]);
                } else if (vimeoMatch) {
                    frame.innerHTML = `<iframe src="https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1&playsinline=1"
                        frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`;
                } else {
                    window.open(videoUrl, '_blank', 'noopener');
                    return;
                }

                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function xsVeClose() {
                const modal = document.getElementById('xs-ve-modal');
                const frame = document.getElementById('xs-ve-frame');
                if (!modal) { return; }
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                if (frame) { frame.innerHTML = ''; } // destroy iframe so audio stops
            }

            document.getElementById('xs-ve-modal')?.addEventListener('click', function (e) {
                if (e.target === this) { xsVeClose(); }
            });

            document.addEventListener('keydown', function (e) {
                const modal = document.getElementById('xs-ve-modal');
                if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) { xsVeClose(); }
            });
        </script>
    @endonce
@endif
