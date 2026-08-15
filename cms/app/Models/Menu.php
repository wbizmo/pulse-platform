<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'location',
        'is_active',
        'active_singleton_location',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public static function publicAt(string $location): ?self
    {
        return static::query()->where('location', $location)->where('is_active', true)->orderBy('id')
            ->with(['items' => fn ($query) => $query->where('is_active', true)->where(function ($items): void {
                $items->where('type', 'custom')->orWhereHas('page', fn ($pages) => $pages->publiclyVisible());
            })->with('page')])->first();
    }
}
