<?php

namespace App\Models;

use App\Domain\Commerce\OrderState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = ['public_reference', 'access_token_hash', 'idempotency_hash', 'request_fingerprint', 'state', 'currency', 'subtotal_minor', 'discount_minor', 'tax_minor', 'shipping_minor', 'total_minor', 'customer_email', 'customer_email_hash', 'shipping_address', 'billing_address', 'coupon_snapshot', 'shipping_snapshot', 'tax_snapshot', 'expires_at'];

    protected $hidden = ['access_token_hash', 'idempotency_hash', 'request_fingerprint', 'customer_email_hash'];

    protected function casts(): array
    {
        return ['state' => OrderState::class, 'shipping_address' => 'array', 'billing_address' => 'array', 'coupon_snapshot' => 'array', 'shipping_snapshot' => 'array', 'tax_snapshot' => 'array', 'expires_at' => 'datetime', 'subtotal_minor' => 'integer', 'discount_minor' => 'integer', 'tax_minor' => 'integer', 'shipping_minor' => 'integer', 'total_minor' => 'integer'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStateHistory::class);
    }

    public function reservationLinks(): HasMany
    {
        return $this->hasMany(OrderReservation::class);
    }

    public function couponRedemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
