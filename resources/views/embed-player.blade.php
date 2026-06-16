<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Video</title>
    <style>
        html, body { margin: 0; background: #000; height: 100%; overflow: hidden; }
        #player { position: fixed; top: 0; left: 0; width: 100%; height: 100%; }
    </style>
</head>
<body>
    {{-- Minimal, video-only YouTube player. Rendered by real Chrome (via the app's Custom Tab),
         where the embed plays reliably — unlike Android's System WebView (error 152). --}}
    <div id="player"></div>
    <script>
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        document.body.appendChild(tag);
        function onYouTubeIframeAPIReady() {
            new YT.Player('player', {
                videoId: @json($videoId),
                width: '100%',
                height: '100%',
                playerVars: { autoplay: 1, playsinline: 1, rel: 0, modestbranding: 1, fs: 1 },
                events: { onReady: function (e) { e.target.playVideo(); } }
            });
        }
    </script>
</body>
</html>
