<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    protected $fillable = [
        'author_id',
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
    ];

    protected $casts = [
        'builder_data' => 'array',
        'is_homepage' => 'boolean',
        'is_blog_page' => 'boolean',
        'show_header' => 'boolean',
        'show_footer' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
