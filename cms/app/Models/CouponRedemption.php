<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    protected $fillable = ['coupon_id', 'order_id', 'state', 'released_at', 'consumed_at'];

    protected function casts(): array
    {
        return ['released_at' => 'datetime', 'consumed_at' => 'datetime'];
    }
}
