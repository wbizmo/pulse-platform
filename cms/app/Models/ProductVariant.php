<?php

namespace App\Models;

use App\Domain\Commerce\Currency;
use App\Domain\Commerce\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'normalized_sku', 'is_active', 'price_minor', 'currency', 'options', 'options_fingerprint', 'tracks_stock'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'tracks_stock' => 'boolean', 'options' => 'array', 'price_minor' => 'integer', 'on_hand' => 'integer', 'reserved' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(InventoryLedgerEntry::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }

    public function getAvailableAttribute(): int
    {
        return $this->on_hand - $this->reserved;
    }

    public function getMoneyAttribute(): Money
    {
        return new Money($this->price_minor, Currency::from($this->currency));
    }

    public function getStockStatusAttribute(): string
    {
        return ! $this->tracks_stock || $this->available > 0 ? 'In stock' : 'Out of stock';
    }
}
