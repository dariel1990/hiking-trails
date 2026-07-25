<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AppleSignInRequest extends FormRequest
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
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
