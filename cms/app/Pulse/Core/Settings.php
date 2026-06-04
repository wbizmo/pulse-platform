<?php

namespace App\Pulse\Core;

use App\Models\Setting;

class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true) ?? $default,
            'integer' => (int) $setting->value,
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string', bool $isPublic = false): void
    {
        if ($type === 'json') {
            $value = json_encode($value);
        }

        if ($type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $value,
                'type' => $type,
                'is_public' => $isPublic,
            ]
        );
    }
}
