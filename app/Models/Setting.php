<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    public const CACHE_KEY = 'beloved.settings.all';

    protected static ?array $resolvedSettings = null;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    public static function allCached(): array
    {
        if (static::$resolvedSettings !== null) {
            return static::$resolvedSettings;
        }

        return static::$resolvedSettings = Cache::rememberForever(
            static::CACHE_KEY,
            fn (): array => static::query()->pluck('value', 'key')->all(),
        );
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::allCached()[$key] ?? $default;
    }

    public static function setMany(array $settings, string $group = 'school'): void
    {
        DB::transaction(function () use ($settings, $group): void {
            foreach ($settings as $key => $value) {
                static::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'group' => $group,
                        'value' => is_array($value) ? json_encode($value) : (string) $value,
                        'type' => is_array($value) ? 'json' : 'string',
                    ],
                );
            }
        });

        static::$resolvedSettings = null;
        Cache::forget(static::CACHE_KEY);
    }
}
