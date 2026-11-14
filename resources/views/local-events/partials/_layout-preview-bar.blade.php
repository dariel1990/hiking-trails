{{--
    Admin-only bar shown while previewing event page layouts (?layout=):
    switch between layouts, or make the previewed one live.
    Expects $pageLayout, $isLayoutPreview. Optional $barOffset (bottom position classes).
--}}
@if(request()->has('layout') && auth()->user()?->is_admin)
    @php($liveLayout = setting('event_page_layout'))
    <div class="fixed left-1/2 z-[150] w-[calc(100%-2rem)] max-w-3xl -translate-x-1/2 {{ $barOffset ?? 'bottom-4' }}">
        <div class="flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-2 text-xs shadow-xl sm:flex-row sm:items-center">
            <nav aria-label="Event page layouts" class="flex min-w-0 flex-1 gap-1 overflow-x-auto">
                @foreach(\App\Http\Controllers\LocalEventController::layouts() as $layoutKey => $layoutOption)
                    <a href="{{ url()->current() }}?layout={{ $layoutKey }}"
                        @if($layoutKey === $pageLayout) aria-current="page" @endif
                        class="whitespace-nowrap rounded-full px-3 py-1.5 font-semibold {{ $layoutKey === $pageLayout ? 'bg-forest-700 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        {{ $layoutOption['label'] }}@if($layoutKey === $liveLayout)<span class="ml-1 font-normal opacity-75">(live)</span>@endif
                    </a>
                @endforeach
            </nav>
            <div class="flex flex-shrink-0 items-center gap-2">
                @if($isLayoutPreview)
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" value="events">
                        <input type="hidden" name="event_page_layout" value="{{ $pageLayout }}">
                        {{-- The rest of the Events settings, unchanged, so the group validates. --}}
                        @foreach(collect(config('settings.definitions'))->where('group', 'events')->except('event_page_layout') as $settingKey => $definition)
                            <input type="hidden" name="{{ $settingKey }}" value="{{ setting($settingKey) }}">
                        @endforeach
                        <button type="submit" class="rounded-full bg-accent-500 px-3 py-1.5 font-semibold text-white hover:bg-accent-600">Use this layout</button>
                    </form>
                @endif
                <a href="{{ url()->current() }}" class="rounded-full px-3 py-1.5 font-semibold text-gray-600 hover:bg-gray-100">Exit preview</a>
            </div>
        </div>
    </div>
@endif
