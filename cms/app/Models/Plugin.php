<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'author',
        'description',
        'category',
        'is_active',
        'has_settings',
        'requires',
        'provides',
        'health_checks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_settings' => 'boolean',
        'requires' => 'array',
        'provides' => 'array',
        'health_checks' => 'array',
    ];
}
