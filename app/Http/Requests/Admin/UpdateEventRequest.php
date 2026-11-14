<?php

namespace App\Http\Requests\Admin;

class UpdateEventRequest extends StoreEventRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return self::eventRules($this->route('event')?->id);
    }
}
