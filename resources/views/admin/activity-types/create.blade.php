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

    <form action="{{ route('admin.activity-types.store') }}" method="POST" class="space-y-6">
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
                                   required 
                                   value="{{ old('icon') }}"
                                   placeholder="Or paste any emoji here"
                                   maxlength="10"
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm @error('icon') border-red-300 @enderror">
                            @error('icon')
                                <p class="text-sm text-red-500">{{ $message }}</p>
                            @enderror
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
});
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