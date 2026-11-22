<?php

namespace App\Http\Requests\Admin;

use App\Models\CarouselSlide;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A carousel slide's /events calendar entry. The Date & Time fields use the same names
 * as the local-events form (shared admin.partials.schedule-days card) and are only
 * checked when the slide is shown on the calendar.
 */
class UpdateCarouselSlideCalendarRequest extends FormRequest
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
        $whenSingle = ['exclude_unless:show_on_calendar,1', 'exclude_if:schedule_mode,days'];
        $whenDays = ['exclude_unless:show_on_calendar,1', 'exclude_unless:schedule_mode,days'];

        return [
            'show_on_calendar' => ['boolean'],
            'calendar_title' => ['exclude_unless:show_on_calendar,1', 'nullable', 'string', 'max:255'],
            'calendar_category' => ['exclude_unless:show_on_calendar,1', 'nullable', 'string', 'max:100'],
            'calendar_location' => ['exclude_unless:show_on_calendar,1', 'nullable', 'string', 'max:255'],
            'calendar_description' => ['exclude_unless:show_on_calendar,1', 'nullable', 'string', 'max:5000'],
            'calendar_url' => ['exclude_unless:show_on_calendar,1', 'nullable', 'url', 'max:500'],
            'schedule_mode' => ['exclude_unless:show_on_calendar,1', 'nullable', Rule::in(['single', 'days'])],
            'event_date' => [...$whenSingle, 'required', 'date'],
            'end_date' => [...$whenSingle, 'nullable', 'date', 'after_or_equal:event_date'],
            'event_time' => [...$whenSingle, 'nullable', 'date_format:H:i'],
            'end_time' => [...$whenSingle, 'nullable', 'date_format:H:i'],
            'days' => [...$whenDays, 'required', 'array', 'min:1', 'max:60'],
            'days.*.date' => [...$whenDays, 'required', 'date', 'distinct'],
            'days.*.start_time' => [...$whenDays, 'nullable', 'date_format:H:i'],
            'days.*.end_time' => [...$whenDays, 'nullable', 'date_format:H:i'],
            'days.*.note' => [...$whenDays, 'nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_date.required' => 'Enter the date the event starts.',
            'end_date.after_or_equal' => 'The end date cannot be before the start date.',
            'event_time.date_format' => 'Enter the start time as HH:MM.',
            'end_time.date_format' => 'Enter the end time as HH:MM.',
            'days.required' => 'Add at least one day to the schedule.',
            'days.min' => 'Add at least one day to the schedule.',
            'days.max' => 'A schedule can have at most 60 days.',
            'days.*.date.required' => 'Each day in the schedule needs a date.',
            'days.*.date.date' => 'Each day in the schedule needs a valid date.',
            'days.*.date.distinct' => 'Each date can only be in the schedule once.',
            'days.*.start_time.date_format' => 'Enter each day\'s opening time as HH:MM.',
            'days.*.end_time.date_format' => 'Enter each day\'s closing time as HH:MM.',
            'calendar_url.url' => 'The "More info" link must be a full web address (https://…).',
        ];
    }

    /**
     * The title only when the admin changed it: blank or the same as the caption saves
     * null, so the calendar keeps following the caption.
     */
    private function customTitle(): ?string
    {
        $title = trim((string) $this->validated('calendar_title'));
        $caption = trim((string) $this->route('carousel')?->caption);

        return $title === '' || $title === $caption ? null : $title;
    }

    public function showsOnCalendar(): bool
    {
        return $this->boolean('show_on_calendar');
    }

    public function usesDaySchedule(): bool
    {
        return $this->showsOnCalendar() && $this->validated('schedule_mode') === 'days';
    }

    /**
     * The submitted schedule days, sorted by date, with blank times as null.
     *
     * @return list<array{date: string, start_time: ?string, end_time: ?string, note: ?string}>
     */
    public function scheduleDays(): array
    {
        return collect($this->validated('days', []))
            ->map(fn (array $day) => [
                'date' => Carbon::parse($day['date'])->toDateString(),
                'start_time' => filled($day['start_time'] ?? null) ? $day['start_time'] : null,
                'end_time' => filled($day['end_time'] ?? null) ? $day['end_time'] : null,
                'note' => filled($day['note'] ?? null) ? trim($day['note']) : null,
            ])
            ->sortBy([['date', 'asc'], ['start_time', 'asc']])
            ->values()
            ->all();
    }

    /**
     * Validated fields mapped to carousel_slides columns. Turning the calendar off keeps
     * the saved details, so switching it back on later restores them.
     *
     * @return array<string, mixed>
     */
    public function calendarAttributes(): array
    {
        if (! $this->showsOnCalendar()) {
            return ['show_on_calendar' => false];
        }

        $attributes = [
            'show_on_calendar' => true,
            'calendar_title' => $this->customTitle(),
            'calendar_category' => filled($this->validated('calendar_category')) ? trim($this->validated('calendar_category')) : null,
            'calendar_location' => $this->validated('calendar_location'),
            'calendar_description' => $this->validated('calendar_description'),
            'calendar_url' => $this->validated('calendar_url'),
        ];

        if ($this->usesDaySchedule()) {
            $summary = CarouselSlide::scheduleSummaryFromDays($this->scheduleDays());

            return $attributes + [
                'calendar_date' => $summary['start_date'],
                'calendar_end_date' => $summary['end_date'],
                'calendar_start_time' => $summary['start_time'],
                'calendar_end_time' => $summary['end_time'],
            ];
        }

        return $attributes + [
            'calendar_date' => $this->validated('event_date'),
            'calendar_end_date' => $this->validated('end_date') ?: null,
            'calendar_start_time' => $this->validated('event_time') ?: null,
            'calendar_end_time' => $this->validated('end_time') ?: null,
        ];
    }
}
