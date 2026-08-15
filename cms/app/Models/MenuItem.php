<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'page_id',
        'label',
        'type',
        'url',
        'target',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function href(): string
    {
        return $this->type === 'page' ? '/'.$this->page->slug : (string) $this->url;
    }

    public function rel(): ?string
    {
        return $this->target === '_blank' ? 'noopener noreferrer' : null;
    }
}
