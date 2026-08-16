<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    protected $fillable = ['coupon_id', 'order_id', 'state', 'released_at'];

    protected function casts(): array
    {
        return ['released_at' => 'datetime'];
    }
}
