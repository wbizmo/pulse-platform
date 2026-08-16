<?php

namespace App\Actions\Commerce;

use App\Domain\Commerce\OrderState;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderReservation;
use App\Models\OrderStateHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateOrder
{
    public function __construct(private CalculateCheckout $calculate, private ReserveInventory $reserve) {}

    public function execute(Cart $cart, array $input, string $key): array
    {
        if (! preg_match('/^[A-Za-z0-9_-]{32,100}$/', $key)) {
            throw ValidationException::withMessages(['idempotency_key' => 'A valid checkout idempotency key is required.']);
        }$hash = hash('sha256', $key);
        $fingerprint = hash('sha256', json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $existing = Order::where('idempotency_hash', $hash)->first();
        if ($existing) {
            if (! hash_equals($existing->request_fingerprint, $fingerprint)) {
                throw ValidationException::withMessages(['idempotency_key' => 'This key was already used for different checkout details.']);
            }

return [$existing, null, true];
        }

        return DB::transaction(function () use ($cart, $input, $hash, $fingerprint) {
            $locked = Cart::lockForUpdate()->findOrFail($cart->id);
            $existing = Order::where('idempotency_hash', $hash)->first();
            if ($existing) {
                if (! hash_equals($existing->request_fingerprint, $fingerprint)) {
                    throw ValidationException::withMessages(['idempotency_key' => 'This key was already used for different checkout details.']);
                }

return [$existing, null, true];
            }$quote = $this->calculate->execute($locked, $input['shipping_address'], $input['shipping_method_id'], $input['coupon_code'] ?? null);
            $coupon = $quote['coupon'] ? Coupon::lockForUpdate()->find($quote['coupon']->id) : null;
            if ($coupon && $coupon->usage_limit !== null && $coupon->reserved_count + $coupon->consumed_count >= $coupon->usage_limit) {
                throw ValidationException::withMessages(['coupon_code' => 'Coupon usage limit has been reached.']);
            }$access = Str::random(43);
            $reference = 'PULSE-'.mb_strtoupper(Str::random(16));
            $expires = now()->addMinutes((int) config('commerce.order_reservation_minutes', 30));
            $address = $input['shipping_address'];
            $order = Order::create(['public_reference' => $reference, 'access_token_hash' => hash('sha256', $access), 'idempotency_hash' => $hash, 'request_fingerprint' => $fingerprint, 'state' => OrderState::AwaitingPayment, 'currency' => $quote['currency'], 'subtotal_minor' => $quote['subtotal'], 'discount_minor' => $quote['discount'], 'tax_minor' => $quote['tax'], 'shipping_minor' => $quote['shipping'], 'total_minor' => $quote['total'], 'customer_email' => $address['email'], 'customer_email_hash' => hash('sha256', mb_strtolower($address['email'])), 'shipping_address' => $address, 'billing_address' => $input['billing_address'] ?? $address, 'coupon_snapshot' => $coupon ? ['code' => $coupon->code, 'type' => $coupon->type->value, 'value' => $coupon->value] : null, 'shipping_snapshot' => ['name' => $quote['method']->name, 'amount_minor' => $quote['shipping'], 'zone' => $quote['method']->zone->name], 'tax_snapshot' => $quote['taxRule'] ? ['country_code' => $quote['taxRule']->country_code, 'region' => $quote['taxRule']->region, 'rate_basis_points' => $quote['taxRule']->rate_basis_points] : [], 'expires_at' => $expires]);
            OrderStateHistory::create(['order_id' => $order->id, 'to_state' => OrderState::AwaitingPayment->value, 'reason' => 'Checkout completed', 'created_at' => now()]);
            foreach (collect($quote['lines'])->sortBy(fn ($l) => $l['variant']->id) as $line) {
                $v = $line['variant'];
                $item = $order->items()->create(['product_id' => $v->product_id, 'product_variant_id' => $v->id, 'product_name' => $v->product->name, 'sku' => $v->sku, 'options_snapshot' => $v->options ?? [], 'unit_price_minor' => $v->price_minor, 'currency' => $v->currency, 'quantity' => $line['item']->quantity, 'line_subtotal_minor' => $line['subtotal']]);
                $reservation = $this->reserve->execute($v, $item->quantity, $expires, $reference);
                OrderReservation::create(['order_id' => $order->id, 'order_item_id' => $item->id, 'inventory_reservation_id' => $reservation->id]);
            }if ($coupon) {
                $coupon->increment('reserved_count');
                CouponRedemption::create(['coupon_id' => $coupon->id, 'order_id' => $order->id]);
            }$locked->update(['state' => 'converted']);

            return [$order, $access, false];
        }, 5);
    }
}
