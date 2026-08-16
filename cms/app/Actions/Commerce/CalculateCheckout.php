<?php

namespace App\Actions\Commerce;

use App\Domain\Commerce\CouponType;
use App\Domain\Commerce\MinorUnitMath;
use App\Domain\Commerce\ProductState;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\ShippingMethod;
use App\Models\TaxRule;
use Illuminate\Validation\ValidationException;

final class CalculateCheckout
{
    public function execute(Cart $cart, array $address, int $shippingMethodId, ?string $couponCode = null): array
    {
        $cart->load('items.variant.product');
        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }$lines = [];
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $v = $item->variant;
            if (! $v->is_active || $v->product->state !== ProductState::Active) {
                throw ValidationException::withMessages(['cart' => 'A cart item is no longer available.']);
            }if ($item->observed_price_minor !== $v->price_minor) {
                throw ValidationException::withMessages(['cart' => 'A price changed. Review your cart before checkout.']);
            }if ($v->currency !== $cart->currency) {
                throw ValidationException::withMessages(['cart' => 'Cart currency is inconsistent.']);
            }if ($v->tracks_stock && $v->available < $item->quantity) {
                throw ValidationException::withMessages(['cart' => 'A cart item has insufficient stock.']);
            }$line = $v->price_minor * $item->quantity;
            $subtotal += $line;
            $lines[] = ['item' => $item, 'variant' => $v, 'subtotal' => $line];
        }
        $method = ShippingMethod::with('zone')->whereKey($shippingMethodId)->where('is_active', true)->first();
        if (! $method || ! $method->zone->is_active || $method->zone->country_code !== $address['country_code'] || ($method->zone->region && mb_strtoupper($method->zone->region) !== mb_strtoupper($address['region'] ?? ''))) {
            throw ValidationException::withMessages(['shipping_method_id' => 'The shipping method is unavailable for this address.']);
        }if ($method->currency !== $cart->currency) {
            throw ValidationException::withMessages(['shipping_method_id' => 'Shipping currency does not match the cart.']);
        }$shipping = $method->free_shipping_threshold_minor !== null && $subtotal >= $method->free_shipping_threshold_minor ? 0 : $method->amount_minor;
        $coupon = null;
        $discount = 0;
        if ($couponCode) {
            $normalized = mb_strtoupper(trim($couponCode));
            $coupon = Coupon::where('normalized_code', $normalized)->where('is_active', true)->first();
            if (! $coupon || ($coupon->valid_from && $coupon->valid_from->isFuture()) || ($coupon->valid_until && $coupon->valid_until->isPast())) {
                throw ValidationException::withMessages(['coupon_code' => 'Coupon is invalid or expired.']);
            }if ($coupon->minimum_subtotal_minor !== null && $subtotal < $coupon->minimum_subtotal_minor) {
                throw ValidationException::withMessages(['coupon_code' => 'Coupon minimum subtotal is not met.']);
            }if ($coupon->type === CouponType::Fixed && $coupon->currency !== $cart->currency) {
                throw ValidationException::withMessages(['coupon_code' => 'Coupon currency does not match the cart.']);
            }$discount = min($subtotal, $coupon->type === CouponType::Percentage ? MinorUnitMath::percentage($subtotal, (int) $coupon->value) : (int) $coupon->value);
        }
        $taxRule = TaxRule::where('is_active', true)->where('country_code', $address['country_code'])->where(fn ($q) => $q->whereNull('region')->orWhere('region', $address['region'] ?? null))->orderByDesc('priority')->first();
        $taxable = $subtotal - $discount;
        $tax = $taxRule ? MinorUnitMath::percentage($taxable, $taxRule->rate_basis_points) : 0;

        return compact('lines', 'subtotal', 'discount', 'tax', 'shipping', 'coupon', 'method', 'taxRule') + ['total' => $taxable + $tax + $shipping, 'currency' => $cart->currency];
    }
}
