<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'in:'.implode(',', array_keys(\App\Models\Business::getBusinessTypes()))],
            'description' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'opening_hours' => ['nullable', 'array'],
            'price_range' => ['nullable', 'in:free,$,$$,$$$'],
            'is_seasonal' => ['boolean'],
            'season_open' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:10'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:10240'],
            'photo_captions' => ['nullable', 'array'],
            'video_urls' => ['nullable', 'array'],
            'video_urls.*' => ['nullable', 'url'],
        ];
    }
}
