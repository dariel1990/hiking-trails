{{--
    Event form. It keeps the element ids of the tour form so it can reuse the
    tour editor script (admin.tours._scripts): sticky toolbar, icon gallery, cover
    image, video preview, and the Route & Map editor with pinned / roaming activities.
--}}
@php
$isEdit = $event !== null;
$inputClass = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';
$smallInputClass = 'flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring';
$routeButtonClass = 'inline-flex items-center gap-1.5 rounded-md border border-input bg-background hover:bg-accent h-8 px-2.5 text-xs font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed';

$existingStopsJson = $isEdit
    ? $event->stops->map(fn ($s) => [
        'latitude' => $s->is_roaming ? null : $s->latitude,
        'longitude' => $s->is_roaming ? null : $s->longitude,
        'item_type' => $s->is_roaming ? 'roaming' : 'pinned',
        'name' => $s->is_roaming ? 'Roaming activity' : 'Pinned on map',
        'start_coordinates' => $s->coordinates(),
        'stop_label' => $s->stop_label ?? '',
        'estimated_visit_time' => $s->time_label ?? '',
        'driving_notes' => $s->notes ?? '',
        'icon' => $s->icon ?? '',
        'icon_image' => $s->icon_image ?? '',
        'icon_image_url' => $s->icon_image_url,
        'description' => $s->description ?? '',
        'existing_image' => $s->image ?? '',
        'image_url' => $s->image_url,
    ])->values()->toJson()
    : '[]';

$routeJson = $isEdit && $event->route_coordinates ? json_encode($event->route_coordinates) : '';
$galleryItems = $isEdit
    ? $event->media->map(fn ($m) => [
        'id' => $m->id,
        'type' => $m->isPhoto() ? 'photo' : 'video',
        'thumbnail_url' => $m->thumbnail_url,
        'video_url' => $m->isVideo() ? $m->url : null,
        'caption' => $m->caption ?? '',
    ])->values()
    : collect();
$paradeSlug = config('app.christmas_parade_slug');
$isChristmas = old('theme', $isEdit ? $event->theme : App\Models\Event::THEME_STANDARD) === App\Models\Event::THEME_CHRISTMAS;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="tour-form-root"
     x-data="tourForm([], [], [], {{ $existingStopsJson }}, {{ json_encode(['hasErrors' => $errors->any()]) }})">

    @include('admin.tours._form-toolbar', [
        'toolbarSections' => [
            'event-basics' => 'Basics',
            'event-when' => 'Date & time',
            'event-where' => 'Where & who',
            'event-look' => 'Look',
            'event-cover' => 'Cover photo',
            'event-media' => 'Photos & videos',
            'tour-route' => 'Route & map',
            'event-activities' => 'Activities',
        ],
        'toolbarCountAnchor' => 'event-activities',
        'toolbarViewUrl' => null,
        'toolbarCancelUrl' => route('admin.local-events.index'),
        'toolbarStopLabel' => 'Activity',
    ])

    <!-- Left: Main fields -->
    <div class="lg:col-span-2 space-y-6">

        @if($isEdit && ! $event->isManual())
            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                This event was imported from SmithersEvents.com. Your changes are kept as long as the importer is not run again.
            </div>
        @endif

        {{-- Basic Info --}}
        <div id="event-basics" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="px-5 py-4 border-b">
                <h3 class="text-base font-semibold leading-none tracking-tight">Basic Information</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="space-y-1.5">
                    <label for="title" class="text-sm font-medium leading-none">Event Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" required maxlength="255"
                        value="{{ old('title', $isEdit ? $event->title : '') }}"
                        placeholder="e.g., Smithers Christmas Parade"
                        class="{{ $inputClass }}">
                    @error('title') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="slug" class="text-sm font-medium leading-none">Web address</label>
                    <div class="flex flex-col gap-1 text-sm sm:flex-row sm:items-center">
                        <span class="truncate text-muted-foreground sm:max-w-[50%] sm:flex-shrink-0">{{ url('/local-events') }}/</span>
                        <input type="text" name="slug" id="slug" maxlength="255"
                            value="{{ old('slug', $isEdit ? $event->slug : '') }}"
                            placeholder="made from the title if left blank"
                            class="{{ $inputClass }}">
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Use <code>{{ $paradeSlug }}</code> for the Christmas parade so the {{ url('/christmas-parade') }} link shows it.
                    </p>
                    @error('slug') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="tagline" class="text-sm font-medium leading-none">Tagline</label>
                    <input type="text" name="tagline" id="tagline" maxlength="255"
                        value="{{ old('tagline', $isEdit ? $event->tagline : '') }}"
                        placeholder="Short one-liner shown on event cards"
                        class="{{ $inputClass }}">
                    @error('tagline') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="description" class="text-sm font-medium leading-none">Description</label>
                    <textarea name="description" id="description" rows="5"
                        placeholder="Describe the event..."
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('description', $isEdit ? $event->description : '') }}</textarea>
                    @error('description') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Date & Time: one date range with the same hours, or a list of days each with its own hours --}}
        @php
            $scheduleMode = old('schedule_mode', $isEdit && $event->days->isNotEmpty() ? 'days' : 'single');
            $scheduleDays = is_array(old('days'))
                ? collect(old('days'))->map(fn ($d) => [
                    'date' => $d['date'] ?? '',
                    'start_time' => $d['start_time'] ?? '',
                    'end_time' => $d['end_time'] ?? '',
                    'note' => $d['note'] ?? '',
                ])->values()
                : ($isEdit ? $event->days->map(fn ($d) => [
                    'date' => $d->date->toDateString(),
                    'start_time' => $d->start_time?->format('H:i') ?? '',
                    'end_time' => $d->end_time?->format('H:i') ?? '',
                    'note' => $d->note ?? '',
                ])->values() : collect());
        @endphp
        <div id="event-when" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm"
            x-data="eventSchedule(@js($scheduleDays), @js($scheduleMode))">
            <div class="px-5 py-4 border-b space-y-1.5">
                <h3 class="text-base font-semibold leading-none tracking-tight">Date &amp; Time</h3>
                <p class="text-sm text-muted-foreground">The event drops off the Events page after its last day.</p>
            </div>
            <div class="p-5 space-y-4">
                <input type="hidden" name="schedule_mode" :value="mode" value="{{ $scheduleMode }}">

                {{-- Mode switch --}}
                <div class="grid grid-cols-1 gap-1 rounded-lg bg-muted p-1 text-sm font-medium sm:inline-grid sm:grid-cols-2" role="radiogroup" aria-label="Schedule type">
                    <button type="button" role="radio" :aria-checked="mode === 'single'" @click="switchTo('single')"
                        :class="mode === 'single' ? 'bg-white text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="rounded-md px-3 py-1.5 transition-colors">Same hours every day</button>
                    <button type="button" role="radio" :aria-checked="mode === 'days'" @click="switchTo('days')"
                        :class="mode === 'days' ? 'bg-white text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="rounded-md px-3 py-1.5 transition-colors">Different dates or hours</button>
                </div>

                {{-- Same hours every day --}}
                <div x-show="mode === 'single'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="event_date" class="text-sm font-medium leading-none">Start date <span class="text-red-500">*</span></label>
                        <input type="date" name="event_date" id="event_date" :required="mode === 'single'" :disabled="mode !== 'single'"
                            value="{{ old('event_date', $isEdit ? $event->event_date?->toDateString() : '') }}"
                            class="{{ $inputClass }}">
                        @error('event_date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="end_date" class="text-sm font-medium leading-none">End date <span class="text-muted-foreground font-normal">(multi-day events)</span></label>
                        <input type="date" name="end_date" id="end_date" :disabled="mode !== 'single'"
                            value="{{ old('end_date', $isEdit ? $event->end_date?->toDateString() : '') }}"
                            class="{{ $inputClass }}">
                        @error('end_date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="event_time" class="text-sm font-medium leading-none">Start time</label>
                        <input type="time" name="event_time" id="event_time" :disabled="mode !== 'single'"
                            value="{{ old('event_time', $isEdit ? $event->event_time?->format('H:i') : '') }}"
                            class="{{ $inputClass }}">
                        @error('event_time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="end_time" class="text-sm font-medium leading-none">End time</label>
                        <input type="time" name="end_time" id="end_time" :disabled="mode !== 'single'"
                            value="{{ old('end_time', $isEdit ? $event->end_time?->format('H:i') : '') }}"
                            class="{{ $inputClass }}">
                        @error('end_time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Different dates or hours --}}
                <template x-if="mode === 'days'">
                    <div class="space-y-3">
                        <div class="hidden grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.6fr)_2rem] gap-2 px-1 text-xs font-medium text-muted-foreground sm:grid">
                            <span>Date</span><span>Opens</span><span>Closes</span><span>Note (optional)</span><span></span>
                        </div>

                        <template x-for="(day, index) in days" :key="day.uid">
                            <div class="grid grid-cols-2 gap-2 rounded-md border bg-muted/30 p-2 sm:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.6fr)_2rem] sm:items-center sm:border-0 sm:bg-transparent sm:p-0">
                                <input type="date" required :name="`days[${index}][date]`" x-model="day.date" aria-label="Date"
                                    class="{{ $inputClass }} col-span-2 sm:col-span-1">
                                <input type="time" :name="`days[${index}][start_time]`" x-model="day.start_time" aria-label="Opens"
                                    class="{{ $inputClass }}">
                                <input type="time" :name="`days[${index}][end_time]`" x-model="day.end_time" aria-label="Closes"
                                    class="{{ $inputClass }}">
                                <input type="text" maxlength="100" :name="`days[${index}][note]`" x-model="day.note" aria-label="Note"
                                    placeholder="e.g. Craft fair only"
                                    class="{{ $inputClass }} col-span-2 sm:col-span-1">
                                <button type="button" @click="removeDay(index)" :disabled="days.length === 1" title="Remove day"
                                    class="col-span-2 inline-flex h-10 items-center justify-center rounded-md border border-red-200 bg-red-50 text-xs text-red-600 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-30 sm:col-span-1 sm:w-8">
                                    <span class="sm:hidden">Remove day</span><span class="hidden sm:inline">✕</span>
                                </button>
                            </div>
                        </template>

                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <button type="button" @click="addDay()"
                                class="inline-flex h-9 items-center gap-1.5 rounded-md border border-dashed border-input bg-background px-3 text-sm font-medium hover:bg-accent transition-colors">
                                + Add day
                            </button>
                            <span class="text-xs text-muted-foreground" x-text="summary"></span>
                        </div>
                        <p class="text-xs text-muted-foreground">Leave the times empty for an all-day date. "+ Add day" copies the last day's hours to the next date.</p>
                    </div>
                </template>

                @error('days') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @foreach(collect($errors->get('days.*'))->flatten()->unique() as $message)
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @endforeach
            </div>
        </div>
        <script>
            function eventSchedule(initialDays, mode) {
                let nextUid = 0;
                const makeDay = (day = {}) => ({ uid: ++nextUid, date: '', start_time: '', end_time: '', note: '', ...day });
                const parse = (value) => {
                    const [y, m, d] = String(value).split('-').map(Number);
                    return y ? new Date(y, m - 1, d) : null;
                };
                const iso = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
                const field = (id) => document.getElementById(id)?.value || '';

                return {
                    mode,
                    days: initialDays.map((day) => makeDay(day)),

                    /** Switching to the day list starts it from the single-range fields so nothing is lost. */
                    switchTo(newMode) {
                        this.mode = newMode;
                        if (newMode === 'days' && this.days.length === 0) {
                            this.days.push(makeDay({ date: field('event_date'), start_time: field('event_time'), end_time: field('end_time') }));
                        }
                    },

                    addDay() {
                        const last = this.days[this.days.length - 1];
                        const lastDate = last ? parse(last.date) : null;
                        let date = '';
                        if (lastDate) {
                            lastDate.setDate(lastDate.getDate() + 1);
                            date = iso(lastDate);
                        }
                        this.days.push(makeDay({ date, start_time: last?.start_time || '', end_time: last?.end_time || '' }));
                    },

                    removeDay(index) {
                        if (this.days.length > 1) {
                            this.days.splice(index, 1);
                        }
                    },

                    get summary() {
                        const dates = this.days.map((day) => parse(day.date)).filter(Boolean).sort((a, b) => a - b);
                        if (dates.length === 0) {
                            return '';
                        }
                        const label = (date) => date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                        const count = `${this.days.length} day${this.days.length === 1 ? '' : 's'}`;
                        return dates.length === 1
                            ? `${count} · ${label(dates[0])}`
                            : `${count} · ${label(dates[0])} – ${label(dates[dates.length - 1])}`;
                    },
                };
            }
        </script>

        {{-- Where & Who --}}
        <div id="event-where" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="px-5 py-4 border-b">
                <h3 class="text-base font-semibold leading-none tracking-tight">Where &amp; Who</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="venue" class="text-sm font-medium leading-none">Venue</label>
                    <input type="text" name="venue" id="venue" maxlength="255"
                        value="{{ old('venue', $isEdit ? $event->venue : '') }}"
                        placeholder="e.g., Main Street"
                        class="{{ $inputClass }}">
                    @error('venue') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="location" class="text-sm font-medium leading-none">Location / address</label>
                    <input type="text" name="location" id="location" maxlength="255"
                        value="{{ old('location', $isEdit ? $event->location : '') }}"
                        placeholder="e.g., Downtown Smithers, BC"
                        class="{{ $inputClass }}">
                    <p class="text-xs text-muted-foreground">Shown on the events calendar and in calendar invites. Uses the venue if left blank.</p>
                    @error('location') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="organizer" class="text-sm font-medium leading-none">Organizer</label>
                    <input type="text" name="organizer" id="organizer" maxlength="255"
                        value="{{ old('organizer', $isEdit ? $event->organizer : '') }}"
                        class="{{ $inputClass }}">
                    @error('organizer') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label for="category" class="text-sm font-medium leading-none">Category</label>
                    <input type="text" name="category" id="category" maxlength="100" list="event-categories"
                        value="{{ old('category', $isEdit ? $event->category : '') }}"
                        placeholder="e.g., Community"
                        class="{{ $inputClass }}">
                    <datalist id="event-categories">
                        @foreach($categories as $category)
                            <option value="{{ $category }}"></option>
                        @endforeach
                    </datalist>
                    <p class="text-xs text-muted-foreground">Used by the category filter on the events calendar.</p>
                    @error('category') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label for="external_url" class="text-sm font-medium leading-none">"More info" link</label>
                    <input type="url" name="external_url" id="external_url" maxlength="500"
                        value="{{ old('external_url', $isEdit ? $event->external_url : '') }}"
                        placeholder="https://www.facebook.com/events/..."
                        class="{{ $inputClass }}">
                    @error('external_url') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Look: theme + map icon --}}
        <div id="event-look" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="px-5 py-4 border-b">
                <h3 class="text-base font-semibold leading-none tracking-tight">Look</h3>
            </div>
            <div class="p-5 space-y-5">
                <div class="space-y-1.5" x-data="{ christmas: @js($isChristmas) }">
                    <p class="text-sm font-medium leading-none">Christmas theme</p>
                    <label for="theme" class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-md border border-input bg-background px-3 py-2 sm:max-w-xs">
                        <span class="text-sm" x-text="christmas ? '🎄 On' : 'Off'">{{ $isChristmas ? '🎄 On' : 'Off' }}</span>
                        <input type="hidden" name="theme" value="{{ App\Models\Event::THEME_STANDARD }}">
                        <input type="checkbox" id="theme" name="theme" value="{{ App\Models\Event::THEME_CHRISTMAS }}" x-model="christmas" class="peer sr-only"
                            @checked($isChristmas)>
                        <span class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-red-600 peer-focus-visible:ring-4 peer-focus-visible:ring-red-100">
                            <span class="absolute left-0.5 inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200" :class="christmas ? 'translate-x-5' : 'translate-x-0'"></span>
                        </span>
                    </label>
                    <p class="text-xs text-muted-foreground">Turns the event page festive: Christmas lights, snowfall, holiday fonts and colours (set the colour in Settings → Events), and a red route on the map.</p>
                    @error('theme') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                @include('admin.tours._icon-picker', [
                    'iconLabel' => 'Event icon',
                    'iconValue' => old('icon', $isEdit ? ($event->icon ?? '') : ''),
                    'iconImageValue' => old('icon_image', $isEdit ? ($event->icon_image ?? '') : ''),
                    'iconMaxLength' => 16,
                    'iconPlaceholder' => '🎄',
                ])
            </div>
        </div>

        {{-- Cover Image --}}
        <div id="event-cover" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="px-5 py-4 border-b space-y-1.5">
                <h3 class="text-base font-semibold leading-none tracking-tight">Cover Image</h3>
                <p class="text-sm text-muted-foreground">Hero photo for the event page and its card. A wide (landscape) photo works best.</p>
            </div>
            <div class="p-5 space-y-4">
                @if($isEdit && $event->cover_image)
                    <div id="current-cover-image" class="space-y-2">
                        <p class="text-sm font-medium leading-none">Current Cover Image</p>
                        <div class="relative w-full max-w-sm">
                            <img src="{{ $event->cover_image_url }}" alt="Current cover" class="w-full h-40 object-cover rounded-md border">
                            <button type="button" title="Delete cover image"
                                onclick="deleteTourCoverImage('{{ route('admin.local-events.cover-image.delete', $event) }}', this)"
                                class="absolute top-2 right-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white hover:bg-destructive transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-muted-foreground">Upload a new image below to replace it, or delete it.</p>
                    </div>
                @endif
                <div id="cover-image-dropzone" class="border-2 border-dashed border-input rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer"
                    onclick="document.getElementById('cover_image').click()">
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" class="hidden" onchange="previewCoverImage(this)">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium text-muted-foreground">Click or drag cover image here to upload</p>
                    <p class="text-xs text-muted-foreground mt-1">JPEG, PNG, WebP up to 50MB</p>
                </div>
                <div id="cover-preview" class="hidden">
                    <img id="cover-preview-img" src="" alt="Preview" class="w-full max-w-sm h-40 object-cover rounded-md border">
                </div>
                @error('cover_image') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Photos & Videos --}}
        <div id="event-media" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm"
            x-data="eventGallery(@js($galleryItems))">
            <div class="px-5 py-4 border-b space-y-1.5">
                <h3 class="text-base font-semibold leading-none tracking-tight">Photos &amp; Videos</h3>
                <p class="text-sm text-muted-foreground">A featured video and a gallery shown on the event page.</p>
            </div>
            <div class="p-5 space-y-5">
                {{-- Featured video --}}
                <div class="space-y-1.5">
                    <label for="video_url" class="text-sm font-medium leading-none">Featured video (YouTube or Vimeo)</label>
                    <input type="url" name="video_url" id="video_url" maxlength="500"
                        value="{{ old('video_url', $isEdit ? $event->video_url : '') }}"
                        placeholder="https://www.youtube.com/watch?v=..."
                        oninput="updateTourVideoPreview(this.value)"
                        class="{{ $inputClass }}">
                    <p class="text-xs text-muted-foreground">Shown near the top of the event page.</p>
                    @error('video_url') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @php $videoThumb = $isEdit ? $event->video_thumbnail_url : null; @endphp
                    <div id="tour-video-preview" class="{{ $videoThumb ? '' : 'hidden' }} pt-1">
                        <button type="button" onclick="playAdminVideo(document.getElementById('video_url').value)"
                            class="relative block w-full max-w-xs aspect-video rounded-md overflow-hidden bg-gray-900 group">
                            <img id="tour-video-preview-img" src="{{ $videoThumb ?? '' }}" alt="Video preview"
                                class="absolute inset-0 w-full h-full object-cover {{ $videoThumb ? '' : 'hidden' }}">
                            <span class="absolute inset-0 flex items-center justify-center">
                                <span class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center shadow group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-gray-900 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Gallery --}}
                <div class="space-y-3 border-t pt-5">
                    <div class="space-y-1">
                        <p class="text-sm font-medium leading-none">Gallery</p>
                        <p class="text-xs text-muted-foreground">Photos upload as soon as you pick them; use the arrows to change the order.</p>
                    </div>
                    <template x-if="true"><input type="hidden" name="media_sync" value="1"></template>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                        <label class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-dashed border-input bg-background px-4 h-10 text-sm font-medium hover:bg-muted transition-colors"
                            @dragover.prevent @drop.prevent="addFiles($event.dataTransfer.files)">
                            📷 Add photos
                            <input type="file" accept="image/*" multiple class="hidden" @change="addFiles($event.target.files); $event.target.value = ''">
                        </label>
                        <div class="flex flex-1 gap-2">
                            <input type="url" x-model="newVideoUrl" @keydown.enter.prevent="addVideo()" data-no-dirty
                                placeholder="Paste a YouTube or Vimeo link"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                            <button type="button" @click="addVideo()"
                                class="inline-flex flex-shrink-0 items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-10 px-4 text-sm font-medium transition-colors">
                                🎬 Add video
                            </button>
                        </div>
                    </div>
                    <p x-show="error" x-text="error" class="text-sm text-red-600"></p>
                    @error('media') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    @foreach($errors->get('media.*') as $messages)
                        <p class="text-sm text-red-600">{{ $messages[0] }}</p>
                    @endforeach

                    <div x-show="items.length > 0" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <template x-for="(item, index) in items" :key="item.key">
                            <div class="overflow-hidden rounded-md border bg-muted/30">
                                <div class="relative aspect-video bg-gray-900">
                                    <template x-if="item.thumbnail_url">
                                        <img :src="item.thumbnail_url" alt="" class="h-full w-full object-cover">
                                    </template>
                                    <div x-show="item.uploading" class="absolute inset-0 flex items-center justify-center bg-black/50 text-xs font-medium text-white">Uploading…</div>
                                    <span x-show="item.type === 'video'" class="absolute left-1.5 top-1.5 rounded bg-red-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">▶ Video</span>
                                    <span x-show="index === 0" class="absolute right-1.5 top-1.5 rounded bg-black/60 px-1.5 py-0.5 text-[10px] font-semibold text-white">First</span>
                                </div>
                                <div class="space-y-2 p-2">
                                    <input type="text" x-model="item.caption" maxlength="255" placeholder="Caption (optional)"
                                        class="{{ $smallInputClass }}">
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="flex gap-1">
                                            <button type="button" @click="move(index, -1)" :disabled="index === 0" title="Move earlier"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded border border-input bg-background text-xs hover:bg-accent disabled:opacity-30">◀</button>
                                            <button type="button" @click="move(index, 1)" :disabled="index === items.length - 1" title="Move later"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded border border-input bg-background text-xs hover:bg-accent disabled:opacity-30">▶</button>
                                        </div>
                                        <button type="button" @click="remove(index)"
                                            class="inline-flex h-7 items-center rounded border border-red-200 bg-red-50 px-2 text-xs text-red-600 hover:bg-red-100">Remove</button>
                                    </div>
                                </div>

                                <template x-if="! item.uploading">
                                    <div>
                                        <input type="hidden" :name="`media[${index}][id]`" :value="item.id || ''">
                                        <input type="hidden" :name="`media[${index}][path]`" :value="item.path || ''">
                                        <input type="hidden" :name="`media[${index}][thumbnail_path]`" :value="item.thumbnail_path || ''">
                                        <input type="hidden" :name="`media[${index}][video_url]`" :value="item.id ? '' : (item.video_url || '')">
                                        <input type="hidden" :name="`media[${index}][caption]`" :value="item.caption">
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <p x-show="items.length === 0" class="rounded-md border border-dashed py-6 text-center text-sm text-muted-foreground">No photos or videos yet.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Settings sidebar -->
    <div class="space-y-6">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="px-5 py-4 border-b">
                <h3 class="text-base font-semibold leading-none tracking-tight">Settings</h3>
            </div>
            <div class="p-5 space-y-4">
                @include('admin._town-select', ['selected' => $isEdit ? $event->town_id : null])

                <div class="pt-2 border-t space-y-3">
                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $isEdit ? $event->is_active : true) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_active" class="text-sm font-medium leading-none">Active (visible on site)</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                            {{ old('is_featured', $isEdit ? $event->is_featured : false) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        <label for="is_featured" class="text-sm font-medium leading-none">Featured (shown first)</label>
                    </div>
                </div>
            </div>
        </div>

        @if($isEdit)
            {{-- Danger zone (submits the hidden delete form on the edit page) --}}
            <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                <h3 class="text-sm font-semibold text-red-800">Danger Zone</h3>
                <p class="mt-1 text-xs text-red-700">Permanently delete this event and its map activities. It also disappears from the events calendar.</p>
                <button type="submit" form="delete-event-form"
                    onclick="return confirm(@js('Delete "' . $event->title . '"? This cannot be undone.'))"
                    class="mt-3 inline-flex h-9 w-full items-center justify-center rounded-md bg-red-600 px-4 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                    Delete Event
                </button>
            </div>
        @endif
    </div>

    {{-- Route & Activities: full width, map beside the activity list (stacked, map first, on smaller screens) --}}
    <div class="lg:col-span-3 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,11fr)_minmax(0,9fr)] xl:items-start">
        <div class="min-w-0 xl:sticky xl:top-40">
            {{-- Route & Map editor --}}
            <div id="tour-route" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col gap-2 px-5 py-4 border-b">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-base font-semibold leading-none tracking-tight">Route &amp; Map <span class="text-sm font-normal text-muted-foreground">(optional)</span></h3>
                        <span class="text-xs text-muted-foreground">Click a pin to open that activity</span>
                    </div>
                    <details class="group rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs text-green-800">
                        <summary class="cursor-pointer select-none font-medium">How this works</summary>
                        <p class="mt-1.5">
                            Draw a route (e.g. the parade route) by clicking along the streets in order — arrows show the direction you draw it.
                            Use <strong>📍 Pin activity on map</strong> to add places people can visit; pinned activities can be dragged to fine-tune them.
                            Leave this empty for a simple event and the page hides the map.
                        </p>
                    </details>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" id="route-draw-btn" onclick="tourRouteEditor.toggleMode('draw')"
                            class="route-tool-btn {{ $routeButtonClass }}">
                            ✏️ Draw route
                        </button>
                        <button type="button" id="route-pin-btn" onclick="tourRouteEditor.toggleMode('pin')"
                            class="route-tool-btn {{ $routeButtonClass }}">
                            📍 Pin activity on map
                        </button>
                        <span class="w-px bg-border mx-0.5 hidden sm:block"></span>
                        <button type="button" id="route-undo-btn" onclick="tourRouteEditor.undo()"
                            class="{{ $routeButtonClass }}">
                            ↶ Undo
                        </button>
                        <button type="button" id="route-clear-btn" onclick="tourRouteEditor.clear()"
                            class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 h-8 px-2.5 text-xs font-medium transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                            Clear route
                        </button>
                        <span class="w-px bg-border mx-0.5 hidden sm:block"></span>
                        <button type="button" id="route-snap-btn" onclick="tourRouteEditor.snapToStreets()"
                            title="Re-trace the drawn line along real roads (uses the route service)"
                            class="{{ $routeButtonClass }}">
                            🧲 Snap to streets
                        </button>
                    </div>

                    <div id="route-mode-hint" class="hidden rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-xs text-blue-800"></div>
                    <div id="route-status" class="hidden text-sm text-muted-foreground"></div>

                    <div class="relative">
                        <div id="route-map" data-default-layer="satellite" class="w-full h-[380px] xl:h-[calc(100vh-22rem)] xl:min-h-[380px] rounded-md border"></div>
                        <div class="absolute top-3 left-3 z-10 inline-flex bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden text-xs font-medium">
                            <button type="button" data-map-layer="standard" onclick="tourRouteEditor.setLayer('standard')"
                                class="route-layer-btn px-3 py-1.5 transition-colors bg-white text-gray-700 hover:bg-gray-50">Standard</button>
                            <button type="button" data-map-layer="satellite" onclick="tourRouteEditor.setLayer('satellite')"
                                class="route-layer-btn px-3 py-1.5 transition-colors bg-green-600 text-white border-l border-gray-200">Satellite</button>
                        </div>
                    </div>

                    <p class="text-xs text-muted-foreground">
                        <span id="route-point-count">0</span> route points · <span id="route-km">0.0</span> km.
                        Tip: zoom in close and click at each corner.
                    </p>
                    <textarea name="route_coordinates" id="driving_route_coordinates" class="hidden">{{ $routeJson }}</textarea>
                    <input type="hidden" name="route_distance_km" id="total_driving_km" value="{{ old('route_distance_km', $isEdit ? $event->route_distance_km : '') }}">
                </div>
            </div>
        </div>

        <div class="min-w-0">
            {{-- Activities --}}
            <div id="event-activities" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="px-5 py-4 border-b space-y-1.5">
                    <h3 class="text-base font-semibold leading-none tracking-tight">Activities &amp; Stops</h3>
                    <p class="text-sm text-muted-foreground">
                        Pin activities with <a href="#tour-route" class="font-medium text-foreground underline">📍 Pin activity on map</a>,
                        or add a roaming activity that has no fixed spot (e.g. carollers). Give each one an icon so visitors can tap it on the map.
                    </p>
                </div>
                <div class="p-5 space-y-4">
                    <button type="button" @click="addRoamingStop()"
                        class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-input bg-background hover:bg-accent h-8 px-3 text-xs font-medium transition-colors">
                        🚶 Add roaming activity <span class="text-muted-foreground">(no fixed spot)</span>
                    </button>
                    @error('stops') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    <div x-show="stops.length > 0" class="space-y-2">
                        <div class="flex items-center justify-between text-xs text-muted-foreground">
                            <span x-text="`${stops.length} activit${stops.length === 1 ? 'y' : 'ies'}`"></span>
                            <span class="flex gap-2">
                                <button type="button" @click="setAllExpanded(true)" class="font-medium hover:text-foreground">Expand all</button>
                                <span>·</span>
                                <button type="button" @click="setAllExpanded(false)" class="font-medium hover:text-foreground">Collapse all</button>
                            </span>
                        </div>

                        <template x-for="(stop, index) in stops" :key="stop.uid">
                            <div class="rounded-md border bg-muted/30 transition-shadow" data-stop-row
                                :class="{
                                    'ring-2 ring-red-400 border-red-300': stop.has_error || (!stop.expanded && !stop.stop_label.trim()),
                                    'ring-2 ring-gray-900 border-gray-900': activeStopUid === stop.uid && !stop.has_error,
                                }">

                                {{-- Summary (always visible; click to expand) --}}
                                <div class="flex items-center gap-2 p-2.5">
                                    <div class="flex-shrink-0 flex h-7 w-7 items-center justify-center rounded-full bg-black text-white text-xs font-bold overflow-hidden">
                                        <template x-if="stop.icon_image_url">
                                            <img :src="stop.icon_image_url" alt="" class="h-full w-full object-contain bg-white">
                                        </template>
                                        <span x-show="!stop.icon_image_url" x-text="stop.icon || (index + 1)" :class="stop.icon ? 'text-base' : ''"></span>
                                    </div>

                                    <template x-if="stop.new_image_preview || (stop.image_url && !stop.remove_image)">
                                        <img :src="stop.new_image_preview || stop.image_url" alt=""
                                            class="h-9 w-9 flex-shrink-0 rounded object-cover border">
                                    </template>

                                    <button type="button" @click="toggleStop(index)"
                                        class="min-w-0 flex-1 text-left" :aria-expanded="stop.expanded">
                                        <span class="flex flex-wrap items-center gap-1.5">
                                            <span x-show="stop.stop_label" class="truncate text-sm font-medium" x-text="stop.stop_label"></span>
                                            <span x-show="!stop.stop_label" class="text-sm font-medium text-red-600">Needs a name</span>
                                            <span x-show="stop.item_type === 'pinned'"
                                                class="inline-flex items-center rounded-full bg-red-50 border border-red-200 px-1.5 py-0.5 text-[11px] font-medium text-red-700">📍 Pinned</span>
                                            <span x-show="stop.item_type === 'roaming'"
                                                class="inline-flex items-center rounded-full bg-purple-50 border border-purple-200 px-1.5 py-0.5 text-[11px] font-medium text-purple-700">🚶 Roaming · no map pin</span>
                                        </span>
                                        <span class="mt-0.5 flex flex-wrap gap-x-3 text-[11px] text-muted-foreground">
                                            <span x-show="stop.estimated_visit_time" x-text="'🕒 ' + stop.estimated_visit_time"></span>
                                            <span x-show="stop.driving_notes" class="truncate" x-text="stop.driving_notes"></span>
                                            <span x-text="stop.description ? '📝 Description' : 'No description'" :class="stop.description ? '' : 'text-amber-700'"></span>
                                        </span>
                                    </button>

                                    <div class="flex flex-shrink-0 items-center gap-1">
                                        <button type="button" @click="toggleStop(index)"
                                            class="inline-flex h-7 items-center rounded border border-input bg-background px-2 text-xs font-medium hover:bg-accent"
                                            x-text="stop.expanded ? 'Done' : 'Edit'"></button>
                                        <button type="button" @click="moveUp(index)" :disabled="index === 0" title="Move up"
                                            class="inline-flex items-center justify-center h-7 w-7 rounded border border-input bg-background hover:bg-accent disabled:opacity-30 disabled:cursor-not-allowed text-xs">▲</button>
                                        <button type="button" @click="moveDown(index)" :disabled="index === stops.length - 1" title="Move down"
                                            class="inline-flex items-center justify-center h-7 w-7 rounded border border-input bg-background hover:bg-accent disabled:opacity-30 disabled:cursor-not-allowed text-xs">▼</button>
                                        <button type="button" @click="removeStop(index)" title="Remove activity"
                                            class="inline-flex items-center justify-center h-7 w-7 rounded border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-xs">✕</button>
                                    </div>
                                </div>

                                {{-- Editor (hidden inputs stay in the DOM while collapsed so they still submit) --}}
                                <div x-show="stop.expanded" class="space-y-3 border-t bg-background/60 p-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <input type="text" :name="`stops[${index}][stop_label]`" x-model="stop.stop_label" required maxlength="255"
                                            placeholder="Name (required, e.g. Wagon Rides)"
                                            class="{{ $smallInputClass }}">
                                        <input type="text" :name="`stops[${index}][time_label]`" x-model="stop.estimated_visit_time" maxlength="100"
                                            placeholder="Time (e.g., 12:00 – 4:00 PM)"
                                            class="{{ $smallInputClass }}">
                                        <input type="text" :name="`stops[${index}][notes]`" x-model="stop.driving_notes" maxlength="500"
                                            placeholder="Where / note (e.g., Bugwood Beans, Main St)"
                                            class="{{ $smallInputClass }} sm:col-span-2">
                                        <input type="hidden" :name="`stops[${index}][latitude]`" :value="stop.item_type === 'pinned' ? stop.latitude : ''">
                                        <input type="hidden" :name="`stops[${index}][longitude]`" :value="stop.item_type === 'pinned' ? stop.longitude : ''">
                                        <input type="hidden" :name="`stops[${index}][is_roaming]`" :value="stop.item_type === 'roaming' ? 1 : 0">
                                        <input type="hidden" :name="`stops[${index}][icon]`" :value="stop.icon || ''">
                                        <input type="hidden" :name="`stops[${index}][icon_image]`" :value="stop.icon_image || ''">
                                        <input type="hidden" :name="`stops[${index}][existing_image]`" :value="stop.existing_image || ''">
                                        <input type="hidden" :name="`stops[${index}][remove_image]`" :value="stop.remove_image ? 1 : 0">
                                    </div>

                                    {{-- Icon --}}
                                    <div class="space-y-1.5">
                                        <p class="text-xs font-medium">Map icon</p>
                                        <div class="flex flex-wrap items-center gap-1">
                                            <template x-for="emoji in iconPresets" :key="emoji">
                                                <button type="button" @click="setStopEmoji(stop, emoji)"
                                                    :class="stop.icon === emoji && !stop.icon_image ? 'border-red-500 bg-red-50' : 'border-transparent hover:bg-muted'"
                                                    class="h-8 w-8 rounded-md border text-lg leading-none" x-text="emoji" :title="'Use ' + emoji"></button>
                                            </template>
                                            <input type="text" maxlength="16" x-model="stop.icon" @input="stop.icon_image = ''; stop.icon_image_url = null"
                                                placeholder="or type"
                                                class="h-8 w-20 rounded-md border border-input bg-background px-2 text-sm placeholder:text-xs placeholder:text-muted-foreground">
                                            <button type="button" @click="toggleIconPicker(stop)"
                                                class="h-8 rounded-md border border-input bg-background px-2 text-xs font-medium hover:bg-accent">
                                                🖼️ Image icon
                                            </button>
                                            <button type="button" x-show="stop.icon || stop.icon_image" @click="clearStopIcon(stop)"
                                                class="h-8 rounded-md px-2 text-xs text-red-600 hover:bg-red-50">Clear</button>
                                        </div>
                                        <div x-show="stop.showIconPicker" class="rounded-md border bg-muted/30 p-2 space-y-2">
                                            <div class="flex flex-wrap gap-1.5">
                                                <template x-for="ic in iconGallery" :key="ic.path">
                                                    <button type="button" @click="chooseStopIconImage(stop, ic)"
                                                        :class="stop.icon_image === ic.path ? 'border-red-500' : 'border-transparent hover:border-gray-300'"
                                                        class="h-9 w-9 rounded-md border-2 bg-white p-0.5">
                                                        <img :src="ic.url" alt="" class="h-full w-full object-contain">
                                                    </button>
                                                </template>
                                                <span x-show="iconGalleryStatus" x-text="iconGalleryStatus" class="text-xs text-muted-foreground self-center"></span>
                                            </div>
                                            <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-dashed border-input bg-background px-2 py-1 text-xs text-muted-foreground hover:bg-muted">
                                                Upload new icon
                                                <input type="file" accept="image/*" class="hidden" @change="uploadStopIcon(stop, $event)">
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-medium">Description</p>
                                            <span class="text-[11px] text-muted-foreground tabular-nums"
                                                x-text="`${(stop.description || '').length.toLocaleString()} / 2,000`"></span>
                                        </div>
                                        <textarea :name="`stops[${index}][description]`" x-model="stop.description" rows="3" maxlength="2000"
                                            placeholder="What's happening (e.g., Free horse-drawn wagon rides around downtown)"
                                            class="flex w-full rounded-md border border-input bg-background px-2 py-1.5 text-xs leading-relaxed placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"></textarea>
                                    </div>

                                    {{-- Photo --}}
                                    <div class="flex items-center gap-2">
                                        <template x-if="stop.new_image_preview || (stop.image_url && !stop.remove_image)">
                                            <img :src="stop.new_image_preview || stop.image_url" alt="" class="h-12 w-12 rounded-md border object-cover">
                                        </template>
                                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-dashed border-input bg-background px-2 py-1.5 text-xs text-muted-foreground hover:bg-muted">
                                            📷 <span x-text="(stop.new_image_preview || (stop.image_url && !stop.remove_image)) ? 'Replace photo' : 'Add photo / flyer (optional)'"></span>
                                            <input type="file" accept="image/*" class="hidden" :name="`stops[${index}][image]`" @change="previewStopImage(stop, $event)">
                                        </label>
                                        <button type="button" x-show="stop.new_image_preview || (stop.image_url && !stop.remove_image)"
                                            @click="removeStopImage(stop, $el)"
                                            class="text-xs text-red-600 hover:underline">Remove photo</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <p x-show="stops.length === 0" class="rounded-md border border-dashed py-6 text-center text-sm text-muted-foreground">
                        No activities yet. Pin one on the map or add a roaming activity.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
