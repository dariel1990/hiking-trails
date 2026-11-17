{{--
    Sticky bar across the top of the tour form: jump links to each section, an
    unsaved-changes indicator, and save buttons that stay reachable on this long page.
    Lives inside the tourForm Alpine component (see _scripts).
--}}
<div class="lg:col-span-3 sticky top-16 z-40 -mx-1 px-1">
    <div class="flex flex-col gap-3 rounded-lg border bg-white/95 px-4 py-3 shadow-sm backdrop-blur sm:flex-row sm:items-center sm:justify-between">
        <nav class="flex flex-wrap items-center gap-1 text-sm" aria-label="Form sections">
            @foreach([
                'tour-basics' => 'Basics',
                'tour-cover' => 'Cover photo',
                'tour-details' => 'Details',
                'tour-stops' => 'Stops',
                'tour-route' => 'Route & map',
            ] as $anchor => $label)
                <a href="#{{ $anchor }}"
                    class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                    {{ $label }}
                    @if($anchor === 'tour-stops')
                        <span class="rounded-full bg-gray-900 px-1.5 text-[11px] font-semibold text-white tabular-nums" x-text="stops.length"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="flex flex-wrap items-center gap-2">
            <span x-show="dirty" x-cloak class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                Unsaved changes
            </span>
            @if($isEdit && $tour->is_active && Route::has($section['publicShowRoute']))
                <a href="{{ route($section['publicShowRoute'], $tour) }}" target="_blank" rel="noopener"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent transition-colors">
                    View on site
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif
            <button type="submit" name="after_save" value="edit"
                class="inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent transition-colors">
                {{ $isEdit ? 'Save' : 'Create' }} &amp; keep editing
            </button>
            <button type="submit"
                class="inline-flex h-9 items-center rounded-md bg-black px-4 text-sm font-medium text-white hover:bg-black/90 transition-colors">
                {{ $isEdit ? 'Save' : 'Create' }} &amp; close
            </button>
        </div>
    </div>

    @if($errors->any())
        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-semibold">Please fix {{ $errors->count() === 1 ? 'this problem' : 'these '.$errors->count().' problems' }} and save again:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->getMessages() as $key => $messages)
                    @php
                        preg_match('/^stops\.(\d+)/', $key, $stopMatch);
                        $stopNumber = isset($stopMatch[1]) ? (int) $stopMatch[1] + 1 : null;
                        $stopName = $stopNumber ? (old('stops.'.($stopNumber - 1).'.stop_label') ?: null) : null;
                    @endphp
                    @foreach($messages as $message)
                        <li>
                            @if($stopNumber)
                                <a href="#tour-stops" class="font-medium underline">Stop {{ $stopNumber }}{{ $stopName ? ' ('.$stopName.')' : '' }}</a>:
                            @endif
                            {{ $message }}
                        </li>
                    @endforeach
                @endforeach
            </ul>
            <p class="mt-2 text-xs text-red-700">Your other changes were kept, but any <strong>new</strong> photos you picked (cover or stop photos) need to be selected again.</p>
        </div>
    @endif
</div>
