{{-- Custom map icon image — shared by the create + edit trail network forms.
     Must sit inside the `iconPicker` Alpine scope: it reads/writes the parent's
     `iconImagePath` and `iconImageUrl`. Files are uploaded/reused via the
     admin trail-network icon endpoints. --}}
<div x-data="networkIconGallery()" x-init="loadIcons()" class="mt-4 pt-4 border-t border-gray-200 space-y-2">
    <div class="flex items-center justify-between">
        <p class="text-xs font-medium text-gray-700">Or upload a custom icon image <span class="font-normal text-gray-400">(overrides the emoji)</span></p>
    </div>

    {{-- Gallery of previously uploaded icons --}}
    <div class="flex flex-wrap gap-2 min-h-[2.5rem] items-center">
        <template x-if="loading">
            <span class="text-xs text-gray-400 italic">Loading icons…</span>
        </template>
        <template x-if="!loading && loadFailed">
            <span class="text-xs text-red-400 italic">Failed to load icons</span>
        </template>
        <template x-if="!loading && !loadFailed && icons.length === 0">
            <span class="text-xs text-gray-400 italic">No custom icons yet</span>
        </template>
        <template x-for="icon in icons" :key="icon.path">
            <div class="relative group">
                <button type="button"
                        class="w-10 h-10 rounded-md border-2 overflow-hidden bg-white flex items-center justify-center p-0.5 transition-colors hover:border-green-500"
                        :class="iconImagePath === icon.path ? 'border-green-500' : 'border-transparent'"
                        :title="icon.path.split('/').pop()"
                        x-on:click="selectImage(icon)">
                    <img :src="icon.url" class="w-full h-full object-contain" alt="">
                </button>
                <button type="button"
                        class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity shadow"
                        title="Delete this custom icon"
                        x-on:click.stop="deleteImage(icon)">✕</button>
            </div>
        </template>
    </div>

    {{-- Selected icon preview --}}
    <div x-show="iconImagePath" style="display:none" class="flex items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-md px-3 py-1.5">
        <img :src="iconImageUrl" alt="" class="w-6 h-6 object-contain rounded">
        <span class="truncate flex-1" x-text="iconImagePath ? iconImagePath.split('/').pop() : ''"></span>
        <button type="button" class="ml-auto text-red-500 hover:text-red-700 shrink-0" title="Remove custom icon" x-on:click="clearImage()">✕</button>
    </div>

    {{-- Upload new icon --}}
    <div class="flex items-center gap-2">
        <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-dashed border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Upload new icon
            <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" x-on:change="uploadImage($event)">
        </label>
        <span class="text-xs text-gray-500" x-text="uploadStatus"></span>
    </div>
    <p class="text-[11px] text-gray-400">PNG, JPG, WEBP or GIF up to 2MB. Resized to 256px.</p>

    <input type="hidden" name="icon_image" :value="iconImagePath || ''">
    @error('icon_image')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<script>
function networkIconGallery() {
    const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

    return {
        icons: [],
        loading: true,
        loadFailed: false,
        uploadStatus: '',

        async loadIcons() {
            this.loading = true;
            this.loadFailed = false;
            try {
                const res = await fetch('{{ route('admin.trail-networks.icons') }}', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) { throw new Error('Failed to load icons'); }
                this.icons = await res.json();
            } catch {
                this.loadFailed = true;
            }
            this.loading = false;
        },

        selectImage(icon) {
            this.iconImagePath = icon.path;
            this.iconImageUrl = icon.url;
        },

        clearImage() {
            this.iconImagePath = null;
            this.iconImageUrl = null;
        },

        async uploadImage(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) { return; }

            this.uploadStatus = 'Uploading…';
            const formData = new FormData();
            formData.append('icon', file);

            try {
                const res = await fetch('{{ route('admin.trail-networks.icons.upload') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: formData,
                });
                const data = await res.json();
                if (!res.ok || !data.path) {
                    throw new Error(data.message || 'Upload failed');
                }
                this.icons.push({ path: data.path, url: data.url });
                this.selectImage(data);
                this.uploadStatus = 'Uploaded!';
                setTimeout(() => { this.uploadStatus = ''; }, 2000);
            } catch (e) {
                this.uploadStatus = e.message || 'Upload failed';
            }

            event.target.value = '';
        },

        async deleteImage(icon) {
            if (!confirm('Delete this custom icon? This cannot be undone.')) { return; }

            const requestDelete = async (force = false) => {
                const res = await fetch('{{ route('admin.trail-networks.icons.delete') }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: JSON.stringify({ path: icon.path, force }),
                });
                if (!res.ok) { throw new Error('Failed to delete icon'); }
                return res.json();
            };

            try {
                let data = await requestDelete();
                if (!data.deleted && data.in_use) {
                    const proceed = confirm(`This icon is used by ${data.in_use} trail network(s). Deleting it will revert them to their emoji icon. Delete anyway?`);
                    if (!proceed) { return; }
                    data = await requestDelete(true);
                }
                if (!data.deleted) { return; }

                this.icons = this.icons.filter(i => i.path !== icon.path);
                if (this.iconImagePath === icon.path) { this.clearImage(); }
            } catch {
                alert('Failed to delete icon.');
            }
        },
    };
}
</script>
