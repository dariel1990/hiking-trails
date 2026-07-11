<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Build rules dynamically from the settings registry for the submitted group.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = ['group' => ['required', 'string', 'in:'.implode(',', array_keys(config('settings.groups')))]];

        foreach ($this->groupDefinitions() as $key => $definition) {
            $rules[$key] = $definition['rules'];

            if ($key === 'regional_pricing') {
                $rules[$key][] = $this->regionalPricingShapeRule();
            }
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $toggles = collect($this->groupDefinitions())
            ->filter(fn (array $definition): bool => $definition['type'] === 'bool')
            ->keys();

        foreach ($toggles as $key) {
            $this->merge([$key => $this->boolean($key)]);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function groupDefinitions(): array
    {
        return collect(config('settings.definitions'))
            ->where('group', $this->input('group'))
            ->all();
    }

    protected function regionalPricingShapeRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $pricing = json_decode((string) $value, true);

            if (! is_array($pricing) || $pricing === []) {
                $fail('The regional pricing must be a JSON object keyed by country code.');

                return;
            }

            foreach ($pricing as $country => $entry) {
                if (! is_string($country) || strlen($country) !== 2) {
                    $fail("Regional pricing keys must be two-letter country codes (found \"{$country}\").");

                    return;
                }

                foreach (['currency', 'symbol', 'monthly', 'annual'] as $field) {
                    if (! isset($entry[$field]) || ! is_string($entry[$field]) || $entry[$field] === '') {
                        $fail("Regional pricing for {$country} is missing a non-empty \"{$field}\" value.");

                        return;
                    }
                }
            }
        };
    }
}
