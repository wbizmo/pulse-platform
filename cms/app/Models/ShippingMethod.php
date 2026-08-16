<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    protected $fillable = ['shipping_zone_id', 'name', 'is_active', 'amount_minor', 'currency', 'free_shipping_threshold_minor', 'position'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'amount_minor' => 'integer', 'free_shipping_threshold_minor' => 'integer', 'position' => 'integer'];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
