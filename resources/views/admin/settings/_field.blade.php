@php
    $hasError = $errors->has($key);

    $inputClasses = 'block w-full rounded-xl border-2 bg-gray-50 px-4 py-3 text-sm text-gray-900 shadow-sm transition-colors duration-150 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-4 '
        .($hasError
            ? 'border-red-300 focus:border-red-500 focus:ring-red-100'
            : 'border-gray-200 hover:border-gray-300 focus:border-emerald-500 focus:ring-emerald-100');

    $displayValue = old($key, $definition['type'] === 'json' && ! is_string($value)
        ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : $value);

    $isWide = in_array($definition['input'], ['textarea', 'json'], true);
@endphp

<div class="{{ $isWide ? 'md:col-span-2' : '' }}">
    <label for="{{ $key }}" class="mb-1.5 block text-sm font-semibold text-gray-800">
        {{ $definition['label'] }}
        @if (in_array('required', $definition['rules'], true))
            <span class="text-red-400" title="Required">*</span>
        @endif
    </label>

    @switch($definition['input'])
        @case('toggle')
            <label for="{{ $key }}" class="flex w-full cursor-pointer items-center justify-between gap-4 rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 transition-colors duration-150 hover:border-gray-300">
                <span class="text-sm text-gray-600" x-text="on_{{ $key }} ? 'Enabled' : 'Disabled'"></span>
                <input type="hidden" name="{{ $key }}" value="0">
                <input
                    type="checkbox"
                    id="{{ $key }}"
                    name="{{ $key }}"
                    value="1"
                    x-model="on_{{ $key }}"
                    class="peer sr-only"
                >
                <span class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-emerald-500 peer-focus-visible:ring-4 peer-focus-visible:ring-emerald-100">
                    <span class="absolute left-0.5 inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200" :class="on_{{ $key }} ? 'translate-x-5' : 'translate-x-0'"></span>
                </span>
            </label>
            @break

        @case('textarea')
            <textarea
                id="{{ $key }}"
                name="{{ $key }}"
                rows="3"
                class="{{ $inputClasses }} leading-relaxed"
            >{{ $displayValue }}</textarea>
            @break

        @case('json')
            <textarea
                id="{{ $key }}"
                name="{{ $key }}"
                rows="12"
                spellcheck="false"
                class="{{ $inputClasses }} font-mono !text-xs leading-relaxed"
            >{{ $displayValue }}</textarea>
            @break

        @case('number')
            <input
                type="number"
                id="{{ $key }}"
                name="{{ $key }}"
                value="{{ $displayValue }}"
                step="{{ $definition['type'] === 'int' ? '1' : 'any' }}"
                class="{{ $inputClasses }}"
            >
            @break

        @case('time')
            <input
                type="time"
                id="{{ $key }}"
                name="{{ $key }}"
                value="{{ $displayValue }}"
                class="{{ $inputClasses }}"
            >
            @break

        @case('url')
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.657-5.657l1.5-1.5m8.486-2.828l1.5-1.5a4 4 0 10-5.657-5.657l-3 3a4 4 0 000 5.657"/></svg>
                </span>
                <input
                    type="url"
                    id="{{ $key }}"
                    name="{{ $key }}"
                    value="{{ $displayValue }}"
                    placeholder="https://"
                    class="{{ $inputClasses }} pl-10"
                >
            </div>
            @break

        @case('email')
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input
                    type="email"
                    id="{{ $key }}"
                    name="{{ $key }}"
                    value="{{ $displayValue }}"
                    placeholder="name@example.com"
                    class="{{ $inputClasses }} pl-10"
                >
            </div>
            @break

        @default
            <input
                type="text"
                id="{{ $key }}"
                name="{{ $key }}"
                value="{{ $displayValue }}"
                class="{{ $inputClasses }}"
            >
    @endswitch

    @if (! empty($definition['hint']))
        <p class="mt-1.5 text-xs leading-relaxed text-gray-500">{{ $definition['hint'] }}</p>
    @endif

    @error($key)
        <p class="mt-1.5 flex items-center gap-1 text-sm font-medium text-red-600">
            <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>
