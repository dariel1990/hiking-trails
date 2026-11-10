<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PublishLegalDraftRequest extends FormRequest
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
            'change_summary' => ['required', 'string', 'max:2000'],
            'is_material' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'change_summary.required' => 'Describe what changed in this version.',
            'is_material.required' => 'Choose whether this is a material change.',
        ];
    }
}
