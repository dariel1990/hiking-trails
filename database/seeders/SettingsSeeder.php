<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed registry defaults without overwriting existing admin-edited values.
     */
    public function run(): void
    {
        foreach (config('settings.definitions') as $key => $definition) {
            if ($definition['default'] === null) {
                continue;
            }

            Setting::query()->firstOrCreate(['key' => $key], [
                'value' => Setting::serializeValue($definition['default'], $definition['type']),
                'type' => $definition['type'],
                'group' => $definition['group'],
            ]);
        }
    }
}
