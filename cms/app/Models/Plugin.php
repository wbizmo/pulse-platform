<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'category',
        'icon',
        'is_active',
        'has_settings',
        'provides',
        'requires',
        'permissions',
        'settings_schema',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_settings' => 'boolean',
        'provides' => 'array',
        'requires' => 'array',
        'permissions' => 'array',
        'settings_schema' => 'array',
        'settings' => 'array',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(PluginSetting::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isInactive(): bool
    {
        return ! $this->is_active;
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }
}
