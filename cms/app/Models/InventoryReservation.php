<?php

namespace App\Models;

use App\Domain\Commerce\ReservationState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['state' => ReservationState::class, 'expires_at' => 'immutable_datetime', 'finalized_at' => 'immutable_datetime', 'quantity' => 'integer'];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
