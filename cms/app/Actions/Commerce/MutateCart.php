<?php

namespace App\Actions\Commerce;

use App\Domain\Commerce\ProductState;
use App\Models\Cart;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class MutateCart
{
    public const MAX_QUANTITY = 100;

    public function add(Cart $cart, ProductVariant $variant, int $quantity): void
    {
        $this->quantity($quantity);
        DB::transaction(function () use ($cart, $variant, $quantity) {
            $c = Cart::lockForUpdate()->findOrFail($cart->id);
            $v = ProductVariant::with('product')->lockForUpdate()->findOrFail($variant->id);
            $this->available($v, $quantity);
            if ($c->currency && $c->currency !== $v->currency) {
                throw ValidationException::withMessages(['variant' => 'Items must use the cart currency.']);
            }$item = $c->items()->where('product_variant_id', $v->id)->lockForUpdate()->first();
            $new = $quantity + ($item?->quantity ?? 0);
            $this->quantity($new);
            $this->available($v, $new);
            $c->update(['currency' => $v->currency, 'version' => $c->version + 1]);
            $c->items()->updateOrCreate(['product_variant_id' => $v->id], ['quantity' => $new, 'observed_price_minor' => $v->price_minor]);
        });
    }

    public function set(Cart $cart, int $itemId, int $quantity): void
    {
        $this->quantity($quantity);
        DB::transaction(function () use ($cart, $itemId, $quantity) {
            $c = Cart::lockForUpdate()->findOrFail($cart->id);
            $item = $c->items()->whereKey($itemId)->lockForUpdate()->firstOrFail();
            $this->available($item->variant()->with('product')->firstOrFail(), $quantity);
            $item->update(['quantity' => $quantity, 'observed_price_minor' => $item->variant->price_minor]);
            $c->increment('version');
        });
    }

    public function remove(Cart $cart, int $itemId): void
    {
        DB::transaction(function () use ($cart, $itemId) {
            $c = Cart::lockForUpdate()->findOrFail($cart->id);
            $c->items()->whereKey($itemId)->delete();
            $c->increment('version');
        });
    }

    private function quantity(int $q): void
    {
        if ($q < 1 || $q > self::MAX_QUANTITY) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be between 1 and 100.']);
        }
    }

    private function available(ProductVariant $v, int $q): void
    {
        if (! $v->is_active || $v->product->state !== ProductState::Active) {
            throw ValidationException::withMessages(['variant' => 'This product variant is unavailable.']);
        }if ($v->tracks_stock && $v->available < $q) {
            throw ValidationException::withMessages(['quantity' => 'Insufficient available stock.']);
        }
    }
}
