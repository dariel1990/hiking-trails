@php
    /** @var \App\Models\TrailNetwork|null $trailNetwork */
    $networkVideoUrl = old('video_url', $trailNetwork->video_url ?? '');
    $networkVideoThumb = isset($trailNetwork) && $trailNetwork ? $trailNetwork->video_thumbnail_url : null;
@endphp

<div>
    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-1.5">Video Link (YouTube or Vimeo)</label>
    <input type="url" name="video_url" id="video_url"
           value="{{ $networkVideoUrl }}"
           placeholder="https://www.youtube.com/watch?v=..."
           oninput="updateNetworkVideoPreview(this.value)"
           class="block w-full rounded-lg border-gray-300 shadow-sm px-4 py-2.5 focus:border-green-500 focus:ring-green-500 @error('video_url') border-red-300 @enderror">
    <p class="mt-1 text-xs text-gray-500">Paste a YouTube or Vimeo link to show a video on the public network page</p>
    @error('video_url')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div id="network-video-preview" class="{{ $networkVideoThumb ? '' : 'hidden' }} mt-3">
        <button type="button" onclick="playAdminVideo(document.getElementById('video_url').value)"
                class="relative block w-full max-w-xs aspect-video rounded-lg overflow-hidden bg-gray-900 group">
            <img id="network-video-preview-img" src="{{ $networkVideoThumb ?? '' }}" alt="Video preview"
                 class="absolute inset-0 w-full h-full object-cover {{ $networkVideoThumb ? '' : 'hidden' }}">
            <span class="absolute inset-0 flex items-center justify-center">
                <span class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center shadow group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-gray-900 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </span>
            </span>
        </button>
    </div>
</div>

<script>
    function getAdminVideoEmbedUrl(videoUrl) {
        const youtubeMatch = videoUrl.match(/(?:youtube\.com\/(?:watch\?.*v=|shorts\/|live\/|embed\/)|youtu\.be\/)([^"&?\/\s]{11})/i);
        if (youtubeMatch) {
            return `https://www.youtube.com/embed/${youtubeMatch[1]}`;
        }
        const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            return `https://player.vimeo.com/video/${vimeoMatch[1]}`;
        }
        return null;
    }

    function getAdminVideoThumbUrl(videoUrl) {
        const youtubeMatch = videoUrl.match(/(?:youtube\.com\/(?:watch\?.*v=|shorts\/|live\/|embed\/)|youtu\.be\/)([^"&?\/\s]{11})/i);
        if (youtubeMatch) {
            return `https://img.youtube.com/vi/${youtubeMatch[1]}/hqdefault.jpg`;
        }
        const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            return `https://vumbnail.com/${vimeoMatch[1]}.jpg`;
        }
        return null;
    }

    function updateNetworkVideoPreview(videoUrl) {
        const preview = document.getElementById('network-video-preview');
        const img = document.getElementById('network-video-preview-img');
        if (!preview || !img) { return; }

        const thumb = videoUrl ? getAdminVideoThumbUrl(videoUrl.trim()) : null;
        if (thumb) {
            img.src = thumb;
            img.classList.remove('hidden');
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    }

    function playAdminVideo(videoUrl) {
        if (!videoUrl) { return; }
        const embedUrl = getAdminVideoEmbedUrl(videoUrl.trim());
        if (!embedUrl) { return; }

        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4';
        modal.innerHTML = `
            <div class="relative w-full max-w-4xl">
                <button onclick="this.closest('.fixed.inset-0').remove()"
                    class="fixed top-4 right-4 text-white hover:text-gray-300 bg-gray-900 bg-opacity-75 rounded-full p-2 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="relative bg-black rounded-lg overflow-hidden" style="padding-bottom: 56.25%;">
                    <iframe src="${embedUrl}?autoplay=1"
                        class="absolute top-0 left-0 w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>`;
        modal.addEventListener('click', (e) => {
            if (e.target === modal) { modal.remove(); }
        });
        document.body.appendChild(modal);
    }
</script>
