@php $isEdit = isset($business) && $business !== null; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left: Main fields -->
    <div class="lg:col-span-2 space-y-6">

        {{-- Basic Info --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Basic Information</h3>
                <p class="text-sm text-muted-foreground">Enter the business details</p>
            </div>
            <div class="p-6 space-y-4">

                {{-- Business Type --}}
                <div class="space-y-2">
                    <label for="business_type" class="text-sm font-medium leading-none">
                        Business Type <span class="text-red-500">*</span>
                    </label>
                    <select name="business_type" id="business_type" required
                        onchange="updateBusinessIcon()"
                        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                        <option value="">Select business type</option>
                        @foreach(App\Models\Business::getBusinessTypes() as $type => $label)
                            <option value="{{ $type }}" {{ old('business_type', $isEdit ? $business->business_type : '') === $type ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('business_type') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Name --}}
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium leading-none">
                        Business Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required
                        value="{{ old('name', $isEdit ? $business->name : '') }}"
                        placeholder="e.g., Smithers Coffee House"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Tagline --}}
                <div class="space-y-2">
                    <label for="tagline" class="text-sm font-medium leading-none">Tagline</label>
                    <input type="text" name="tagline" id="tagline"
                        value="{{ old('tagline', $isEdit ? $business->tagline : '') }}"
                        placeholder="Short one-liner shown on the map popup"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('tagline') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div class="space-y-2">
                    <label for="description" class="text-sm font-medium leading-none">Description</label>
                    <textarea name="description" id="description" rows="4"
                        placeholder="Describe this business..."
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('description', $isEdit ? $business->description : '') }}</textarea>
                    @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Contact Info --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Contact & Online</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="space-y-2">
                    <label for="phone" class="text-sm font-medium leading-none">Phone</label>
                    <input type="text" name="phone" id="phone"
                        value="{{ old('phone', $isEdit ? $business->phone : '') }}"
                        placeholder="+1 250-847-0000"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('phone') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium leading-none">Email</label>
                    <input type="email" name="email" id="email"
                        value="{{ old('email', $isEdit ? $business->email : '') }}"
                        placeholder="hello@business.com"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('email') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <label for="website" class="text-sm font-medium leading-none">Website</label>
                    <input type="url" name="website" id="website"
                        value="{{ old('website', $isEdit ? $business->website : '') }}"
                        placeholder="https://www.example.com"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('website') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="facebook_url" class="text-sm font-medium leading-none">Facebook URL</label>
                    <input type="url" name="facebook_url" id="facebook_url"
                        value="{{ old('facebook_url', $isEdit ? $business->facebook_url : '') }}"
                        placeholder="https://facebook.com/..."
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('facebook_url') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="instagram_url" class="text-sm font-medium leading-none">Instagram URL</label>
                    <input type="url" name="instagram_url" id="instagram_url"
                        value="{{ old('instagram_url', $isEdit ? $business->instagram_url : '') }}"
                        placeholder="https://instagram.com/..."
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('instagram_url') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Location --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Location</h3>
                <p class="text-sm text-muted-foreground">Search for a location or click the map to pin the business</p>
            </div>
            <div class="p-6 space-y-4">

                <div class="space-y-2">
                    <label for="address" class="text-sm font-medium leading-none">Street Address</label>
                    <input type="text" name="address" id="address"
                        value="{{ old('address', $isEdit ? $business->address : '') }}"
                        placeholder="e.g., 1234 Main Street, Smithers, BC"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    @error('address') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Search on Map</label>
                    <div class="relative">
                        <input type="text" id="map-search-input"
                            placeholder="Search for a place..."
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 pl-10 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            autocomplete="off">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <button type="button" id="clear-search-btn" class="hidden absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <div id="search-loading" class="hidden absolute right-10 top-2.5">
                            <svg class="animate-spin h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    <div id="search-results-dropdown" class="hidden absolute z-50 mt-1 w-full max-w-xl bg-white rounded-md shadow-lg border border-gray-200 max-h-60 overflow-y-auto"></div>
                </div>

                <div id="coordinate-map" class="w-full h-[350px] rounded-md border border-input relative z-0"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="latitude" class="text-sm font-medium leading-none">Latitude <span class="text-red-500">*</span></label>
                        <input type="number" name="latitude" id="latitude" step="0.0000001" required readonly
                            value="{{ old('latitude', $isEdit ? $business->latitude : '54.7804') }}"
                            class="flex h-10 w-full rounded-md border border-input bg-muted px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('latitude') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="longitude" class="text-sm font-medium leading-none">Longitude <span class="text-red-500">*</span></label>
                        <input type="number" name="longitude" id="longitude" step="0.0000001" required readonly
                            value="{{ old('longitude', $isEdit ? $business->longitude : '-127.1698') }}"
                            class="flex h-10 w-full rounded-md border border-input bg-muted px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        @error('longitude') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- Media Upload --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Add Media</h3>
                <p class="text-sm text-muted-foreground">Upload photos or add video links</p>
            </div>
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Photos</label>
                    <div class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer" onclick="document.getElementById('photos').click()">
                        <input type="file" id="photos" name="photos[]" multiple accept="image/*" class="hidden" onchange="handlePhotoSelection(this)">
                        <svg class="mx-auto h-12 w-12 text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium text-muted-foreground">Click to upload photos</p>
                        <p class="text-xs text-muted-foreground mt-1">or drag and drop</p>
                    </div>
                    <div id="photo-preview" class="grid grid-cols-4 gap-2 mt-4"></div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none">Video Links</label>
                    <div id="video-urls-container" class="space-y-2">
                        <div class="flex gap-2">
                            <input type="url" name="video_urls[]"
                                placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <button type="button" onclick="addVideoUrlField()"
                                class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground">Supports YouTube and Vimeo links</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Right: Settings sidebar -->
    <div class="space-y-6">

        {{-- Save --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">{{ $isEdit ? 'Save Changes' : 'Publish' }}</h3>
            </div>
            <div class="p-6 flex flex-col gap-2">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 w-full">
                    {{ $isEdit ? 'Update Business' : 'Create Business' }}
                </button>
                <a href="{{ route('admin.businesses.index') }}"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 w-full">
                    Cancel
                </a>
            </div>
        </div>

        {{-- Settings --}}
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Settings</h3>
            </div>
            <div class="p-6 space-y-4">

                <div class="space-y-2">
                    <label for="icon" class="text-sm font-medium leading-none">Custom Icon <span class="text-muted-foreground text-xs">(optional)</span></label>
                    <input type="text" name="icon" id="icon" maxlength="10"
                        value="{{ old('icon', $isEdit ? $business->attributes['icon'] ?? '' : '') }}"
                        placeholder="Leave empty for default"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground">Default: <span id="icon-preview" class="text-base">📍</span></p>
                </div>

                <div class="space-y-2">
                    <label for="price_range" class="text-sm font-medium leading-none">Price Range</label>
                    <select name="price_range" id="price_range"
                        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                        <option value="">Not specified</option>
                        @foreach(App\Models\Business::getPriceRanges() as $val => $label)
                            <option value="{{ $val }}" {{ old('price_range', $isEdit ? $business->price_range : '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2 border-t space-y-3">
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $isEdit ? $business->is_active : true) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_active" class="text-sm font-medium leading-none">Active (visible on map)</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                            {{ old('is_featured', $isEdit ? $business->is_featured : false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_featured" class="text-sm font-medium leading-none">Featured</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_seasonal" id="is_seasonal" value="1"
                            {{ old('is_seasonal', $isEdit ? $business->is_seasonal : false) ? 'checked' : '' }}
                            onchange="document.getElementById('season_open_wrap').classList.toggle('hidden', !this.checked)"
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_seasonal" class="text-sm font-medium leading-none">Seasonal business</label>
                    </div>
                </div>

                <div id="season_open_wrap" class="{{ old('is_seasonal', $isEdit ? $business->is_seasonal : false) ? '' : 'hidden' }} space-y-2">
                    <label for="season_open" class="text-sm font-medium leading-none">Open Season</label>
                    <input type="text" name="season_open" id="season_open"
                        value="{{ old('season_open', $isEdit ? $business->season_open : '') }}"
                        placeholder="e.g. May–October"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                </div>

            </div>
        </div>

    </div>
</div>
