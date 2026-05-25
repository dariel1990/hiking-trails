<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrailPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'trail_id' => ['required', 'integer', 'exists:trails,id'],
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'caption' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'g-recaptcha-response' => ['required', 'string'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Please choose a photo to upload.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Only JPG, PNG, and WebP images are accepted.',
            'image.max' => 'Photos must be 8 MB or smaller.',
            'email.required' => 'Please provide an email so we can follow up if needed.',
            'g-recaptcha-response.required' => 'Please complete the spam check before submitting.',
            'website.max' => 'Spam detected.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'g-recaptcha-response' => 'spam check',
            'website' => 'honeypot',
        ];
    }
}
