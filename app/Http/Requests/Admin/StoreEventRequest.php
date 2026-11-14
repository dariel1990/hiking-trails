<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The /events calendar and calendar links show `location`, so it falls back to the venue.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => filled($this->input('slug')) ? str($this->input('slug'))->slug()->toString() : null,
            'location' => filled($this->input('location')) ? $this->input('location') : $this->input('venue'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return self::eventRules();
    }

    /**
     * Rules shared by the create and update requests.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function eventRules(?int $ignoreId = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('events', 'slug')->ignore($ignoreId)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'event_date' => ['required', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'town_id' => ['nullable', 'integer', 'exists:towns,id'],
            'theme' => ['required', 'string', Rule::in(array_keys(Event::getThemes()))],
            'icon' => ['nullable', 'string', 'max:16'],
            'icon_image' => ['nullable', 'string', 'max:500', 'starts_with:feature-icons/', 'not_regex:/\.\./'],
            'route_coordinates' => ['nullable', 'string'],
            'route_distance_km' => ['nullable', 'numeric', 'min:0'],
            'cover_image' => ['nullable', 'image', 'max:51200'],
            'video_url' => ['nullable', 'url', 'max:500', 'regex:#^https?://(www\.)?(youtube\.com|youtu\.be|m\.youtube\.com|vimeo\.com)/#i'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'stops' => ['nullable', 'array'],
            'stops.*' => [function (string $attribute, mixed $value, \Closure $fail) {
                $isPinned = isset($value['latitude'], $value['longitude']) && $value['latitude'] !== '' && $value['longitude'] !== '';
                $isRoaming = filter_var($value['is_roaming'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if (! $isPinned && ! $isRoaming) {
                    $fail('Each activity must be pinned on the map or be a roaming activity.');
                }
                if (empty($value['stop_label'])) {
                    $fail('Each activity needs a name.');
                }
            }],
            'stops.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'stops.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'stops.*.is_roaming' => ['nullable', 'boolean'],
            'stops.*.stop_label' => ['nullable', 'string', 'max:255'],
            'stops.*.time_label' => ['nullable', 'string', 'max:100'],
            'stops.*.notes' => ['nullable', 'string', 'max:500'],
            'stops.*.icon' => ['nullable', 'string', 'max:16'],
            'stops.*.icon_image' => ['nullable', 'string', 'max:500', 'starts_with:feature-icons/', 'not_regex:/\.\./'],
            'stops.*.description' => ['nullable', 'string', 'max:2000'],
            'stops.*.image' => ['nullable', 'image', 'max:10240'],
            'stops.*.existing_image' => ['nullable', 'string', 'max:500'],
            'stops.*.remove_image' => ['nullable', 'boolean'],
            'media_sync' => ['nullable', 'boolean'],
            'media' => ['nullable', 'array', 'max:100'],
            'media.*' => [function (string $attribute, mixed $value, \Closure $fail) {
                if (! is_array($value) || (empty($value['id']) && empty($value['path']) && empty($value['video_url']))) {
                    $fail('Each gallery item must be an existing item, an uploaded photo or a video link.');
                }
            }],
            'media.*.id' => ['nullable', 'integer'],
            'media.*.path' => ['nullable', 'string', 'max:500', 'starts_with:events/tmp/photos/', 'not_regex:/\.\./'],
            'media.*.thumbnail_path' => ['nullable', 'string', 'max:500', 'starts_with:events/tmp/photos/', 'not_regex:/\.\./'],
            'media.*.video_url' => ['nullable', 'url', 'max:500', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value && ! EventMedia::parseVideo($value)) {
                    $fail('Gallery videos must be YouTube or Vimeo links.');
                }
            }],
            'media.*.caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return self::eventMessages();
    }

    /**
     * @return array<string, string>
     */
    public static function eventMessages(): array
    {
        return [
            'event_date.required' => 'Enter the date the event starts.',
            'end_date.after_or_equal' => 'The end date cannot be before the start date.',
            'event_time.date_format' => 'Enter the start time as HH:MM.',
            'end_time.date_format' => 'Enter the end time as HH:MM.',
            'slug.unique' => 'Another event already uses this web address.',
            'slug.alpha_dash' => 'The web address may only contain letters, numbers and dashes.',
            'external_url.url' => 'The "More info" link must be a full web address (https://...).',
            'video_url.regex' => 'The video link must be a YouTube or Vimeo URL.',
            'icon_image.starts_with' => 'Choose the icon from the icon gallery.',
            'stops.*.icon_image.starts_with' => 'Choose activity icons from the icon gallery.',
            'media.max' => 'A gallery can have at most 100 photos and videos.',
            'media.*.path.starts_with' => 'Upload gallery photos with the "Add photos" button.',
            'media.*.video_url.url' => 'Gallery videos must be full links (https://...).',
        ];
    }
}
