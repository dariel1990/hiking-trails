<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
            'privacy' => ['accepted'],
            'accepted_versions' => ['required', 'array', 'min:1'],
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
            'terms.accepted' => 'You must agree to the Terms & Conditions to create an account.',
            'privacy.accepted' => 'You must agree to the Privacy Policy to create an account.',
            'accepted_versions.required' => 'Please review and accept the Terms & Conditions and Privacy Policy.',
        ];
    }
}
