<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'author',
        'description',
        'category',
        'icon',
        'is_active',
        'has_settings',
        'requires',
        'provides',
        'permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_settings' => 'boolean',
        'requires' => 'array',
        'provides' => 'array',
        'permissions' => 'array',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(PluginSetting::class);
    }
}
