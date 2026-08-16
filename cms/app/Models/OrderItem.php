<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'product_name', 'sku', 'options_snapshot', 'unit_price_minor', 'currency', 'quantity', 'line_subtotal_minor', 'line_discount_minor', 'line_tax_minor'];

    protected function casts(): array
    {
        return ['options_snapshot' => 'array', 'unit_price_minor' => 'integer', 'quantity' => 'integer', 'line_subtotal_minor' => 'integer', 'line_discount_minor' => 'integer', 'line_tax_minor' => 'integer'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
