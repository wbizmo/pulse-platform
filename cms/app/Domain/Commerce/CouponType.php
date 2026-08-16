<?php

namespace App\Domain\Commerce;

enum CouponType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
}
