<?php

namespace App\Http\Controllers;

use App\Actions\Commerce\CreateOrder;
use App\Actions\Commerce\GetOrCreateCart;
use App\Http\Requests\Commerce\CheckoutRequest;
use App\Models\Order;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(Request $r, GetOrCreateCart $get)
    {
        [$cart] = $get->execute($r->cookie(config('commerce.cart_cookie')));
        if ($cart->items()->doesntExist()) {
            return redirect()->route('cart.show');
        }$methods = ShippingMethod::with('zone')->where('is_active', true)->whereHas('zone', fn ($q) => $q->where('is_active', true))->orderBy('position')->limit(100)->get();

        return response()->view('frontend.commerce.checkout', compact('cart', 'methods'))->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'no-store, private');
    }

    public function store(CheckoutRequest $r, GetOrCreateCart $get, CreateOrder $create)
    {
        [$cart] = $get->execute($r->cookie(config('commerce.cart_cookie')));
        [$order,$access,$replay] = $create->execute($cart, $r->safe()->except('idempotency_key'), $r->string('idempotency_key')->toString());
        if ($replay && (! $r->cookie(config('commerce.order_cookie')) || ! hash_equals($order->access_token_hash, hash('sha256', $r->cookie(config('commerce.order_cookie')))))) {
            abort(403);
        }$token = $access ?? $r->cookie(config('commerce.order_cookie'));

        return redirect()->route('orders.show', $order->public_reference)->cookie(config('commerce.order_cookie'), $token, 60, '/', null, app()->environment('production'), true, false, 'strict');
    }

    public function order(Request $r, string $reference)
    {
        $order = Order::with('items')->where('public_reference', $reference)->firstOrFail();
        $token = $r->cookie(config('commerce.order_cookie'));
        abort_unless(is_string($token) && hash_equals($order->access_token_hash, hash('sha256', $token)), 404);

        return response()->view('frontend.commerce.order', compact('order'))->header('X-Robots-Tag', 'noindex, nofollow, noarchive')->header('Cache-Control', 'no-store, private');
    }
}
