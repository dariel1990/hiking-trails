{{--
    Date & Time card: one date range with the same hours, or a list of days each with
    its own hours. Used by the local-events form and the carousel slide calendar form.

    @param  array{event_date: string, end_date: string, event_time: string, end_time: string}  $scheduleValues
    @param  \Illuminate\Support\Collection  $scheduleExistingDays  saved days (date, start_time, end_time, note)
    @param  string  $inputClass
    @param  string|null  $scheduleCardId
    @param  string|null  $scheduleHint
--}}
        @php
            $scheduleMode = old('schedule_mode', $scheduleExistingDays->isNotEmpty() ? 'days' : 'single');
            $scheduleDays = is_array(old('days'))
                ? collect(old('days'))->map(fn ($d) => [
                    'date' => $d['date'] ?? '',
                    'start_time' => $d['start_time'] ?? '',
                    'end_time' => $d['end_time'] ?? '',
                    'note' => $d['note'] ?? '',
                ])->values()
                : $scheduleExistingDays->map(fn ($d) => [
                    'date' => $d->date->toDateString(),
                    'start_time' => $d->start_time?->format('H:i') ?? '',
                    'end_time' => $d->end_time?->format('H:i') ?? '',
                    'note' => $d->note ?? '',
                ])->values();
        @endphp
        <div id="{{ $scheduleCardId ?? 'event-when' }}" class="scroll-mt-56 xl:scroll-mt-40 rounded-lg border bg-card text-card-foreground shadow-sm"
            x-data="eventSchedule(@js($scheduleDays), @js($scheduleMode))">
            <div class="px-5 py-4 border-b space-y-1.5">
                <h3 class="text-base font-semibold leading-none tracking-tight">Date &amp; Time</h3>
                <p class="text-sm text-muted-foreground">{{ $scheduleHint ?? 'The event drops off the Events page after its last day.' }}</p>
            </div>
            <div class="p-5 space-y-4">
                <input type="hidden" name="schedule_mode" :value="mode" value="{{ $scheduleMode }}">

                {{-- Mode switch --}}
                <div class="grid grid-cols-1 gap-1 rounded-lg bg-muted p-1 text-sm font-medium sm:inline-grid sm:grid-cols-2" role="radiogroup" aria-label="Schedule type">
                    <button type="button" role="radio" :aria-checked="mode === 'single'" @click="switchTo('single')"
                        :class="mode === 'single' ? 'bg-white text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="rounded-md px-3 py-1.5 transition-colors">Same hours every day</button>
                    <button type="button" role="radio" :aria-checked="mode === 'days'" @click="switchTo('days')"
                        :class="mode === 'days' ? 'bg-white text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="rounded-md px-3 py-1.5 transition-colors">Different dates or hours</button>
                </div>

                {{-- Same hours every day --}}
                <div x-show="mode === 'single'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="event_date" class="text-sm font-medium leading-none">Start date <span class="text-red-500">*</span></label>
                        <input type="date" name="event_date" id="event_date" :required="mode === 'single'" :disabled="mode !== 'single'"
                            value="{{ old('event_date', $scheduleValues['event_date']) }}"
                            class="{{ $inputClass }}">
                        @error('event_date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="end_date" class="text-sm font-medium leading-none">End date <span class="text-muted-foreground font-normal">(multi-day events)</span></label>
                        <input type="date" name="end_date" id="end_date" :disabled="mode !== 'single'"
                            value="{{ old('end_date', $scheduleValues['end_date']) }}"
                            class="{{ $inputClass }}">
                        @error('end_date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="event_time" class="text-sm font-medium leading-none">Start time</label>
                        <input type="time" name="event_time" id="event_time" :disabled="mode !== 'single'"
                            value="{{ old('event_time', $scheduleValues['event_time']) }}"
                            class="{{ $inputClass }}">
                        @error('event_time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label for="end_time" class="text-sm font-medium leading-none">End time</label>
                        <input type="time" name="end_time" id="end_time" :disabled="mode !== 'single'"
                            value="{{ old('end_time', $scheduleValues['end_time']) }}"
                            class="{{ $inputClass }}">
                        @error('end_time') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Different dates or hours --}}
                <template x-if="mode === 'days'">
                    <div class="space-y-3">
                        <div class="hidden grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.6fr)_2rem] gap-2 px-1 text-xs font-medium text-muted-foreground sm:grid">
                            <span>Date</span><span>Opens</span><span>Closes</span><span>Note (optional)</span><span></span>
                        </div>

                        <template x-for="(day, index) in days" :key="day.uid">
                            <div class="grid grid-cols-2 gap-2 rounded-md border bg-muted/30 p-2 sm:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.6fr)_2rem] sm:items-center sm:border-0 sm:bg-transparent sm:p-0">
                                <input type="date" required :name="`days[${index}][date]`" x-model="day.date" aria-label="Date"
                                    class="{{ $inputClass }} col-span-2 sm:col-span-1">
                                <input type="time" :name="`days[${index}][start_time]`" x-model="day.start_time" aria-label="Opens"
                                    class="{{ $inputClass }}">
                                <input type="time" :name="`days[${index}][end_time]`" x-model="day.end_time" aria-label="Closes"
                                    class="{{ $inputClass }}">
                                <input type="text" maxlength="100" :name="`days[${index}][note]`" x-model="day.note" aria-label="Note"
                                    placeholder="e.g. Craft fair only"
                                    class="{{ $inputClass }} col-span-2 sm:col-span-1">
                                <button type="button" @click="removeDay(index)" :disabled="days.length === 1" title="Remove day"
                                    class="col-span-2 inline-flex h-10 items-center justify-center rounded-md border border-red-200 bg-red-50 text-xs text-red-600 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-30 sm:col-span-1 sm:w-8">
                                    <span class="sm:hidden">Remove day</span><span class="hidden sm:inline">✕</span>
                                </button>
                            </div>
                        </template>

                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <button type="button" @click="addDay()"
                                class="inline-flex h-9 items-center gap-1.5 rounded-md border border-dashed border-input bg-background px-3 text-sm font-medium hover:bg-accent transition-colors">
                                + Add day
                            </button>
                            <span class="text-xs text-muted-foreground" x-text="summary"></span>
                        </div>
                        <p class="text-xs text-muted-foreground">Leave the times empty for an all-day date. "+ Add day" copies the last day's hours to the next date.</p>
                    </div>
                </template>

                @error('days') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                @foreach(collect($errors->get('days.*'))->flatten()->unique() as $message)
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @endforeach
            </div>
        </div>
        @once
        <script>
            function eventSchedule(initialDays, mode) {
                let nextUid = 0;
                const makeDay = (day = {}) => ({ uid: ++nextUid, date: '', start_time: '', end_time: '', note: '', ...day });
                const parse = (value) => {
                    const [y, m, d] = String(value).split('-').map(Number);
                    return y ? new Date(y, m - 1, d) : null;
                };
                const iso = (date) => [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
                const field = (id) => document.getElementById(id)?.value || '';

                return {
                    mode,
                    days: initialDays.map((day) => makeDay(day)),

                    /** Switching to the day list starts it from the single-range fields so nothing is lost. */
                    switchTo(newMode) {
                        this.mode = newMode;
                        if (newMode === 'days' && this.days.length === 0) {
                            this.days.push(makeDay({ date: field('event_date'), start_time: field('event_time'), end_time: field('end_time') }));
                        }
                    },

                    addDay() {
                        const last = this.days[this.days.length - 1];
                        const lastDate = last ? parse(last.date) : null;
                        let date = '';
                        if (lastDate) {
                            lastDate.setDate(lastDate.getDate() + 1);
                            date = iso(lastDate);
                        }
                        this.days.push(makeDay({ date, start_time: last?.start_time || '', end_time: last?.end_time || '' }));
                    },

                    removeDay(index) {
                        if (this.days.length > 1) {
                            this.days.splice(index, 1);
                        }
                    },

                    get summary() {
                        const dates = this.days.map((day) => parse(day.date)).filter(Boolean).sort((a, b) => a - b);
                        if (dates.length === 0) {
                            return '';
                        }
                        const label = (date) => date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                        const count = `${this.days.length} day${this.days.length === 1 ? '' : 's'}`;
                        return dates.length === 1
                            ? `${count} · ${label(dates[0])}`
                            : `${count} · ${label(dates[0])} – ${label(dates[dates.length - 1])}`;
                    },
                };
            }
        </script>
        @endonce

