<?php

namespace App\Models;

use App\Domain\Content\Taxonomy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'normalized_name',
        'slug',
        'description',
    ];

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            $category->normalized_name = Taxonomy::normalizeName($category->name);
        });
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
