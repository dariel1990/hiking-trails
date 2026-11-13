<?php

namespace App\Http\Requests\Admin;

use App\Models\Tour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'town_id' => ['nullable', 'integer', 'exists:towns,id'],
            'title' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tour_type' => ['required', 'string', Rule::in(array_keys(Tour::getTourTypes()))],
            'icon' => ['nullable', 'string', 'max:10'],
            'icon_image' => ['nullable', 'string', 'max:500'],
            'difficulty_summary' => ['nullable', 'string', 'max:255'],
            'duration_estimate' => ['nullable', 'string', 'max:100'],
            'total_driving_km' => ['nullable', 'numeric', 'min:0'],
            'driving_route_coordinates' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover_image' => ['nullable', 'image', 'max:51200'],
            'video_url' => ['nullable', 'url', 'max:500', 'regex:#^https?://(www\.)?(youtube\.com|youtu\.be|m\.youtube\.com|vimeo\.com)/#i'],
            'stops' => ['nullable', 'array'],
            ...self::stopRules(),
        ];
    }

    /**
     * Rules for the tour's stops, shared by the create and update requests.
     * A stop links a trail/facility, is pinned on the map, or is a roaming
     * activity with no fixed spot; pinned and roaming stops need a name.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function stopRules(): array
    {
        return [
            'stops.*' => [function ($attribute, $value, $fail) {
                $isLinked = ! empty($value['trail_id']) || ! empty($value['facility_id']);
                $isPinned = isset($value['latitude'], $value['longitude']) && $value['latitude'] !== '' && $value['longitude'] !== '';
                $isRoaming = filter_var($value['is_roaming'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if (! $isLinked && ! $isPinned && ! $isRoaming) {
                    $fail('Each stop must reference a trail or a facility, be pinned on the map, or be a roaming activity.');
                }
                if (! $isLinked && ($isPinned || $isRoaming) && empty($value['stop_label'])) {
                    $fail('Pinned and roaming stops need a name.');
                }
            }],
            'stops.*.trail_id' => ['nullable', 'integer', 'exists:trails,id'],
            'stops.*.feature_id' => ['nullable', 'integer', 'exists:trail_features,id'],
            'stops.*.facility_id' => ['nullable', 'integer', 'exists:facilities,id'],
            'stops.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'stops.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'stops.*.is_roaming' => ['nullable', 'boolean'],
            'stops.*.stop_label' => ['nullable', 'string', 'max:255'],
            'stops.*.icon' => ['nullable', 'string', 'max:16'],
            'stops.*.icon_image' => ['nullable', 'string', 'max:500', 'starts_with:feature-icons/', 'not_regex:/\.\./'],
            'stops.*.description' => ['nullable', 'string', 'max:2000'],
            'stops.*.image' => ['nullable', 'image', 'max:10240'],
            'stops.*.existing_image' => ['nullable', 'string', 'max:500'],
            'stops.*.remove_image' => ['nullable', 'boolean'],
            'stops.*.driving_notes' => ['nullable', 'string', 'max:500'],
            'stops.*.estimated_visit_time' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'video_url.regex' => 'The video link must be a YouTube or Vimeo URL.',
        ];
    }
}
