<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The mobile apps send a single `name` field, so split it when the
     * separate fields are missing.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('first_name') || ! $this->filled('name')) {
            return;
        }

        $nameParts = preg_split('/\s+/', trim($this->string('name')->value()), 2);

        $this->merge([
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'accepted_versions' => ['sometimes', 'array', 'min:1'],
            'accepted_versions.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'An account with this email already exists.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
