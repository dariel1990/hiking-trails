<script>
/**
 * Event gallery editor. Photos are uploaded one by one to a temporary folder
 * as soon as they are picked; the form then submits the gallery as an ordered
 * list of existing ids, uploaded photo paths and video links.
 */
function eventGallery(existing) {
    let nextKey = 0;
    const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

    return {
        items: existing.map((item) => ({ ...item, key: ++nextKey, uploading: false })),
        newVideoUrl: '',
        error: '',

        addFiles(files) {
            this.error = '';
            Array.from(files || []).filter((file) => file.type.startsWith('image/')).forEach((file) => this.upload(file));
        },

        async upload(file) {
            const item = { key: ++nextKey, type: 'photo', thumbnail_url: URL.createObjectURL(file), caption: '', uploading: true };
            this.items.push(item);
            const body = new FormData();
            body.append('photo', file);

            try {
                const res = await fetch(@json(route('admin.local-events.media.upload')), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                    body,
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { throw new Error(data.errors?.photo?.[0] || data.message || 'Upload failed.'); }

                const live = this.items.find((i) => i.key === item.key);
                if (!live) {
                    this.discard(data);
                    return;
                }
                Object.assign(live, { path: data.path, thumbnail_path: data.thumbnail_path, thumbnail_url: data.thumbnail_url, uploading: false });
            } catch (e) {
                this.items = this.items.filter((i) => i.key !== item.key);
                this.error = file.name + ': ' + e.message;
            }
        },

        addVideo() {
            const url = this.newVideoUrl.trim();
            if (!url) { return; }
            const youtube = url.match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i);
            const vimeo = url.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
            if (!youtube && !vimeo) {
                this.error = 'Only YouTube and Vimeo links can be added.';
                return;
            }
            this.error = '';
            this.items.push({
                key: ++nextKey,
                type: 'video',
                video_url: url,
                thumbnail_url: youtube ? `https://img.youtube.com/vi/${youtube[1]}/hqdefault.jpg` : `https://vumbnail.com/${vimeo[1]}.jpg`,
                caption: '',
                uploading: false,
            });
            this.newVideoUrl = '';
        },

        move(index, step) {
            const target = index + step;
            if (target < 0 || target >= this.items.length) { return; }
            const [item] = this.items.splice(index, 1);
            this.items.splice(target, 0, item);
        },

        remove(index) {
            const [item] = this.items.splice(index, 1);
            if (item.path) { this.discard(item); }
        },

        discard(item) {
            fetch(@json(route('admin.local-events.media.upload.delete')), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ path: item.path, thumbnail_path: item.thumbnail_path }),
            }).catch(() => {});
        },
    };
}
</script>
