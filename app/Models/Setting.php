<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('gamehub_settings', 3600, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', ?string $label = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value,
                'group' => $group,
                'label' => $label ?? $key,
            ]
        );

        Cache::forget('gamehub_settings');

        return $setting;
    }
}

