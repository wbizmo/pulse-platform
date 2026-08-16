<?php

namespace App\Models;

use App\Domain\Commerce\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLedgerEntry extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['movement' => StockMovement::class, 'on_hand_delta' => 'integer', 'reserved_delta' => 'integer', 'on_hand_after' => 'integer', 'reserved_after' => 'integer'];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(InventoryReservation::class);
    }
}
