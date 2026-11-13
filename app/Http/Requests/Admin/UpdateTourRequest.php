<?php

namespace App\Http\Requests\Admin;

use App\Models\Tour;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTourRequest extends FormRequest
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
            ...StoreTourRequest::stopRules(),
        ];
    }

    public function messages(): array
    {
        return [
            'video_url.regex' => 'The video link must be a YouTube or Vimeo URL.',
        ];
    }
}
