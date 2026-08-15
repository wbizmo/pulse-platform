<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'success_message', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order')->orderBy('id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
