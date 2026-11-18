{{--
    Map icon picker shared by the tour and event editors: saved image icons, an upload
    dropzone, and an emoji fallback. The element ids are driven by admin.tours._scripts.

    Variables: $iconLabel, $iconValue, $iconImageValue, $iconMaxLength, $iconPlaceholder
--}}
<div class="space-y-2">
    <p class="text-sm font-medium leading-none">{{ $iconLabel }}</p>

    <div class="space-y-2 rounded-md border border-input bg-muted/30 p-2.5">
        {{-- Row 1: saved image icons + upload --}}
        <div class="flex flex-wrap items-center gap-1.5">
            <div class="flex flex-wrap items-center gap-1.5" id="tour-icon-gallery">
                <span class="text-xs text-muted-foreground italic">Loading icons…</span>
            </div>

            <label for="tour-icon-image-input" id="tour-icon-dropzone" title="Upload or drag a new icon"
                class="cursor-pointer inline-flex h-10 items-center gap-1 rounded-md border border-dashed border-input bg-background px-2.5 text-xs text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload
            </label>
            <input type="file" id="tour-icon-image-input" accept="image/*" class="hidden">

            {{-- Shown while an image icon is selected (the selected thumbnail is outlined) --}}
            <div id="tour-icon-image-preview" class="hidden items-center">
                <img id="tour-icon-image-preview-img" src="" alt="" class="hidden">
                <span id="tour-icon-image-name" class="hidden"></span>
                <button type="button" id="tour-icon-image-clear" title="Stop using the image icon"
                    class="inline-flex h-10 items-center rounded-md px-2 text-xs text-red-600 hover:bg-red-50">Remove image</button>
            </div>

            <span id="tour-icon-upload-status" class="text-xs text-muted-foreground"></span>
        </div>

        {{-- Row 2: emoji fallback --}}
        <div class="flex items-center gap-2 border-t border-input pt-2">
            <label for="tour_icon" class="text-xs text-muted-foreground">Or emoji</label>
            <input type="text" name="icon" id="tour_icon"
                value="{{ $iconValue }}"
                placeholder="{{ $iconPlaceholder }}"
                maxlength="{{ $iconMaxLength }}"
                class="h-9 w-16 rounded-md border border-input bg-background px-2 text-center text-xl ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            <button type="button" onclick="document.getElementById('tour_icon').value=''" title="Clear emoji"
                class="h-9 w-8 rounded-md text-xs text-muted-foreground hover:bg-muted">✕</button>
            <span class="text-xs text-muted-foreground">Used when no image icon is selected.</span>
        </div>
    </div>

    <input type="hidden" name="icon_image" id="tour-icon-image-path" value="{{ $iconImageValue }}">
    @error('icon') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    @error('icon_image') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
</div>
