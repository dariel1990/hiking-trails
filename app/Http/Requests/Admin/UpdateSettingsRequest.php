<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public const MAX_SOCIAL_LINKS = 12;

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

            if ($key === 'social_links') {
                $rules[$key][] = $this->socialLinksShapeRule();
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

    /**
     * Each row must be {icon, label, url} with an icon from the shipped library.
     */
    protected function socialLinksShapeRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $links = json_decode((string) $value, true);

            if (! is_array($links)) {
                $fail('The social links must be a JSON array.');

                return;
            }

            if (count($links) > self::MAX_SOCIAL_LINKS) {
                $fail('You can add at most '.self::MAX_SOCIAL_LINKS.' social links.');

                return;
            }

            $icons = array_keys(config('social-icons'));

            foreach (array_values($links) as $index => $link) {
                $position = $index + 1;

                if (! is_array($link)) {
                    $fail("Social link #{$position} is malformed.");

                    return;
                }

                if (! in_array($link['icon'] ?? null, $icons, true)) {
                    $fail("Social link #{$position} must use an icon from the library.");

                    return;
                }

                $label = $link['label'] ?? '';

                if (! is_string($label) || trim($label) === '' || mb_strlen($label) > 40) {
                    $fail("Social link #{$position} needs a label of 1 to 40 characters.");

                    return;
                }

                $url = $link['url'] ?? '';

                if (! is_string($url) || mb_strlen($url) > 500 || filter_var($url, FILTER_VALIDATE_URL) === false) {
                    $fail("Social link #{$position} (\"{$label}\") needs a valid URL of up to 500 characters.");

                    return;
                }
            }
        };
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
