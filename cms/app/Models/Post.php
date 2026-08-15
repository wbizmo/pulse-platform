<?php

namespace App\Models;

use App\Domain\Content\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'featured_media_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'og_image',
        'lock_version',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => ContentStatus::class,
    ];

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
