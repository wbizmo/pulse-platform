<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'author',
        'description',
        'preview_image',
        'supports',
        'default_pages',
        'is_active',
    ];

    protected $casts = [
        'supports' => 'array',
        'default_pages' => 'array',
        'is_active' => 'boolean',
    ];
}
