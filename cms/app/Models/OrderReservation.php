<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReservation extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'order_item_id', 'inventory_reservation_id'];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(InventoryReservation::class, 'inventory_reservation_id');
    }
}
