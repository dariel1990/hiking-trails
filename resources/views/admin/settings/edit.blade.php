@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@php
    $groupIcons = [
        'branding' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
        'contact' => 'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 12.632a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z',
        'map' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
        'subscriptions' => 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        'content' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'routing' => 'M13 10V3L4 14h7v7l9-11h-7z',
        'system' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
    ];
@endphp

@section('content')
<style>[x-cloak]{display:none !important;}</style>
<div class="space-y-6" x-data="{ tab: '{{ old('group', $activeTab) }}' }">

    {{-- Page header --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Configuration</p>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900" style="font-family: 'Inter', sans-serif;">Site Settings</h2>
        <p class="mt-1 text-sm text-gray-500">Global configuration for the application. Each section saves independently.</p>
    </div>

    @if (session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm"
        >
            <svg class="h-5 w-5 flex-shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
            <button type="button" @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
            <svg class="h-5 w-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span class="font-medium">Some fields need attention — please review the highlighted inputs below.</span>
        </div>
    @endif

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">

        {{-- Vertical tab navigation --}}
        <nav class="w-full flex-shrink-0 lg:w-60 lg:sticky lg:top-24" aria-label="Settings sections">
            <div class="flex gap-1 overflow-x-auto rounded-2xl border border-gray-200 bg-white p-2 shadow-sm lg:flex-col lg:overflow-visible">
                @foreach ($groups as $groupKey => $group)
                    <button
                        type="button"
                        @click="tab = '{{ $groupKey }}'"
                        :class="tab === '{{ $groupKey }}'
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                        class="group flex flex-shrink-0 items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-sm font-medium transition-colors duration-150"
                    >
                        <svg class="h-5 w-5 flex-shrink-0" :class="tab === '{{ $groupKey }}' ? 'text-emerald-100' : 'text-gray-400 group-hover:text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $groupIcons[$groupKey] ?? $groupIcons['system'] }}"/>
                        </svg>
                        <span class="whitespace-nowrap">{{ $group['label'] }}</span>
                        <span
                            class="ml-auto hidden rounded-full px-2 py-0.5 text-[11px] font-semibold lg:inline-block"
                            :class="tab === '{{ $groupKey }}' ? 'bg-emerald-500 text-emerald-50' : 'bg-gray-100 text-gray-500'"
                        >{{ $definitionsByGroup->get($groupKey, collect())->count() }}</span>
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- One panel + form per group --}}
        <div class="min-w-0 flex-1">
            @foreach ($groups as $groupKey => $group)
                @php
                    $groupDefinitions = $definitionsByGroup->get($groupKey, collect());
                    $toggleKeys = $groupDefinitions->where('type', 'bool')->keys();
                    $toggleState = $toggleKeys
                        ->map(fn ($key) => 'on_'.$key.': '.var_export((bool) old($key, $values[$key]), true))
                        ->implode(', ');
                @endphp

                <form
                    method="POST"
                    action="{{ route('admin.settings.update') }}"
                    x-show="tab === '{{ $groupKey }}'"
                    x-cloak
                    @if ($toggleState) x-data="{ {{ $toggleState }} }" @endif
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="group" value="{{ $groupKey }}">

                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        {{-- Section header --}}
                        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-5">
                            <h3 class="text-lg font-bold text-gray-900">{{ $group['label'] }}</h3>
                            @if (! empty($group['description']))
                                <p class="mt-1 text-sm text-gray-500">{{ $group['description'] }}</p>
                            @endif
                        </div>

                        {{-- Fields --}}
                        <div class="grid grid-cols-1 gap-x-6 gap-y-6 p-6 md:grid-cols-2">
                            @foreach ($groupDefinitions as $key => $definition)
                                @include('admin.settings._field', [
                                    'key' => $key,
                                    'definition' => $definition,
                                    'value' => $values[$key],
                                ])
                            @endforeach
                        </div>

                        {{-- Save bar --}}
                        <div class="flex items-center justify-between gap-4 border-t border-gray-100 bg-gray-50 px-6 py-4">
                            <p class="text-xs text-gray-400">
                                Changes apply immediately after saving.
                            </p>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Save {{ strtolower($group['label']) }}
                            </button>
                        </div>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</div>
@endsection
