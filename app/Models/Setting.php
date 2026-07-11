<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const DEVELOPER_EMAIL = 'developer_email';

    public const CACHE_KEY = 'settings.all';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * All settings from the database as key => cast value, cached forever.
     *
     * @return array<string, mixed>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): array => static::query()
                ->get(['key', 'value', 'type'])
                ->mapWithKeys(fn (self $setting): array => [
                    $setting->key => self::castValue($setting->value, $setting->type),
                ])
                ->all()
        );
    }

    /**
     * Get a setting value: database row, then explicit default, then registry default.
     *
     * A row that exists with a null value means the admin cleared the field,
     * so null is returned as-is instead of falling back to the default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $settings = static::allCached();
        } catch (\Throwable) {
            $settings = [];
        }

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default ?? config("settings.definitions.{$key}.default");
    }

    public static function set(string $key, mixed $value): void
    {
        $type = config("settings.definitions.{$key}.type", 'string');
        $group = config("settings.definitions.{$key}.group", 'system');

        static::query()->updateOrCreate(['key' => $key], [
            'value' => self::serializeValue($value, $type),
            'type' => $type,
            'group' => $group,
        ]);

        Cache::forget(self::CACHE_KEY);
    }

    public static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    public static function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE),
            default => (string) $value,
        };
    }
}
