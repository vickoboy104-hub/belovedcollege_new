<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setMany(array $settings, string $group = 'school'): void
    {
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
    }
}
