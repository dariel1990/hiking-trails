<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Event;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $groups = config('settings.groups');
        $definitions = collect(config('settings.definitions'))->groupBy('group', preserveKeys: true);

        $values = collect(config('settings.definitions'))
            ->mapWithKeys(fn (array $definition, string $key): array => [$key => Setting::get($key)]);

        $activeTab = $request->query('tab');
        if (! array_key_exists($activeTab ?? '', $groups)) {
            $activeTab = array_key_first($groups);
        }

        return view('admin.settings.edit', [
            'groups' => $groups,
            'definitionsByGroup' => $definitions,
            'values' => $values,
            'activeTab' => $activeTab,
            'previewUrls' => ['event_page_layout' => $this->eventPreviewUrl()],
        ]);
    }

    /**
     * Page of the next upcoming event, for previewing event page layouts.
     */
    private function eventPreviewUrl(): ?string
    {
        $event = Event::current()->orderBy('event_date')->orderBy('event_time')->first();

        return $event ? route('local-events.show', $event->slug) : null;
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $group = $validated['group'];

        foreach ($request->groupDefinitions() as $key => $definition) {
            Setting::set($key, $validated[$key] ?? null);
        }

        return redirect()
            ->route('admin.settings.edit', ['tab' => $group])
            ->with('success', config("settings.groups.{$group}.label").' settings saved.');
    }
}
