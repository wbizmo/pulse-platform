<?php

namespace App\Models;

use App\Domain\Commerce\CouponType;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'normalized_code', 'type', 'value', 'currency', 'minimum_subtotal_minor', 'usage_limit', 'is_active', 'valid_from', 'valid_until'];

    protected function casts(): array
    {
        return ['type' => CouponType::class, 'value' => 'integer', 'minimum_subtotal_minor' => 'integer', 'usage_limit' => 'integer', 'reserved_count' => 'integer', 'consumed_count' => 'integer', 'is_active' => 'boolean', 'valid_from' => 'datetime', 'valid_until' => 'datetime'];
    }
}
