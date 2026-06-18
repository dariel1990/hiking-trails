<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $currentPasswordRule = $this->filled('password') && filled($this->user()->password)
            ? ['required', 'current_password']
            : ['nullable', 'current_password'];

        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'current_password' => $currentPasswordRule,
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Re-fill "current_password" on a failed save — only the new/confirm password
     * fields stay blanked out, since those don't need to survive a redisplay.
     */
    protected function failedValidation(Validator $validator): void
    {
        $exception = new ValidationException($validator);

        $exception->response = redirect($this->getRedirectUrl())
            ->withInput(Arr::except($this->input(), ['password', 'password_confirmation']))
            ->withErrors($validator->errors(), $this->errorBag);

        throw $exception;
    }
}
