@extends('layouts.admin')

@section('title', 'Add Activity Type')
@section('page-title', 'Add Activity Type')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('admin.activity-types.index') }}" class="hover:text-foreground transition-colors">Activity Types</a>
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span>Create</span>
    </div>

    <!-- Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-bold tracking-tight">Create Activity Type</h1>
        <p class="text-muted-foreground">Add a new outdoor activity for trails</p>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="rounded-lg border-2 border-red-300 bg-red-50 p-6">
            <div class="flex items-start gap-3">
                <svg class="h-6 w-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-red-900 mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.activity-types.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Basic Information Card -->
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Basic Information</h3>
                    <p class="text-sm text-muted-foreground">Essential details about the activity type</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Activity Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               required 
                               value="{{ old('name') }}"
                               placeholder="e.g., Hiking, Fishing, Camping"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="slug" 
                               id="slug"
                               required 
                               value="{{ old('slug') }}"
                               placeholder="e.g., hiking, fishing, camping"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm @error('slug') border-red-300 @enderror">
                        <p class="text-xs text-muted-foreground">Auto-generated from name. Use lowercase with hyphens.</p>
                        @error('slug')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-medium">
                            Description
                        </label>
                        <textarea name="description" 
                                  rows="3"
                                  placeholder="Brief description of this activity..."
                                  class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        <p class="text-xs text-muted-foreground">Maximum 500 characters</p>
                        @error('description')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Design Card -->
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Visual Design</h3>
                    <p class="text-sm text-muted-foreground">Icon and color for the activity</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Icon Selector -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Icon <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-3">
                            <!-- Popular Emojis -->
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🥾">🥾</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🎣">🎣</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="⛺">⛺</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="👁️">👁️</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="⛷️">⛷️</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🎿">🎿</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🚴">🚴</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🏃">🏃</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🧗">🧗</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🏊">🏊</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🚣">🚣</button>
                                <button type="button" class="emoji-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary hover:bg-primary/5 flex items-center justify-center text-2xl transition-all" data-emoji="🛶">🛶</button>
                            </div>
                            
                            <!-- Custom Input -->
                            <input type="text"
                                   name="icon"
                                   id="icon"
                                   value="{{ old('icon') }}"
                                   placeholder="Or paste any emoji here"
                                   maxlength="10"
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm @error('icon') border-red-300 @enderror">
                            @error('icon')
                                <p class="text-sm text-red-500">{{ $message }}</p>
                            @enderror

                            <!-- Image Upload -->
                            <div class="pt-3 border-t border-gray-200 space-y-2">
                                <p class="text-xs font-medium text-gray-600">Or upload PNG/JPG icon <span class="text-gray-400 font-normal">(overrides emoji)</span></p>

                                {{-- Gallery of previously uploaded icons --}}
                                <div class="flex flex-wrap gap-2 min-h-[3rem] items-center" id="activity-icon-gallery">
                                    <span class="text-xs text-gray-400 italic self-center">Loading icons…</span>
                                </div>

                                {{-- Selected icon preview --}}
                                <div id="activity-icon-image-preview" class="hidden items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-md px-3 py-1.5">
                                    <img id="activity-icon-image-preview-img" src="" alt="" class="w-6 h-6 object-contain rounded">
                                    <span id="activity-icon-image-name" class="truncate flex-1"></span>
                                    <button type="button" id="activity-icon-image-clear" class="ml-auto text-red-500 hover:text-red-700 shrink-0" title="Remove custom icon">✕</button>
                                </div>

                                {{-- Upload new icon --}}
                                <div class="flex items-center gap-2">
                                    <label for="activity-icon-image-input" class="cursor-pointer inline-flex items-center gap-1.5 rounded-md border border-dashed border-gray-300 px-3 py-1.5 text-xs text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Upload new icon
                                    </label>
                                    <input type="file" id="activity-icon-image-input" accept="image/*" class="hidden">
                                    <span id="activity-icon-upload-status" class="text-xs text-gray-400"></span>
                                </div>
                                <p class="text-xs text-gray-400">PNG, JPG, WebP · Max 2 MB</p>

                                <input type="hidden" name="icon_image" id="activity-icon-image-path" value="{{ old('icon_image') }}">
                                @error('icon_image')
                                    <p class="text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Color Picker -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">
                            Color <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-3">
                            <!-- Preset Colors -->
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="color-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary transition-all" style="background-color: #10B981;" data-color="#10B981"></button>
                                <button type="button" class="color-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary transition-all" style="background-color: #3B82F6;" data-color="#3B82F6"></button>
                                <button type="button" class="color-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary transition-all" style="background-color: #F59E0B;" data-color="#F59E0B"></button>
                                <button type="button" class="color-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary transition-all" style="background-color: #8B5CF6;" data-color="#8B5CF6"></button>
                                <button type="button" class="color-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary transition-all" style="background-color: #EF4444;" data-color="#EF4444"></button>
                                <button type="button" class="color-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary transition-all" style="background-color: #06B6D4;" data-color="#06B6D4"></button>
                                <button type="button" class="color-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary transition-all" style="background-color: #EC4899;" data-color="#EC4899"></button>
                                <button type="button" class="color-btn w-12 h-12 rounded-md border-2 border-gray-200 hover:border-primary transition-all" style="background-color: #84CC16;" data-color="#84CC16"></button>
                            </div>
                            
                            <!-- Custom Color Input -->
                            <div class="flex gap-2">
                                <input type="color" 
                                       id="color-picker"
                                       value="{{ old('color', '#10B981') }}"
                                       class="h-10 w-20 rounded-md border border-input cursor-pointer">
                                <input type="text" 
                                       name="color" 
                                       id="color"
                                       required 
                                       value="{{ old('color', '#10B981') }}"
                                       placeholder="#000000"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       class="flex h-10 flex-1 rounded-md border border-input bg-background px-3 py-2 text-sm font-mono @error('color') border-red-300 @enderror">
                            </div>
                            @error('color')
                                <p class="text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Card -->
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Configuration</h3>
                    <p class="text-sm text-muted-foreground">Season availability and status</p>
                </div>

                <div class="space-y-6">
                    <!-- Season Applicability -->
                    <div class="space-y-3">
                        <label class="text-sm font-medium">
                            Season Applicability <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3 p-3 rounded-md border border-input hover:bg-accent cursor-pointer transition-colors">
                                <input type="radio" name="season_applicable" value="summer" {{ old('season_applicable') == 'summer' ? 'checked' : '' }} required class="w-4 h-4">
                                <div>
                                    <div class="font-medium">Summer Only</div>
                                    <div class="text-sm text-muted-foreground">Available during summer months</div>
                                </div>
                            </label>
                            <label class="flex items-center space-x-3 p-3 rounded-md border border-input hover:bg-accent cursor-pointer transition-colors">
                                <input type="radio" name="season_applicable" value="winter" {{ old('season_applicable') == 'winter' ? 'checked' : '' }} required class="w-4 h-4">
                                <div>
                                    <div class="font-medium">Winter Only</div>
                                    <div class="text-sm text-muted-foreground">Available during winter months</div>
                                </div>
                            </label>
                            <label class="flex items-center space-x-3 p-3 rounded-md border border-input hover:bg-accent cursor-pointer transition-colors">
                                <input type="radio" name="season_applicable" value="both" {{ old('season_applicable', 'both') == 'both' ? 'checked' : '' }} required class="w-4 h-4">
                                <div>
                                    <div class="font-medium">All Year Round</div>
                                    <div class="text-sm text-muted-foreground">Available in all seasons</div>
                                </div>
                            </label>
                        </div>
                        @error('season_applicable')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Active Status -->
                    <div class="space-y-3">
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-300">
                            <div>
                                <div class="font-medium">Active</div>
                                <div class="text-sm text-muted-foreground">Make this activity type available for trails</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Card -->
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-6 space-y-4">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold">Preview</h3>
                    <p class="text-sm text-muted-foreground">How this activity will appear</p>
                </div>

                <div id="preview-card" class="rounded-lg border p-4" style="border-left: 4px solid #10B981;">
                    <div class="flex items-center gap-3">
                        <div id="preview-icon" class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl" style="background-color: #10B98120;">
                            🥾
                        </div>
                        <div>
                            <h4 id="preview-name" class="font-semibold text-lg">Activity Name</h4>
                            <p id="preview-slug" class="text-sm text-muted-foreground">activity-slug</p>
                        </div>
                    </div>
                    <p id="preview-description" class="text-sm text-muted-foreground mt-3">Activity description will appear here</p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between py-4">
            <a href="{{ route('admin.activity-types.index') }}" 
               class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-10 px-4 py-2 text-sm font-medium">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-8 py-2 text-sm font-medium">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Create Activity Type
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    
    nameInput.addEventListener('input', function() {
        if (!slugInput.dataset.manuallyEdited) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
            updatePreview();
        }
    });
    
    slugInput.addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
        updatePreview();
    });

    // Emoji selection
    document.querySelectorAll('.emoji-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const emoji = this.dataset.emoji;
            document.getElementById('icon').value = emoji;
            
            // Visual feedback
            document.querySelectorAll('.emoji-btn').forEach(b => {
                b.classList.remove('border-primary', 'bg-primary/10');
                b.classList.add('border-gray-200');
            });
            this.classList.add('border-primary', 'bg-primary/10');
            this.classList.remove('border-gray-200');
            
            updatePreview();
        });
    });

    // Icon input update
    document.getElementById('icon').addEventListener('input', updatePreview);

    // Color selection
    document.querySelectorAll('.color-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const color = this.dataset.color;
            document.getElementById('color').value = color;
            document.getElementById('color-picker').value = color;
            
            // Visual feedback
            document.querySelectorAll('.color-btn').forEach(b => {
                b.classList.remove('border-primary');
                b.classList.add('border-gray-200');
            });
            this.classList.add('border-primary');
            this.classList.remove('border-gray-200');
            
            updatePreview();
        });
    });

    // Color picker sync
    document.getElementById('color-picker').addEventListener('input', function() {
        document.getElementById('color').value = this.value;
        updatePreview();
    });

    // Color text input sync
    document.getElementById('color').addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            document.getElementById('color-picker').value = this.value;
            updatePreview();
        }
    });

    // Description update
    document.querySelector('textarea[name="description"]').addEventListener('input', updatePreview);

    // Update preview function
    function updatePreview() {
        const name = document.getElementById('name').value || 'Activity Name';
        const slug = document.getElementById('slug').value || 'activity-slug';
        const icon = document.getElementById('icon').value || '🥾';
        const color = document.getElementById('color').value || '#10B981';
        const description = document.querySelector('textarea[name="description"]').value || 'Activity description will appear here';

        document.getElementById('preview-name').textContent = name;
        document.getElementById('preview-slug').textContent = slug;
        document.getElementById('preview-icon').textContent = icon;
        document.getElementById('preview-description').textContent = description;
        
        // Update colors
        document.getElementById('preview-card').style.borderLeftColor = color;
        document.getElementById('preview-icon').style.backgroundColor = color + '20';
    }

    // Initial preview update
    updatePreview();

    initActivityIconGallery();

    const uploadInput = document.getElementById('activity-icon-image-input');
    if (uploadInput) {
        uploadInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) { return; }
            const statusEl = document.getElementById('activity-icon-upload-status');
            if (statusEl) { statusEl.textContent = 'Uploading…'; }

            const fd = new FormData();
            fd.append('icon', file);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const res = await fetch('{{ route("admin.activity-types.icons.upload") }}', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.path && data.url) {
                    selectActivityIcon(data.path, data.url);
                    addToActivityIconGallery(data.path, data.url);
                    if (statusEl) { statusEl.textContent = 'Uploaded!'; }
                    setTimeout(() => { if (statusEl) { statusEl.textContent = ''; } }, 2000);
                }
            } catch {
                if (statusEl) { statusEl.textContent = 'Upload failed.'; }
            }
            uploadInput.value = '';
        });
    }

    const clearBtn = document.getElementById('activity-icon-image-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearActivityIcon);
    }
});

function initActivityIconGallery() {
    const gallery = document.getElementById('activity-icon-gallery');
    if (!gallery) { return; }

    fetch('{{ route("admin.activity-types.icons") }}', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(icons => {
        if (!icons.length) {
            gallery.innerHTML = '<span class="text-xs text-gray-400 italic self-center">No custom icons yet</span>';
            return;
        }

        const current = document.getElementById('activity-icon-image-path').value;
        gallery.innerHTML = icons.map(ic => `
            <div class="relative group" data-icon-wrapper="${ic.path}">
                <button type="button" data-path="${ic.path}" data-url="${ic.url}"
                    class="activity-icon-thumb w-10 h-10 rounded-md border-2 ${ic.path === current ? 'border-primary' : 'border-transparent'} hover:border-primary overflow-hidden bg-white flex items-center justify-center p-0.5 transition-colors"
                    title="${ic.path.split('/').pop()}">
                    <img src="${ic.url}" class="w-full h-full object-contain" alt="">
                </button>
                <button type="button" data-delete-path="${ic.path}"
                    class="activity-icon-delete absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity shadow"
                    title="Delete this custom icon">✕</button>
            </div>
        `).join('');

        gallery.querySelectorAll('.activity-icon-thumb').forEach(btn => {
            btn.addEventListener('click', () => selectActivityIcon(btn.dataset.path, btn.dataset.url));
        });
        gallery.querySelectorAll('.activity-icon-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteActivityIcon(btn.dataset.deletePath, btn.closest('[data-icon-wrapper]'));
            });
        });

        if (current) {
            const match = icons.find(ic => ic.path === current);
            if (match) { selectActivityIcon(match.path, match.url); }
        }
    })
    .catch(() => {
        gallery.innerHTML = '<span class="text-xs text-red-400 italic self-center">Failed to load icons</span>';
    });
}

function selectActivityIcon(path, url) {
    document.getElementById('activity-icon-image-path').value = path;

    const preview = document.getElementById('activity-icon-image-preview');
    const previewImg = document.getElementById('activity-icon-image-preview-img');
    const previewName = document.getElementById('activity-icon-image-name');
    if (preview && previewImg) {
        previewImg.src = url;
        if (previewName) { previewName.textContent = path.split('/').pop(); }
        preview.classList.remove('hidden');
        preview.classList.add('flex');
    }

    document.querySelectorAll('#activity-icon-gallery .activity-icon-thumb').forEach(btn => {
        btn.classList.toggle('border-primary', btn.dataset.path === path);
        btn.classList.toggle('border-transparent', btn.dataset.path !== path);
    });
}

function clearActivityIcon() {
    document.getElementById('activity-icon-image-path').value = '';

    const preview = document.getElementById('activity-icon-image-preview');
    if (preview) {
        preview.classList.add('hidden');
        preview.classList.remove('flex');
    }

    document.querySelectorAll('#activity-icon-gallery .activity-icon-thumb').forEach(btn => {
        btn.classList.remove('border-primary');
        btn.classList.add('border-transparent');
    });
}

function addToActivityIconGallery(path, url) {
    const gallery = document.getElementById('activity-icon-gallery');
    if (!gallery) { return; }

    const placeholder = gallery.querySelector('span.italic');
    if (placeholder) { placeholder.remove(); }

    const wrapper = document.createElement('div');
    wrapper.className = 'relative group';
    wrapper.dataset.iconWrapper = path;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.dataset.path = path;
    btn.dataset.url = url;
    btn.className = 'activity-icon-thumb w-10 h-10 rounded-md border-2 border-transparent hover:border-primary overflow-hidden bg-white flex items-center justify-center p-0.5 transition-colors';
    btn.title = path.split('/').pop();
    btn.addEventListener('click', () => selectActivityIcon(path, url));
    btn.innerHTML = `<img src="${url}" class="w-full h-full object-contain" alt="">`;

    const deleteBtn = document.createElement('button');
    deleteBtn.type = 'button';
    deleteBtn.dataset.deletePath = path;
    deleteBtn.className = 'activity-icon-delete absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] leading-none flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity shadow';
    deleteBtn.title = 'Delete this custom icon';
    deleteBtn.textContent = '✕';
    deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        deleteActivityIcon(path, wrapper);
    });

    wrapper.appendChild(btn);
    wrapper.appendChild(deleteBtn);
    gallery.prepend(wrapper);
}

async function deleteActivityIcon(path, wrapperEl) {
    if (!confirm('Delete this custom icon? This cannot be undone.')) { return; }

    const requestDelete = async (force = false) => {
        const res = await fetch('{{ route("admin.activity-types.icons.delete") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ path, force })
        });
        if (!res.ok) { throw new Error('Failed to delete icon'); }
        return res.json();
    };

    try {
        let data = await requestDelete();

        if (!data.deleted && data.in_use) {
            const proceed = confirm(`This icon is currently used by ${data.in_use} item(s). Deleting it will revert them to the stock icon. Delete anyway?`);
            if (!proceed) { return; }
            data = await requestDelete(true);
        }

        if (!data.deleted) { return; }

        if (wrapperEl) { wrapperEl.remove(); }

        if (document.getElementById('activity-icon-image-path').value === path) {
            clearActivityIcon();
        }

        const gallery = document.getElementById('activity-icon-gallery');
        if (gallery && !gallery.querySelector('.activity-icon-thumb')) {
            gallery.innerHTML = '<span class="text-xs text-gray-400 italic self-center">No custom icons yet</span>';
        }
    } catch {
        alert('Failed to delete icon.');
    }
}
</script>
@endpush
<style>
/* shadcn/ui inspired color variables */
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --card: 0 0% 100%;
  --card-foreground: 222.2 84% 4.9%;
  --popover: 0 0% 100%;
  --popover-foreground: 222.2 84% 4.9%;
  --primary: 221.2 83.2% 53.3%;
  --primary-foreground: 210 40% 98%;
  --secondary: 210 40% 96%;
  --secondary-foreground: 222.2 84% 4.9%;
  --muted: 210 40% 96%;
  --muted-foreground: 215.4 16.3% 46.9%;
  --accent: 210 40% 96%;
  --accent-foreground: 222.2 84% 4.9%;
  --destructive: 0 84.2% 60.2%;
  --destructive-foreground: 210 40% 98%;
  --border: 214.3 31.8% 91.4%;
  --input: 214.3 31.8% 91.4%;
  --ring: 221.2 83.2% 53.3%;
  --radius: 0.5rem;
}

.dark {
  --background: 222.2 84% 4.9%;
  --foreground: 210 40% 98%;
  --card: 222.2 84% 4.9%;
  --card-foreground: 210 40% 98%;
  --popover: 222.2 84% 4.9%;
  --popover-foreground: 210 40% 98%;
  --primary: 217.2 91.2% 59.8%;
  --primary-foreground: 222.2 84% 4.9%;
  --secondary: 217.2 32.6% 17.5%;
  --secondary-foreground: 210 40% 98%;
  --muted: 217.2 32.6% 17.5%;
  --muted-foreground: 215 20.2% 65.1%;
  --accent: 217.2 32.6% 17.5%;
  --accent-foreground: 210 40% 98%;
  --destructive: 0 62.8% 30.6%;
  --destructive-foreground: 210 40% 98%;
  --border: 217.2 32.6% 17.5%;
  --input: 217.2 32.6% 17.5%;
  --ring: 224.3 76.3% 94.1%;
}

/* Apply the color variables */
.bg-background { background-color: hsl(var(--background)); }
.text-foreground { color: hsl(var(--foreground)); }
.bg-card { background-color: hsl(var(--card)); }
.text-card-foreground { color: hsl(var(--card-foreground)); }
.bg-popover { background-color: hsl(var(--popover)); }
.text-popover-foreground { color: hsl(var(--popover-foreground)); }
.bg-primary { background-color: hsl(var(--primary)); }
.text-primary-foreground { color: hsl(var(--primary-foreground)); }
.bg-primary\/90 { background-color: hsl(var(--primary) / 0.9); }
.bg-secondary { background-color: hsl(var(--secondary)); }
.text-secondary-foreground { color: hsl(var(--secondary-foreground)); }
.bg-secondary\/80 { background-color: hsl(var(--secondary) / 0.8); }
.bg-muted { background-color: hsl(var(--muted)); }
.text-muted-foreground { color: hsl(var(--muted-foreground)); }
.bg-muted\/50 { background-color: hsl(var(--muted) / 0.5); }
.bg-accent { background-color: hsl(var(--accent)); }
.text-accent-foreground { color: hsl(var(--accent-foreground)); }
.hover\:bg-accent:hover { background-color: hsl(var(--accent)); }
.hover\:text-accent-foreground:hover { color: hsl(var(--accent-foreground)); }
.border-border { border-color: hsl(var(--border)); }
.border-input { border-color: hsl(var(--input)); }
.ring-ring { --tw-ring-color: hsl(var(--ring)); }
.ring-offset-background { --tw-ring-offset-color: hsl(var(--background)); }
</style>
@endsection