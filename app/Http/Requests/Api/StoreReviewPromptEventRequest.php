<?php

namespace App\Http\Requests\Api;

use App\Models\ReviewPromptEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewPromptEventRequest extends FormRequest
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
        return [
            'channel' => ['required', 'string', Rule::in(ReviewPromptEvent::CHANNELS)],
            'action' => ['required', 'string', Rule::in(ReviewPromptEvent::ACTIONS)],
            'trigger' => ['nullable', 'string', 'max:40'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ];
    }
}
