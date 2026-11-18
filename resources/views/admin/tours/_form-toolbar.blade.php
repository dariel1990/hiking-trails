{{--
    Sticky bar across the top of a long admin editor form: jump links to each section,
    an unsaved-changes indicator, and save buttons that stay reachable while scrolling.
    Lives inside the tourForm Alpine component (see admin.tours._scripts), which is
    shared by the tour and event editors.

    Optional variables (the defaults are the tour editor's):
      $toolbarSections    anchor => label jump links
      $toolbarCountAnchor the section that shows the stop count badge
      $toolbarViewUrl     "View on site" link (null hides it)
      $toolbarCancelUrl   "Cancel" link (null hides it)
      $toolbarStopLabel   what a stop is called in the error summary ("Stop", "Activity")
--}}
@php
$toolbarSections ??= [
    'tour-basics' => 'Basics',
    'tour-cover' => 'Cover photo',
    'tour-details' => 'Details',
    'tour-route' => 'Route & map',
    'tour-stops' => 'Stops',
];
$toolbarCountAnchor ??= 'tour-stops';
if (! isset($toolbarViewUrl) && isset($section, $tour)) {
    $toolbarViewUrl = $tour->is_active && Route::has($section['publicShowRoute'])
        ? route($section['publicShowRoute'], $tour)
        : null;
}
$toolbarViewUrl ??= null;
$toolbarCancelUrl ??= null;
$toolbarStopLabel ??= 'Stop';
@endphp
{{--
    Below xl: section links wrap (all visible), buttons on their own row below.
    xl and up: links and buttons share one row; links still wrap if they don't fit.
--}}
<div class="lg:col-span-3 sticky top-16 z-40 -mx-1 px-1">
    <div class="flex flex-col gap-2 rounded-lg border bg-white/95 p-2 shadow-sm backdrop-blur sm:px-3 xl:flex-row xl:items-center xl:gap-3 xl:py-2.5">
        <nav class="flex min-w-0 flex-1 flex-wrap items-center gap-0.5 text-[13px] sm:text-sm" aria-label="Form sections">
            @foreach($toolbarSections as $anchor => $label)
                <a href="#{{ $anchor }}"
                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-md px-2 py-1 font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors sm:px-2.5 sm:py-1.5">
                    {{ $label }}
                    @if($anchor === $toolbarCountAnchor)
                        <span class="rounded-full bg-gray-900 px-1.5 text-[11px] font-semibold text-white tabular-nums" x-text="stops.length"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="flex flex-shrink-0 items-center gap-2 border-t pt-2 xl:border-t-0 xl:pt-0">
            <span x-show="dirty" x-cloak title="Unsaved changes"
                class="mr-auto inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 xl:mr-0">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                <span class="sm:hidden">Unsaved</span>
                <span class="hidden sm:inline">Unsaved changes</span>
            </span>
            <span x-show="!dirty" class="mr-auto xl:hidden"></span>
            @if($toolbarCancelUrl)
                <a href="{{ $toolbarCancelUrl }}"
                    class="hidden h-9 items-center rounded-md px-3 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors sm:inline-flex">
                    Cancel
                </a>
            @endif
            @if($toolbarViewUrl)
                <a href="{{ $toolbarViewUrl }}" target="_blank" rel="noopener" title="View on site"
                    class="inline-flex h-9 items-center gap-1.5 rounded-md border border-input bg-background px-2.5 text-sm font-medium hover:bg-accent transition-colors sm:px-3">
                    <span class="sr-only sm:not-sr-only">View on site</span>
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif
            <button type="submit" name="after_save" value="edit"
                class="inline-flex h-9 items-center whitespace-nowrap rounded-md border border-input bg-background px-3 text-sm font-medium hover:bg-accent transition-colors">
                <span class="sm:hidden">{{ $isEdit ? 'Save' : 'Create' }}</span>
                <span class="hidden sm:inline">{{ $isEdit ? 'Save' : 'Create' }} &amp; keep editing</span>
            </button>
            <button type="submit"
                class="inline-flex h-9 items-center whitespace-nowrap rounded-md bg-black px-3 text-sm font-medium text-white hover:bg-black/90 transition-colors sm:px-4">
                {{ $isEdit ? 'Save' : 'Create' }} &amp; close
            </button>
        </div>
    </div>
</div>

{{-- Not sticky: a long error list would otherwise cover the form while scrolling. --}}
@if($errors->any())
<div class="lg:col-span-3">
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
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
                                <a href="#{{ $toolbarCountAnchor }}" class="font-medium underline">{{ $toolbarStopLabel }} {{ $stopNumber }}{{ $stopName ? ' ('.$stopName.')' : '' }}</a>:
                            @endif
                            {{ $message }}
                        </li>
                    @endforeach
                @endforeach
            </ul>
            <p class="mt-2 text-xs text-red-700">Your other changes were kept, but any <strong>new</strong> photos you picked (cover or stop photos) need to be selected again.</p>
        </div>
</div>
@endif
