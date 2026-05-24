{{--
    Sponsorship repeater section. Expects:
    - $sponsors (Collection|array) — current sponsors (may be empty)

    Renders the "Sponsorships" panel with an Alpine.js repeater.
--}}
@php
    $sponsorsInitial = [];
    foreach (old('sponsors', []) as $row) {
        $sponsorsInitial[] = [
            'id' => $row['id'] ?? '',
            'name' => $row['name'] ?? '',
            'tagline' => $row['tagline'] ?? '',
            'url' => $row['url'] ?? '',
            'welcome_message' => $row['welcome_message'] ?? '',
            'banner_text' => $row['banner_text'] ?? '',
            'cta_text' => $row['cta_text'] ?? '',
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'is_active' => isset($row['is_active']) && (string) $row['is_active'] === '1' ? '1' : '0',
            '_delete' => '0',
            'existing_logo' => null,
            'logo_preview' => null,
            'remove_logo' => '0',
        ];
    }

    if (empty($sponsorsInitial)) {
        foreach ($sponsors ?? [] as $sponsor) {
            $sponsorsInitial[] = [
                'id' => (string) $sponsor->id,
                'name' => $sponsor->name,
                'tagline' => $sponsor->tagline ?? '',
                'url' => $sponsor->url ?? '',
                'welcome_message' => $sponsor->welcome_message ?? '',
                'banner_text' => $sponsor->banner_text ?? '',
                'cta_text' => $sponsor->cta_text ?? '',
                'sort_order' => (int) $sponsor->sort_order,
                'is_active' => $sponsor->is_active ? '1' : '0',
                '_delete' => '0',
                'existing_logo' => $sponsor->logo ? asset('storage/'.$sponsor->logo) : null,
                'logo_preview' => null,
                'remove_logo' => '0',
            ];
        }
    }
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200"
     x-data="sponsorRepeater({{ Js::from($sponsorsInitial) }})">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.449a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.539 1.118l-3.37-2.449a1 1 0 00-1.175 0l-3.37 2.449c-.783.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h2 class="text-sm font-semibold text-gray-900">Sponsorships</h2>
            <p class="text-xs text-gray-500">Optional — banner + floating badge shown on public pages for this network</p>
        </div>
        <button type="button" x-on:click="add()"
                class="inline-flex items-center gap-1.5 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-md transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add sponsor
        </button>
    </div>

    <div class="px-6 py-5 space-y-4">
        <template x-for="(sponsor, index) in sponsors" :key="sponsor._key">
            <div class="rounded-lg border border-gray-200 bg-gray-50/40 p-4 space-y-4"
                 x-show="sponsor._delete !== '1'">

                {{-- Hidden ID + delete flag (delete flag also submitted when hidden) --}}
                <input type="hidden" :name="`sponsors[${index}][id]`" :value="sponsor.id">
                <input type="hidden" :name="`sponsors[${index}][_delete]`" :value="sponsor._delete">
                <input type="hidden" :name="`sponsors[${index}][remove_logo]`" :value="sponsor.remove_logo">

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold"
                              x-text="index + 1"></span>
                        <span class="text-sm font-medium text-gray-700">Sponsor</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="hidden" :name="`sponsors[${index}][is_active]`" :value="sponsor.is_active">
                            <input type="checkbox"
                                   :checked="sponsor.is_active === '1'"
                                   x-on:change="sponsor.is_active = $event.target.checked ? '1' : '0'"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <span class="text-xs text-gray-600">Active</span>
                        </label>
                        <button type="button" x-on:click="remove(index)"
                                class="text-xs text-red-600 hover:text-red-700 font-medium inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/>
                            </svg>
                            Remove
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sponsor name <span class="text-red-500">*</span></label>
                        <input type="text" :name="`sponsors[${index}][name]`" x-model="sponsor.name"
                               placeholder="e.g., Phil Bernier"
                               class="block w-full rounded-lg border-gray-300 shadow-sm text-sm px-3 py-2 focus:border-green-500 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tagline</label>
                        <input type="text" :name="`sponsors[${index}][tagline]`" x-model="sponsor.tagline"
                               placeholder="e.g., REALTOR®"
                               class="block w-full rounded-lg border-gray-300 shadow-sm text-sm px-3 py-2 focus:border-green-500 focus:ring-green-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Logo</label>
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-white border border-gray-200 flex items-center justify-center overflow-hidden">
                            <template x-if="sponsor.logo_preview || (sponsor.existing_logo && sponsor.remove_logo !== '1')">
                                <img :src="sponsor.logo_preview || sponsor.existing_logo" alt="Logo preview" class="w-full h-full object-contain">
                            </template>
                            <template x-if="!sponsor.logo_preview && (!sponsor.existing_logo || sponsor.remove_logo === '1')">
                                <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="Default logo" class="w-full h-full object-contain opacity-50">
                            </template>
                        </div>
                        <div class="flex-1 flex items-center gap-2">
                            <label class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium px-3 py-2 rounded-md cursor-pointer transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span x-text="(sponsor.logo_preview || (sponsor.existing_logo && sponsor.remove_logo !== '1')) ? 'Replace' : 'Upload logo'"></span>
                                <input type="file" :name="`sponsors[${index}][logo]`" accept="image/*"
                                       x-on:change="onLogoChange(index, $event)" class="hidden">
                            </label>
                            <button type="button" x-on:click="clearLogo(index)"
                                    x-show="sponsor.logo_preview || (sponsor.existing_logo && sponsor.remove_logo !== '1')"
                                    class="text-xs text-red-600 hover:text-red-700 font-medium">
                                Clear
                            </button>
                        </div>
                    </div>
                    <p class="mt-1 text-[11px] text-gray-500">PNG, JPG, WebP, or SVG · up to 2 MB · falls back to Xplore Smithers logo when blank.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Click-through URL</label>
                    <input type="url" :name="`sponsors[${index}][url]`" x-model="sponsor.url"
                           placeholder="https://example.com"
                           class="block w-full rounded-lg border-gray-300 shadow-sm text-sm px-3 py-2 focus:border-green-500 focus:ring-green-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Welcome message <span class="text-gray-400 font-normal">(desktop banner prefix)</span></label>
                        <input type="text" :name="`sponsors[${index}][welcome_message]`" x-model="sponsor.welcome_message"
                               placeholder="e.g., Welcome to Hudson Bay Mountain!"
                               class="block w-full rounded-lg border-gray-300 shadow-sm text-sm px-3 py-2 focus:border-green-500 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Banner text</label>
                        <input type="text" :name="`sponsors[${index}][banner_text]`" x-model="sponsor.banner_text"
                               placeholder="e.g., Trail maps proudly sponsored by"
                               class="block w-full rounded-lg border-gray-300 shadow-sm text-sm px-3 py-2 focus:border-green-500 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">CTA button text</label>
                        <input type="text" :name="`sponsors[${index}][cta_text]`" x-model="sponsor.cta_text"
                               placeholder="e.g., Visit BVLiving.ca"
                               class="block w-full rounded-lg border-gray-300 shadow-sm text-sm px-3 py-2 focus:border-green-500 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sort order</label>
                        <input type="number" min="0" max="9999" :name="`sponsors[${index}][sort_order]`" x-model="sponsor.sort_order"
                               class="block w-full rounded-lg border-gray-300 shadow-sm text-sm px-3 py-2 focus:border-green-500 focus:ring-green-500">
                    </div>
                </div>
            </div>
        </template>

        <template x-if="sponsors.filter(s => s._delete !== '1').length === 0">
            <div class="rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center">
                <p class="text-sm text-gray-500">No sponsors yet.</p>
                <button type="button" x-on:click="add()"
                        class="mt-2 inline-flex items-center gap-1.5 text-sm text-green-700 hover:text-green-800 font-medium">
                    + Add the first sponsor
                </button>
            </div>
        </template>

        @if(isset($errors) && $errors->any())
            @error('sponsors')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            @foreach ($errors->get('sponsors.*') as $key => $messages)
                @foreach ($messages as $message)
                    <p class="text-sm text-red-600">{{ $key }}: {{ $message }}</p>
                @endforeach
            @endforeach
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            function sponsorRepeater(initial) {
                const blank = () => ({
                    _key: Math.random().toString(36).slice(2),
                    id: '',
                    name: '',
                    tagline: '',
                    url: '',
                    welcome_message: '',
                    banner_text: '',
                    cta_text: '',
                    sort_order: 0,
                    is_active: '1',
                    _delete: '0',
                    existing_logo: null,
                    logo_preview: null,
                    remove_logo: '0',
                });

                const seeded = (initial || []).map((row) => ({
                    _key: Math.random().toString(36).slice(2),
                    ...blank(),
                    ...row,
                }));

                return {
                    sponsors: seeded.length ? seeded : [blank()],
                    add() {
                        this.sponsors.push(blank());
                    },
                    remove(i) {
                        const s = this.sponsors[i];
                        if (s.id) {
                            s._delete = '1';
                        } else {
                            this.sponsors.splice(i, 1);
                            if (this.sponsors.length === 0) {
                                this.add();
                            }
                        }
                    },
                    onLogoChange(i, event) {
                        const file = event.target.files && event.target.files[0];
                        if (!file) { return; }
                        this.sponsors[i].remove_logo = '0';
                        const reader = new FileReader();
                        reader.onload = (e) => { this.sponsors[i].logo_preview = e.target.result; };
                        reader.readAsDataURL(file);
                    },
                    clearLogo(i) {
                        this.sponsors[i].logo_preview = null;
                        if (this.sponsors[i].existing_logo) {
                            this.sponsors[i].remove_logo = '1';
                        }
                        const input = document.querySelector(`input[name="sponsors[${i}][logo]"]`);
                        if (input) { input.value = ''; }
                    },
                };
            }
        </script>
    @endpush
@endonce
