<?php

namespace App\Models;

use App\Domain\Content\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'author_id',
        'featured_media_id',
        'title',
        'slug',
        'status',
        'template',
        'content',
        'builder_data',
        'is_homepage',
        'is_blog_page',
        'show_header',
        'show_footer',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'published_at',
        'lock_version',
    ];

    protected $casts = [
        'builder_data' => 'array',
        'is_homepage' => 'boolean',
        'is_blog_page' => 'boolean',
        'show_header' => 'boolean',
        'show_footer' => 'boolean',
        'published_at' => 'datetime',
        'status' => ContentStatus::class,
    ];

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
