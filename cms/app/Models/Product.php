<?php

namespace App\Models;

use App\Domain\Commerce\ProductState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'short_description', 'description', 'state', 'featured_media_id'];

    protected function casts(): array
    {
        return ['state' => ProductState::class];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function gallery(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_media')->withPivot('position')->orderByPivot('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopePubliclyVisible(Builder $q): Builder
    {
        return $q->where('state', ProductState::Active->value)->whereHas('variants', fn ($v) => $v->where('is_active', true));
    }
}
