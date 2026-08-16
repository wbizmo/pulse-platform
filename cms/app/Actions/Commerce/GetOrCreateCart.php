<?php

namespace App\Actions\Commerce;

use App\Models\Cart;
use Illuminate\Support\Str;

final class GetOrCreateCart
{
    public function execute(?string $token): array
    {
        if (is_string($token) && preg_match('/^[A-Za-z0-9_-]{43}$/', $token)) {
            $cart = Cart::where('token_hash', hash('sha256', $token))->where('state', 'active')->first();
            if ($cart) {
                return [$cart, $token, false];
            }
        } $raw = Str::random(43);

        return [Cart::create(['token_hash' => hash('sha256', $raw), 'expires_at' => now()->addDays(30)]), $raw, true];
    }
}
