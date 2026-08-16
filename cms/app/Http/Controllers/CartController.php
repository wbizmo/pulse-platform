<?php

namespace App\Http\Controllers;

use App\Actions\Commerce\GetOrCreateCart;
use App\Actions\Commerce\MutateCart;
use App\Http\Requests\Commerce\CartItemRequest;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends Controller
{
    private function cart(Request $r, GetOrCreateCart $get): array
    {
        return $get->execute($r->cookie(config('commerce.cart_cookie')));
    }

    private function cookie(RedirectResponse|Response $response, string $token): RedirectResponse|Response
    {
        return $response->cookie(config('commerce.cart_cookie'), $token, 60 * 24 * 30, '/', null, app()->environment('production'), true, false, 'lax');
    }

    public function show(Request $r, GetOrCreateCart $get)
    {
        [$cart,$token,$new] = $this->cart($r, $get);
        $cart->load('items.variant.product');
        $response = response()->view('frontend.commerce.cart', compact('cart'))->header('X-Robots-Tag', 'noindex, nofollow');

        return $new ? $this->cookie($response, $token) : $response;
    }

    public function add(CartItemRequest $r, GetOrCreateCart $get, MutateCart $mutate)
    {
        [$cart,$token,$new] = $this->cart($r, $get);
        $mutate->add($cart, ProductVariant::findOrFail($r->integer('variant_id')), $r->integer('quantity'));
        $response = redirect()->route('cart.show')->with('success', 'Item added to cart.');

        return $new ? $this->cookie($response, $token) : $response;
    }

    public function update(CartItemRequest $r, int $item, GetOrCreateCart $get, MutateCart $mutate)
    {
        [$cart] = $this->cart($r, $get);
        $mutate->set($cart, $item, $r->integer('quantity'));

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $r, int $item, GetOrCreateCart $get, MutateCart $mutate)
    {
        [$cart] = $this->cart($r, $get);
        $mutate->remove($cart, $item);

        return back()->with('success', 'Item removed.');
    }
}
