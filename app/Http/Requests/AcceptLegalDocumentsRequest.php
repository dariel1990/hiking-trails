<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcceptLegalDocumentsRequest extends FormRequest
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
            'versions' => ['required', 'array', 'min:1'],
            'versions.*' => ['required', 'integer'],
            'accepted' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'accepted.accepted' => 'Please confirm that you have read and agree to the updated documents.',
        ];
    }
}
