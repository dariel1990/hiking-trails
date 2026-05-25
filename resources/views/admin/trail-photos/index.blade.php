@extends('layouts.admin')

@section('title', 'Community Photos')
@section('page-title', 'Community Photos')

@section('content')
<div class="space-y-6" x-data="trailPhotoModeration()">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Community Photos</h2>
            <p class="text-sm text-muted-foreground">Review and moderate user-submitted trail photos.</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Status tabs --}}
    <div class="rounded-lg border bg-card p-4">
        <div class="flex flex-wrap items-center gap-2">
            @foreach([
                'pending' => ['label' => 'Pending', 'class' => 'bg-amber-100 text-amber-800'],
                'approved' => ['label' => 'Approved', 'class' => 'bg-emerald-100 text-emerald-800'],
                'rejected' => ['label' => 'Rejected', 'class' => 'bg-red-100 text-red-800'],
            ] as $key => $meta)
                @php($isActive = $status === $key)
                <a href="{{ route('admin.trail-photos.index', array_filter(['status' => $key, 'trail_id' => $trailId])) }}"
                    class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium border transition-colors {{ $isActive ? 'border-gray-900 bg-gray-900 text-white' : 'border-input bg-background text-gray-700 hover:bg-gray-50' }}">
                    {{ $meta['label'] }}
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $meta['class'] }}">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach

            {{-- Trail filter --}}
            <form method="GET" action="{{ route('admin.trail-photos.index') }}" class="ml-auto flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <label for="trail-filter" class="text-sm text-muted-foreground">Trail:</label>
                <select name="trail_id" id="trail-filter" onchange="this.form.submit()"
                    class="h-9 rounded-md border-input bg-background px-2 text-sm">
                    <option value="">All trails</option>
                    @foreach($trailsWithSubmissions as $option)
                        <option value="{{ $option->id }}" @selected($trailId === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Bulk actions --}}
    <div x-show="selected.length > 0" x-cloak class="rounded-lg border bg-indigo-50 border-indigo-200 px-4 py-3 flex items-center justify-between">
        <p class="text-sm text-indigo-900"><span x-text="selected.length"></span> selected</p>
        <div class="flex items-center gap-2">
            <button type="button" @click="bulk('approved')"
                class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">
                Approve selected
            </button>
            <button type="button" @click="bulk('rejected')"
                class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">
                Reject selected
            </button>
        </div>
    </div>

    {{-- Grid --}}
    @if($photos->count() === 0)
        <div class="rounded-lg border bg-card p-12 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-muted flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5V7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5Z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold">No {{ $status }} photos</h3>
            <p class="text-sm text-muted-foreground mt-1">Nothing to moderate here right now.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($photos as $photo)
                @php($previewUrl = $photo->thumbnail_url ?? $photo->image_url)
                <div id="photo-{{ $photo->id }}" class="rounded-lg border bg-card overflow-hidden flex flex-col">
                    <div class="relative aspect-video bg-gray-100">
                        @if($previewUrl)
                            <button type="button" class="block w-full h-full" @click="lightbox = '{{ $photo->image_url }}'">
                                <img src="{{ $previewUrl }}" alt="Submission for {{ $photo->trail?->name }}" class="w-full h-full object-cover" loading="lazy">
                            </button>
                        @else
                            <div class="flex items-center justify-center h-full text-sm text-muted-foreground">No file (rejected)</div>
                        @endif
                        <label class="absolute top-2 left-2 bg-white/90 rounded p-1 cursor-pointer">
                            <input type="checkbox" :checked="selected.includes({{ $photo->id }})"
                                @change="toggle({{ $photo->id }})"
                                class="w-4 h-4 accent-emerald-600">
                        </label>
                        <span @class([
                            'absolute top-2 right-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
                            'bg-amber-100 text-amber-800' => $photo->isPending(),
                            'bg-emerald-100 text-emerald-800' => $photo->isApproved(),
                            'bg-red-100 text-red-800' => $photo->isRejected(),
                        ])>{{ ucfirst($photo->status) }}</span>
                    </div>

                    <div class="p-4 space-y-3 flex-1 flex flex-col">
                        <div>
                            <a href="{{ route('trails.show', $photo->trail) }}" target="_blank" class="font-semibold hover:underline">{{ $photo->trail?->name ?? 'Trail #'.$photo->trail_id }}</a>
                            <p class="text-xs text-muted-foreground">
                                Submitted {{ $photo->created_at->diffForHumans() }}
                                @if($photo->reviewer)
                                    · Reviewed by {{ $photo->reviewer->name }} {{ $photo->reviewed_at?->diffForHumans() }}
                                @endif
                            </p>
                        </div>

                        @if($photo->caption)
                            <p class="text-sm text-gray-700">{{ $photo->caption }}</p>
                        @endif

                        <p class="text-xs text-muted-foreground">
                            By {{ $photo->name ?: 'Anonymous' }}
                        </p>

                        <div class="mt-auto pt-3 border-t flex items-center gap-2">
                            @if(! $photo->isApproved())
                                <form method="POST" action="{{ route('admin.trail-photos.update', $photo) }}" class="flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">
                                        Approve
                                    </button>
                                </form>
                            @endif

                            @if(! $photo->isRejected())
                                <form method="POST" action="{{ route('admin.trail-photos.update', $photo) }}" class="flex-1"
                                    onsubmit="return confirm('Reject this photo? The image files will be deleted but the submission record will be kept for audit.')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="w-full inline-flex items-center justify-center rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">
                                        Reject
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.trail-photos.destroy', $photo) }}"
                                onsubmit="return confirm('Permanently delete this submission? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Permanently delete" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-input text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($photos->hasPages())
            <div class="flex items-center justify-between border-t pt-4">
                <div class="text-sm text-muted-foreground">
                    Showing {{ $photos->firstItem() }}–{{ $photos->lastItem() }} of {{ $photos->total() }}
                </div>
                {{ $photos->links() }}
            </div>
        @endif
    @endif

    {{-- Lightbox --}}
    <div x-show="lightbox" x-cloak @click="lightbox = null" @keydown.escape.window="lightbox = null"
        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 cursor-zoom-out">
        <img :src="lightbox" alt="Full preview" class="max-h-[90vh] max-w-[90vw] object-contain">
    </div>

    {{-- Hidden bulk-action form --}}
    <form method="POST" action="{{ route('admin.trail-photos.bulk') }}" id="bulk-form" class="hidden">
        @csrf
        <input type="hidden" name="status" id="bulk-status">
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="photo_ids[]" :value="id">
        </template>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('trailPhotoModeration', () => ({
            selected: [],
            lightbox: null,
            toggle(id) {
                const idx = this.selected.indexOf(id);
                if (idx === -1) this.selected.push(id);
                else this.selected.splice(idx, 1);
            },
            bulk(status) {
                if (this.selected.length === 0) return;
                const verb = status === 'approved' ? 'approve' : 'reject';
                if (!confirm(`${this.selected.length === 1 ? 'This photo' : `${this.selected.length} photos`} will be ${verb}d. Continue?`)) return;
                document.getElementById('bulk-status').value = status;
                // Re-render hidden inputs from this.selected
                const form = document.getElementById('bulk-form');
                form.querySelectorAll('input[name="photo_ids[]"]').forEach(el => el.remove());
                this.selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'photo_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                form.submit();
            },
        }));
    });
</script>
@endpush
@endsection
