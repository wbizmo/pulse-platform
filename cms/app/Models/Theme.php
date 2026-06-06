<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'author',
        'description',
        'category',
        'screenshot',
        'supports',
        'default_pages',
        'default_settings',
        'is_active',
    ];

    protected $casts = [
        'supports' => 'array',
        'default_pages' => 'array',
        'default_settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(ThemeSetting::class);
    }
}
