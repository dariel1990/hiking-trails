@extends('layouts.admin')

@section('title', 'Carousel Slides')
@section('page-title', 'Carousel Slides')

@section('content')

{{-- Delete Confirmation Modal --}}
<div id="confirm-modal" class="fixed inset-0 bg-black/50 z-[100] items-center justify-center p-4" style="display:none;">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-gray-200">
        <div class="p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Delete this slide?</h3>
            <p class="text-sm text-gray-500">The image file will be permanently removed. This cannot be undone.</p>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('confirm-modal').style.display='none'"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                    Cancel
                </button>
                <form id="delete-form" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="px-4 lg:px-8 py-6 space-y-6">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Upload new slide --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900">Upload New Slide</h2>
            <span class="text-xs text-gray-400">JPG, PNG, GIF, WebP · max 10 MB</span>
        </div>
        <form action="{{ route('admin.carousel.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="grid sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image <span class="text-red-500">*</span></label>
                    <input type="file" name="image" accept="image/*" required
                           class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 border border-gray-300 rounded-lg cursor-pointer">
                    @error('image') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <input type="text" name="caption" value="{{ old('caption') }}" placeholder="Slide title shown on image"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('caption') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-400 mt-1">Lower number = shown first</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Show From</label>
                    <input type="date" name="starts_at" value="{{ old('starts_at', now()->toDateString()) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('starts_at') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Show Until</label>
                    <input type="date" name="ends_at" value="{{ old('ends_at') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @error('ends_at') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep showing indefinitely</p>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Upload Slide
                </button>
            </div>
        </form>
    </div>

    {{-- Images already in storage but not in database --}}
    @if($unregistered->isNotEmpty())
    <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-100 flex items-center gap-3 bg-amber-50">
            <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h2 class="text-base font-semibold text-amber-900">Images Found in Storage</h2>
                <p class="text-xs text-amber-700 mt-0.5">These files exist in the slide-show folder but are not yet managed. Click <strong>Add to Carousel</strong> to register them.</p>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-6">
            @foreach($unregistered as $filename)
            <div class="group flex flex-col gap-2">
                <div class="aspect-video rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                    <img src="{{ asset('storage/slide-show/' . rawurlencode($filename)) }}"
                         alt="{{ $filename }}"
                         data-zoomable data-caption="{{ $filename }}"
                         class="w-full h-full object-cover cursor-zoom-in hover:opacity-90 transition-opacity">
                </div>
                <p class="text-xs text-gray-500 truncate" title="{{ $filename }}">{{ $filename }}</p>
                <form action="{{ route('admin.carousel.import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="filename" value="{{ $filename }}">
                    <button type="submit"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold py-1.5 px-3 rounded-lg transition-colors">
                        Add to Carousel
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Existing slides --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">
                Current Slides
                <span class="ml-2 text-sm font-normal text-gray-400">({{ $slides->count() }} total)</span>
            </h2>
        </div>

        @if($slides->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">No slides yet. Upload one above.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($slides as $slide)
                <div class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">

                    {{-- Thumbnail --}}
                    <div class="w-24 h-16 rounded-lg overflow-hidden bg-gray-100 shrink-0 border border-gray-200">
                        <img src="{{ $slide->url }}" alt="{{ $slide->caption }}"
                             data-zoomable data-caption="{{ $slide->caption }}"
                             class="w-full h-full object-cover cursor-zoom-in hover:opacity-90 transition-opacity">
                    </div>

                    {{-- Edit form --}}
                    <form action="{{ route('admin.carousel.update', $slide) }}" method="POST"
                          class="flex-1 flex flex-col gap-2">
                        @csrf @method('PATCH')

                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex-1 min-w-40">
                                <input type="text" name="caption" value="{{ $slide->caption }}"
                                       placeholder="Caption"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div class="w-20">
                                <input type="number" name="sort_order" value="{{ $slide->sort_order }}" min="0"
                                       title="Sort order"
                                       class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                       {{ $slide->is_active ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                Active
                            </label>

                            @if($slide->is_active)
                                @if(!$slide->is_within_schedule)
                                    <span class="text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-full px-2 py-0.5">
                                        {{ $slide->starts_at && \Illuminate\Support\Carbon::today()->lt($slide->starts_at) ? 'Scheduled' : 'Expired' }}
                                    </span>
                                @else
                                    <span class="text-xs font-semibold text-green-700 bg-green-50 border border-green-200 rounded-full px-2 py-0.5">
                                        Live
                                    </span>
                                @endif
                            @endif

                            <button type="submit"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                Save
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <label class="text-xs text-gray-500">From</label>
                                <input type="date" name="starts_at" value="{{ $slide->starts_at?->toDateString() }}"
                                       class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div class="flex items-center gap-1.5">
                                <label class="text-xs text-gray-500">To</label>
                                <input type="date" name="ends_at" value="{{ $slide->ends_at?->toDateString() }}"
                                       class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <p class="text-xs text-gray-400">Leave blank to control manually</p>
                        </div>
                    </form>

                    {{-- Delete --}}
                    <button type="button"
                            onclick="confirmDelete('{{ route('admin.carousel.destroy', $slide) }}')"
                            class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors shrink-0"
                            title="Delete slide">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>

                </div>
                @endforeach
            </div>
        @endif
    </div>

{{-- Image Zoom Modal --}}
<div id="image-zoom-modal" class="hidden fixed inset-0 z-[200] items-center justify-center p-4 cursor-zoom-out">
    <div id="image-zoom-backdrop" class="absolute inset-0 bg-black/90 opacity-0 transition-opacity duration-200"></div>
    <button type="button" id="image-zoom-close"
            class="absolute top-4 right-4 z-10 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-2 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <div class="relative max-w-6xl max-h-[90vh] flex flex-col items-center gap-3">
        <img id="image-zoom-img" src="" alt=""
             class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl cursor-default opacity-0 scale-95 transition-all duration-200 ease-out">
        <p id="image-zoom-caption" class="text-white/80 text-sm"></p>
    </div>
</div>

</div>

<script>
function confirmDelete(url) {
    document.getElementById('delete-form').action = url;
    document.getElementById('confirm-modal').style.display = 'flex';
}

(function () {
    const modal = document.getElementById('image-zoom-modal');
    const backdrop = document.getElementById('image-zoom-backdrop');
    const img = document.getElementById('image-zoom-img');
    const caption = document.getElementById('image-zoom-caption');

    function openImageZoom(src, captionText) {
        img.src = src;
        caption.textContent = captionText || '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Force layout so the opacity/scale transition actually plays.
        void modal.offsetWidth;
        backdrop.classList.remove('opacity-0');
        img.classList.remove('opacity-0', 'scale-95');
    }

    function closeImageZoom() {
        backdrop.classList.add('opacity-0');
        img.classList.add('opacity-0', 'scale-95');
        setTimeout(function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            img.src = '';
        }, 200);
    }

    document.querySelectorAll('img[data-zoomable]').forEach(function (el) {
        el.addEventListener('click', function () {
            openImageZoom(el.currentSrc || el.src, el.dataset.caption);
        });
    });

    backdrop.addEventListener('click', closeImageZoom);
    document.getElementById('image-zoom-close').addEventListener('click', closeImageZoom);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) { closeImageZoom(); }
    });
})();
</script>
@endsection
