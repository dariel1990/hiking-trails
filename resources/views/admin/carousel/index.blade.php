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
                    <input type="hidden" name="return_status" value="{{ $status }}">
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

    @if(session('error') || $errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm" role="alert">
            @if(session('error'))<p>{{ session('error') }}</p>@endif
            @foreach($errors->all() as $message)
                <p>{{ $message }}</p>
            @endforeach
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
    @php
        $tabs = ['active' => 'Active', 'inactive' => 'Inactive', 'all' => 'All'];
        $fieldClass = 'border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500';
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-base font-semibold text-gray-900">Slides</h2>

            {{-- Status filter; Active is the default --}}
            <nav class="inline-flex rounded-lg bg-gray-100 p-1 text-sm font-medium" aria-label="Filter slides by status">
                @foreach($tabs as $value => $label)
                    <a href="{{ route('admin.carousel.index', $value === 'active' ? [] : ['status' => $value]) }}"
                       @if($status === $value) aria-current="page" @endif
                       class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 transition-colors {{ $status === $value ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                        {{ $label }}
                        <span class="rounded-full px-1.5 text-xs tabular-nums {{ $status === $value ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-600' }}">{{ $counts[$value] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        @if($slides->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm">
                    @if($counts['all'] === 0)
                        No slides yet. Upload one above.
                    @elseif($status === 'active')
                        No active slides. The carousel is hidden on the events page.
                    @else
                        No {{ $status === 'inactive' ? 'inactive' : '' }} slides.
                    @endif
                </p>
            </div>
        @else
            {{-- One hidden form per row; the row's inputs point at it with form="…" (a form can't wrap a <tr>). --}}
            @foreach($slides as $slide)
                <form id="slide-form-{{ $slide->id }}" action="{{ route('admin.carousel.update', $slide) }}" method="POST" class="hidden">
                    @csrf @method('PATCH')
                    <input type="hidden" name="return_status" value="{{ $status }}">
                </form>
            @endforeach

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Slide</th>
                            <th scope="col" class="px-3 py-3">Status</th>
                            <th scope="col" class="px-3 py-3">Schedule</th>
                            <th scope="col" class="px-3 py-3">Calendar</th>
                            <th scope="col" class="px-3 py-3 text-center">Order</th>
                            <th scope="col" class="px-3 py-3 text-center">Active</th>
                            <th scope="col" class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($slides as $slide)
                            @php $formId = 'slide-form-'.$slide->id; @endphp
                            <tr class="align-middle hover:bg-gray-50 transition-colors {{ $slide->is_active ? '' : 'bg-gray-50/60' }}">
                                {{-- Slide: thumbnail + caption --}}
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-24 h-14 rounded-lg overflow-hidden bg-gray-100 shrink-0 border border-gray-200 {{ $slide->is_active ? '' : 'opacity-60 grayscale' }}">
                                            <img src="{{ $slide->url }}" alt="{{ $slide->caption }}"
                                                 data-zoomable data-caption="{{ $slide->caption }}"
                                                 class="w-full h-full object-cover cursor-zoom-in hover:opacity-90 transition-opacity">
                                        </div>
                                        <input type="text" name="caption" form="{{ $formId }}" value="{{ $slide->caption }}"
                                               placeholder="Caption" aria-label="Caption"
                                               class="{{ $fieldClass }} w-full min-w-[12rem] px-3 py-1.5">
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-3 py-3 whitespace-nowrap">
                                    @if(! $slide->is_active && $slide->calendar_ended_at && $slide->calendar_last_day)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600"
                                              title="Turned off automatically after its calendar event finished. Switch Active on and save to show it again.">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Event ended {{ $slide->calendar_last_day->format('M j') }}
                                        </span>
                                    @elseif($slide->has_ended && ! $slide->show_on_calendar)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600"
                                              title="Turned off automatically after its end date. Change or clear the To date to show it again.">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Ended {{ $slide->ends_at->format('M j') }}
                                        </span>
                                    @elseif(! $slide->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Inactive
                                        </span>
                                    @elseif($slide->has_ended)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700"
                                              title="Out of the slideshow since {{ $slide->ends_at->format('M j') }}. Stays on the events calendar until its event's last day{{ $slide->calendar_last_day ? ' ('.$slide->calendar_last_day->format('M j').')' : '' }}.">
                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>Calendar only
                                        </span>
                                    @elseif(! $slide->is_within_schedule)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Starts {{ $slide->starts_at->format('M j') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-green-200 bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Live
                                        </span>
                                    @endif
                                </td>

                                {{-- Schedule --}}
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-1.5 whitespace-nowrap">
                                        <input type="date" name="starts_at" form="{{ $formId }}" value="{{ $slide->starts_at?->toDateString() }}"
                                               aria-label="Show from" class="{{ $fieldClass }} px-2 py-1 text-xs">
                                        <span class="text-gray-400">→</span>
                                        <input type="date" name="ends_at" form="{{ $formId }}" value="{{ $slide->ends_at?->toDateString() }}"
                                               aria-label="Show until" title="Leave blank to keep showing" class="{{ $fieldClass }} px-2 py-1 text-xs">
                                    </div>
                                </td>

                                {{-- Events calendar entry --}}
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <a href="{{ route('admin.carousel.calendar.edit', $slide) }}"
                                       title="{{ $slide->show_on_calendar ? 'Shown on the events calendar. Click to edit its details.' : 'Add this slide to the events calendar' }}"
                                       class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors {{ $slide->show_on_calendar ? 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100' : 'border-dashed border-gray-300 text-gray-500 hover:bg-gray-100' }}">
                                        @if($slide->show_on_calendar && $slide->calendar_date)
                                            📅 {{ $slide->calendar_date->format('M j') }}{{ $slide->calendar_end_date ? ' – '.$slide->calendar_end_date->format('M j') : '' }}
                                        @else
                                            + Add to calendar
                                        @endif
                                    </a>
                                </td>

                                {{-- Order --}}
                                <td class="px-3 py-3 text-center">
                                    <input type="number" name="sort_order" form="{{ $formId }}" value="{{ $slide->sort_order }}" min="0"
                                           aria-label="Sort order" class="{{ $fieldClass }} w-16 px-2 py-1 text-center">
                                </td>

                                {{-- Active toggle --}}
                                <td class="px-3 py-3 text-center">
                                    <input type="hidden" name="is_active" value="0" form="{{ $formId }}">
                                    <label class="relative inline-flex cursor-pointer items-center" title="Show on the events page">
                                        <input type="checkbox" name="is_active" value="1" form="{{ $formId }}" class="peer sr-only"
                                               {{ $slide->is_active ? 'checked' : '' }} aria-label="Active">
                                        <span class="h-6 w-11 rounded-full bg-gray-300 transition-colors peer-checked:bg-green-600 peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500 peer-focus-visible:ring-offset-2"></span>
                                        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                                    </label>
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="submit" form="{{ $formId }}"
                                                class="bg-gray-900 hover:bg-gray-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                            Save
                                        </button>
                                        <button type="button"
                                                onclick="confirmDelete('{{ route('admin.carousel.destroy', $slide) }}')"
                                                class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                                title="Delete slide" aria-label="Delete slide">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="border-t border-gray-100 px-6 py-3 text-xs text-gray-400">
                Live slides show on the events page in order (lowest first). Leave "Show until" blank to keep a slide showing; slides are switched off automatically after their end date.
                Slides on the events calendar are switched off (slideshow and calendar) after their event's last day; switch them back on any time.
            </p>
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
