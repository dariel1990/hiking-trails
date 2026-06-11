{{--
    Trail photo upload modal — branded design.

    Required vars in scope:
        $trail — Trail model. Used for the trail_id payload and the empty-state copy.
--}}
@php
    $recaptchaSiteKey = config('services.recaptcha.site_key');
@endphp

<style>
    /* Hide the floating reCAPTCHA badge — disclosure text + privacy/terms links are shown in the modal footer instead */
    .grecaptcha-badge {
        visibility: hidden;
    }
</style>

<div
    x-data="trailPhotoUpload({
        trailId: {{ (int) $trail->id }},
        endpoint: '{{ url('/api/trail-photos') }}',
        recaptchaSiteKey: @js($recaptchaSiteKey),
    })"
    x-cloak
    class="relative inline-block"
>
    {{-- Trigger --}}
    <button
        type="button"
        @click="open()"
        class="group inline-flex items-center gap-2 bg-gradient-to-r from-forest-600 to-emerald-400 hover:from-forest-700 hover:to-emerald-500 text-white font-semibold py-2.5 px-5 rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2"
    >
        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 16.5V7.5A2.5 2.5 0 0 1 5.5 5h2.379a1 1 0 0 0 .707-.293l1.121-1.121A1 1 0 0 1 10.414 3.293H13.586a1 1 0 0 1 .707.293l1.121 1.121A1 1 0 0 0 16.121 5H18.5A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5Z"/>
            <circle cx="12" cy="12" r="3.5"/>
        </svg>
        Submit a photo
    </button>

    {{-- Modal --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="close()"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-forest-900/70 backdrop-blur-sm p-4 overflow-y-auto"
        style="display: none;"
    >
        <div
            @click.outside="close()"
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-8 overflow-hidden flex flex-col max-h-[calc(100vh-4rem)]"
        >
            {{-- Branded header with hero gradient --}}
            <div class="relative px-6 py-5 text-white overflow-hidden flex-shrink-0"
                style="background: linear-gradient(135deg, #2C5F5D 0%, #4A9B8E 60%, #E87B35 100%);">

                {{-- Decorative mountain pattern --}}
                <svg class="absolute right-0 bottom-0 w-40 h-32 text-white/10 pointer-events-none" viewBox="0 0 120 80" fill="currentColor">
                    <path d="M0 80 L20 40 L35 55 L55 20 L75 50 L95 30 L120 80 Z"/>
                </svg>

                <div class="relative flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs uppercase tracking-widest text-white/80 font-semibold mb-1">Community Submission</p>
                        <h3 class="font-display text-2xl sm:text-3xl font-bold leading-tight">Share your view</h3>
                        <p class="text-sm text-white/90 mt-1 truncate">from <span class="font-semibold">{{ $trail->name }}</span></p>
                    </div>
                    <button type="button" @click="close()"
                        aria-label="Close"
                        class="shrink-0 w-9 h-9 rounded-full bg-white/15 hover:bg-white/30 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Success state (replaces form when successMessage is set) --}}
            <template x-if="successMessage">
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                        <svg class="w-9 h-9 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h4 class="font-display text-2xl font-bold text-gray-900 mb-2">Thanks for sharing!</h4>
                    <p class="text-gray-600 max-w-md mx-auto">Your photo is now in our admin queue. Once approved, it will appear in the community gallery for everyone to enjoy.</p>
                </div>
            </template>

            {{-- Form --}}
            <template x-if="!successMessage">
                <form @submit.prevent="submit()" class="overflow-y-auto custom-scrollbar">
                    <div class="px-6 py-5 space-y-5">

                        {{-- Drag-and-drop file zone --}}
                        <div x-show="!hasFile" class="space-y-2">
                            <label class="form-label">Photo</label>
                            <label
                                for="trail-photo-file"
                                @dragover.prevent="dragOver = true"
                                @dragleave.prevent="dragOver = false"
                                @drop.prevent="onDrop($event)"
                                :class="dragOver ? 'border-forest-500 bg-forest-50' : 'border-gray-300 bg-gray-50 hover:bg-emerald-50 hover:border-emerald-400'"
                                class="flex flex-col items-center justify-center w-full py-10 px-4 border-2 border-dashed rounded-xl cursor-pointer transition-all duration-200"
                            >
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-forest-100 to-emerald-100 flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-forest-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-gray-900 font-semibold">Drop your photo here</p>
                                <p class="text-sm text-gray-500 mt-1">or <span class="text-forest-600 font-medium underline">browse from your device</span></p>
                                <p class="text-xs text-gray-400 mt-3">JPG, PNG, or WebP · Max 8 MB</p>
                                <input
                                    id="trail-photo-file"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    @change="onFileSelected($event)"
                                    class="sr-only"
                                >
                            </label>
                        </div>

                        {{-- Cropper preview --}}
                        <div x-show="hasFile" class="space-y-2" style="display: none;">
                            <div class="flex items-center justify-between">
                                <label class="form-label mb-0">Crop to 16:9</label>
                                <button type="button" @click="resetFile()"
                                    class="text-sm text-forest-600 hover:text-forest-800 font-medium inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Choose another
                                </button>
                            </div>
                            <div class="bg-gray-900 rounded-xl overflow-hidden border border-gray-200 shadow-inner" style="height: 45vh; min-height: 280px; max-height: 500px;">
                                <img x-ref="cropImage" alt="Photo to crop" style="display: block; max-width: 100%;">
                            </div>
                            <p class="text-xs text-gray-500">Drag the crop area or pinch/scroll to zoom. The framed area is what will appear in the carousel.</p>
                        </div>

                        {{-- Caption --}}
                        <div>
                            <label for="trail-photo-caption" class="form-label">Caption <span class="text-gray-400 font-normal">— optional</span></label>
                            <input id="trail-photo-caption" x-model="form.caption" type="text" maxlength="255"
                                class="form-input"
                                placeholder="A short description of the view, conditions, time of year…">
                        </div>

                        {{-- Name + Email row --}}
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="trail-photo-name" class="form-label">Your name <span class="text-gray-400 font-normal">— optional</span></label>
                                <input id="trail-photo-name" x-model="form.name" type="text" maxlength="100"
                                    class="form-input"
                                    placeholder="Shown as the credit">
                            </div>
                            <div>
                                <label for="trail-photo-email" class="form-label">Email <span class="text-accent-600">*</span></label>
                                <input id="trail-photo-email" x-model="form.email" type="email" required maxlength="150"
                                    class="form-input"
                                    placeholder="you@example.com">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 -mt-2 flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Your email is never shown publicly — we only use it if our moderators need to follow up.</span>
                        </p>

                        {{-- Honeypot — hidden from real users, bots fill it --}}
                        <div aria-hidden="true" style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;">
                            <label>Website</label>
                            <input type="text" x-model="form.website" tabindex="-1" autocomplete="off">
                        </div>

                        {{-- Error message --}}
                        <template x-if="errorMessage">
                            <div class="alert alert-error flex items-start gap-2 text-sm">
                                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="errorMessage"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Footer / actions --}}
                    <div class="bg-sand-50 border-t border-gray-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-xs text-gray-500 max-w-xs">
                            Protected by Google reCAPTCHA.
                            <a href="https://policies.google.com/privacy" class="underline hover:text-forest-600" target="_blank" rel="noopener">Privacy</a> ·
                            <a href="https://policies.google.com/terms" class="underline hover:text-forest-600" target="_blank" rel="noopener">Terms</a>
                        </p>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="close()"
                                class="px-4 py-2.5 text-gray-700 hover:bg-gray-100 rounded-xl font-medium transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                :disabled="!canSubmit"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-forest-600 to-emerald-400 hover:from-forest-700 hover:to-emerald-500 text-white font-semibold py-2.5 px-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-md disabled:hover:from-forest-600 disabled:hover:to-emerald-400">
                                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9"/>
                                </svg>
                                <span x-text="submitting ? 'Submitting…' : 'Submit photo'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js" defer></script>
        @if($recaptchaSiteKey)
            <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}" async defer></script>
        @endif
        <script>
            // Alpine component for the trail photo upload modal.
            // Registered globally so it can be reused on any page that includes the partial.
            document.addEventListener('alpine:init', () => {
                Alpine.data('trailPhotoUpload', (config) => ({
                    isOpen: false,
                    hasFile: false,
                    dragOver: false,
                    submitting: false,
                    errorMessage: '',
                    successMessage: '',
                    cropper: null,
                    form: { caption: '', name: '', email: '', website: '' },

                    get canSubmit() {
                        return this.hasFile && this.form.email && !this.submitting;
                    },

                    open() {
                        this.resetState();
                        this.isOpen = true;
                    },
                    close() {
                        this.destroyCropper();
                        this.isOpen = false;
                    },
                    resetState() {
                        this.destroyCropper();
                        this.hasFile = false;
                        this.dragOver = false;
                        this.submitting = false;
                        this.errorMessage = '';
                        this.successMessage = '';
                        this.form = { caption: '', name: '', email: '', website: '' };
                    },
                    resetFile() {
                        this.destroyCropper();
                        this.hasFile = false;
                        const input = document.getElementById('trail-photo-file');
                        if (input) input.value = '';
                    },
                    destroyCropper() {
                        if (this.cropper) {
                            this.cropper.destroy();
                            this.cropper = null;
                        }
                    },

                    onDrop(event) {
                        this.dragOver = false;
                        const file = event.dataTransfer?.files?.[0];
                        if (file) this.handleFile(file);
                    },
                    onFileSelected(event) {
                        const file = event.target.files && event.target.files[0];
                        if (file) this.handleFile(file);
                    },
                    handleFile(file) {
                        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
                            this.errorMessage = 'Please choose a JPG, PNG, or WebP image.';
                            return;
                        }
                        if (file.size > 8 * 1024 * 1024) {
                            this.errorMessage = 'Photo must be 8 MB or smaller.';
                            return;
                        }
                        this.errorMessage = '';
                        this.destroyCropper();
                        this.hasFile = true;

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const dataUrl = e.target.result;
                            this.$nextTick(() => {
                                const img = this.$refs.cropImage;
                                if (!img) {
                                    return;
                                }
                                let started = false;
                                const start = () => {
                                    if (started) {
                                        return;
                                    }
                                    started = true;
                                    img.onload = null;
                                    requestAnimationFrame(() => this.initCropper(img));
                                };
                                img.onload = start;
                                img.src = dataUrl;
                                if (img.complete && img.naturalWidth > 0) {
                                    start();
                                }
                            });
                        };
                        reader.readAsDataURL(file);
                    },

                    initCropper(img, attempt = 0) {
                        this.destroyCropper();
                        if (typeof Cropper === 'undefined') {
                            this.errorMessage = 'Photo editor failed to load. Please refresh and try again.';
                            return;
                        }
                        const parent = img.parentElement;
                        if (parent && parent.offsetWidth === 0 && attempt < 10) {
                            requestAnimationFrame(() => this.initCropper(img, attempt + 1));
                            return;
                        }
                        this.cropper = new Cropper(img, {
                            aspectRatio: 16 / 9,
                            viewMode: 1,
                            autoCropArea: 1,
                            background: false,
                            movable: true,
                            zoomable: true,
                            scalable: false,
                            rotatable: false,
                        });
                    },

                    async getCroppedBlob() {
                        return new Promise((resolve, reject) => {
                            if (!this.cropper) {
                                reject(new Error('No image to submit.'));
                                return;
                            }
                            const canvas = this.cropper.getCroppedCanvas({
                                width: 1600,
                                height: 900,
                                imageSmoothingQuality: 'high',
                            });
                            canvas.toBlob(
                                (blob) => blob ? resolve(blob) : reject(new Error('Could not export the cropped image.')),
                                'image/jpeg',
                                0.92,
                            );
                        });
                    },

                    async getRecaptchaToken() {
                        if (!config.recaptchaSiteKey) {
                            return '';
                        }
                        if (typeof grecaptcha === 'undefined' || !grecaptcha.execute) {
                            throw new Error('Spam check failed to load. Please refresh and try again.');
                        }
                        return new Promise((resolve, reject) => {
                            grecaptcha.ready(() => {
                                grecaptcha
                                    .execute(config.recaptchaSiteKey, { action: 'submit_trail_photo' })
                                    .then(resolve)
                                    .catch(reject);
                            });
                        });
                    },

                    async submit() {
                        this.errorMessage = '';
                        this.successMessage = '';
                        this.submitting = true;
                        try {
                            const blob = await this.getCroppedBlob();
                            const token = await this.getRecaptchaToken();

                            const fd = new FormData();
                            fd.append('trail_id', config.trailId);
                            fd.append('image', blob, 'photo.jpg');
                            fd.append('caption', this.form.caption || '');
                            fd.append('name', this.form.name || '');
                            fd.append('email', this.form.email);
                            fd.append('website', this.form.website || '');
                            fd.append('g-recaptcha-response', token || '');

                            const res = await fetch(config.endpoint, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                },
                                body: fd,
                            });

                            if (!res.ok) {
                                const data = await res.json().catch(() => ({}));
                                if (data.errors) {
                                    const firstField = Object.keys(data.errors)[0];
                                    this.errorMessage = data.errors[firstField][0];
                                } else {
                                    this.errorMessage = data.message || 'Something went wrong. Please try again.';
                                }
                                return;
                            }

                            this.destroyCropper();
                            this.hasFile = false;
                            this.successMessage = 'Thanks — your photo is awaiting review.';
                            setTimeout(() => this.close(), 2600);
                        } catch (err) {
                            this.errorMessage = err.message || 'Something went wrong. Please try again.';
                        } finally {
                            this.submitting = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
