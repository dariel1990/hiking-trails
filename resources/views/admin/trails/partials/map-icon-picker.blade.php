{{-- Custom Map Icon picker — shared by create + edit trail forms.
     Carries a storage-relative path in the hidden `icon_image` input;
     files are uploaded/reused via the admin trail-icons endpoints. --}}
@php($currentIconImage = old('icon_image', $trail->icon_image ?? ''))
<div class="md:col-span-2 space-y-2">
    <label class="text-sm font-medium leading-none">
        Custom Map Icon <span class="text-gray-400">(Optional)</span>
    </label>
    <p class="text-xs text-gray-500">Shown as this trail's marker on the interactive map. Leave empty to use the activity icon.</p>

    {{-- Text/emoji icon — used when no image is selected (image overrides it) --}}
    <div class="flex items-center gap-2">
        <input type="text" name="icon" value="{{ old('icon', $trail->icon ?? '') }}" maxlength="16" placeholder="e.g. ⛰️"
               class="flex h-10 w-24 rounded-md border border-input bg-background px-3 py-2 text-sm text-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring @error('icon') border-red-300 @enderror">
        <span class="text-xs text-gray-500">Text/emoji icon — a selected image below overrides it</span>
    </div>
    @error('icon')
        <p class="text-sm text-red-500">{{ $message }}</p>
    @enderror

    {{-- Gallery of previously uploaded icons --}}
    <div class="flex flex-wrap gap-2 min-h-[2.5rem] items-center" id="trail-icon-gallery">
        <span class="text-xs text-muted-foreground italic self-center">Loading icons…</span>
    </div>

    {{-- Selected icon preview --}}
    <div id="trail-icon-image-preview" class="hidden items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-md px-3 py-1.5">
        <img id="trail-icon-image-preview-img" src="" alt="" class="w-6 h-6 object-contain rounded">
        <span id="trail-icon-image-name" class="truncate flex-1"></span>
        <button type="button" id="trail-icon-image-clear" class="ml-auto text-red-500 hover:text-red-700 shrink-0" title="Remove custom icon">✕</button>
    </div>

    {{-- Upload new icon --}}
    <div class="flex items-center gap-2">
        <label for="trail-icon-image-input" class="cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-dashed border-input px-3 py-1.5 text-xs text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Upload new icon
        </label>
        <input type="file" id="trail-icon-image-input" accept="image/*" class="hidden">
        <span id="trail-icon-upload-status" class="text-xs text-muted-foreground"></span>
    </div>

    {{-- Hidden field carrying the selected storage path --}}
    <input type="hidden" name="icon_image" id="trail-icon-image-path" value="{{ $currentIconImage }}">
    @error('icon_image')
        <p class="text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

    function selectTrailIcon(path, url) {
        document.getElementById('trail-icon-image-path').value = path;

        const preview = document.getElementById('trail-icon-image-preview');
        const previewImg = document.getElementById('trail-icon-image-preview-img');
        const previewName = document.getElementById('trail-icon-image-name');
        if (preview && previewImg) {
            previewImg.src = url;
            if (previewName) { previewName.textContent = path.split('/').pop(); }
            preview.classList.remove('hidden');
            preview.classList.add('flex');
        }

        document.querySelectorAll('.trail-icon-thumb').forEach(t => {
            t.classList.toggle('border-primary', t.dataset.path === path);
            t.classList.toggle('border-transparent', t.dataset.path !== path);
        });
    }

    function clearTrailIcon() {
        document.getElementById('trail-icon-image-path').value = '';

        const preview = document.getElementById('trail-icon-image-preview');
        if (preview) {
            preview.classList.add('hidden');
            preview.classList.remove('flex');
        }

        document.querySelectorAll('.trail-icon-thumb').forEach(t => {
            t.classList.remove('border-primary');
            t.classList.add('border-transparent');
        });
    }

    function renderTrailIconGallery(icons) {
        const gallery = document.getElementById('trail-icon-gallery');
        if (!gallery) { return; }

        if (!icons.length) {
            gallery.innerHTML = '<span class="text-xs text-muted-foreground italic self-center">No custom icons yet</span>';
            return;
        }

        gallery.innerHTML = icons.map(icon => `
            <div class="relative group" data-icon-wrapper="${icon.path}">
                <button type="button" data-path="${icon.path}" data-url="${icon.url}"
                    class="trail-icon-thumb w-10 h-10 rounded-md border-2 border-transparent hover:border-primary overflow-hidden bg-white flex items-center justify-center p-0.5 transition-colors"
                    title="${icon.path.split('/').pop()}">
                    <img src="${icon.url}" class="w-full h-full object-contain" alt="">
                </button>
                <button type="button" data-delete-path="${icon.path}"
                    class="trail-icon-delete absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity shadow"
                    title="Delete this custom icon">✕</button>
            </div>
        `).join('');

        gallery.querySelectorAll('.trail-icon-thumb').forEach(btn => {
            btn.addEventListener('click', () => selectTrailIcon(btn.dataset.path, btn.dataset.url));
        });
        gallery.querySelectorAll('.trail-icon-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteTrailIcon(btn.dataset.deletePath, btn.closest('[data-icon-wrapper]'));
            });
        });

        // Re-highlight the current selection after (re)render
        const current = document.getElementById('trail-icon-image-path').value;
        if (current) {
            gallery.querySelectorAll('.trail-icon-thumb').forEach(t => {
                t.classList.toggle('border-primary', t.dataset.path === current);
            });
        }
    }

    async function deleteTrailIcon(path, wrapperEl) {
        if (!confirm('Delete this custom icon? This cannot be undone.')) { return; }

        const requestDelete = async (force = false) => {
            const res = await fetch('{{ route('admin.trails.trail-icons.delete') }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
                body: JSON.stringify({ path, force }),
            });
            if (!res.ok) { throw new Error('Failed to delete icon'); }
            return res.json();
        };

        try {
            let data = await requestDelete();

            if (!data.deleted && data.in_use) {
                const proceed = confirm(`This icon is currently used by ${data.in_use} trail(s). Deleting it will revert them to their activity icon. Delete anyway?`);
                if (!proceed) { return; }
                data = await requestDelete(true);
            }

            if (!data.deleted) { return; }

            if (wrapperEl) { wrapperEl.remove(); }

            if (document.getElementById('trail-icon-image-path').value === path) {
                clearTrailIcon();
            }

            const gallery = document.getElementById('trail-icon-gallery');
            if (gallery && !gallery.querySelector('.trail-icon-thumb')) {
                gallery.innerHTML = '<span class="text-xs text-muted-foreground italic self-center">No custom icons yet</span>';
            }
        } catch {
            alert('Failed to delete icon.');
        }
    }

    // Upload handler
    const iconInput = document.getElementById('trail-icon-image-input');
    if (iconInput) {
        iconInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) { return; }

            const statusEl = document.getElementById('trail-icon-upload-status');
            if (statusEl) { statusEl.textContent = 'Uploading…'; }

            const fd = new FormData();
            fd.append('icon', file);
            fd.append('_token', csrf());

            try {
                const res = await fetch('{{ route('admin.trails.trail-icons.upload') }}', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.path && data.url) {
                    selectTrailIcon(data.path, data.url);
                    loadGallery();
                    if (statusEl) { statusEl.textContent = 'Uploaded!'; }
                    setTimeout(() => { if (statusEl) { statusEl.textContent = ''; } }, 2000);
                } else if (statusEl) {
                    statusEl.textContent = 'Upload failed';
                }
            } catch {
                if (statusEl) { statusEl.textContent = 'Upload failed'; }
            }

            iconInput.value = '';
        });
    }

    document.getElementById('trail-icon-image-clear')?.addEventListener('click', clearTrailIcon);

    function loadGallery() {
        fetch('{{ route('admin.trails.trail-icons') }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
        })
        .then(r => r.json())
        .then(renderTrailIconGallery)
        .catch(() => {
            const gallery = document.getElementById('trail-icon-gallery');
            if (gallery) { gallery.innerHTML = '<span class="text-xs text-red-400 italic self-center">Failed to load icons</span>'; }
        });
    }

    loadGallery();

    // Initial preview when editing a trail that already has a custom icon
    @if($currentIconImage)
        selectTrailIcon(@json($currentIconImage), @json(asset('storage/'.$currentIconImage)));
    @endif
});
</script>
