<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tour_type' => ['required', 'string', 'in:waterfalls,fishing,heritage,scenic'],
            'difficulty_summary' => ['nullable', 'string', 'max:255'],
            'duration_estimate' => ['nullable', 'string', 'max:100'],
            'total_driving_km' => ['nullable', 'numeric', 'min:0'],
            'driving_route_coordinates' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover_image' => ['nullable', 'image', 'max:51200'],
            'stops' => ['nullable', 'array'],
            'stops.*.trail_id' => ['required_with:stops', 'integer', 'exists:trails,id'],
            'stops.*.stop_label' => ['nullable', 'string', 'max:255'],
            'stops.*.driving_notes' => ['nullable', 'string', 'max:500'],
            'stops.*.estimated_visit_time' => ['nullable', 'string', 'max:100'],
        ];
    }
}
