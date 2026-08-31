{{--
    Town assignment dropdown, shared by the trail, business, facility and tour
    admin forms.

    Expects: $towns (collection). Optional: $selected (current town_id),
    $selectClass and $labelClass to match the surrounding form's styling.
--}}
@php
    $selectClass = $selectClass ?? 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring';
    $labelClass = $labelClass ?? 'text-sm font-medium leading-none';
@endphp
<div class="space-y-2">
    <label for="town_id" class="{{ $labelClass }}">Town <span class="text-gray-400">(Optional)</span></label>
    <select name="town_id" id="town_id" class="{{ $selectClass }} @error('town_id') border-red-300 @enderror">
        <option value="">&mdash; Unassigned &mdash;</option>
        @foreach($towns as $town)
            <option value="{{ $town->id }}" @selected(old('town_id', $selected ?? null) == $town->id)>
                {{ $town->name }}, {{ $town->province_code }}
            </option>
        @endforeach
    </select>
    <p class="text-xs text-gray-500">
        Decides which town landing page this appears on. Leave unassigned to let
        <code>php artisan towns:assign</code> pick the nearest town by coordinates.
    </p>
    @error('town_id')
        <p class="text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>
