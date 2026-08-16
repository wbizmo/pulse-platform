<?php

namespace Tests\Feature;

use App\Actions\Commerce\CreateOrder;
use App\Actions\Commerce\GetOrCreateCart;
use App\Actions\Commerce\MutateCart;
use App\Actions\Commerce\TransitionOrder;
use App\Domain\Commerce\CouponType;
use App\Domain\Commerce\MinorUnitMath;
use App\Domain\Commerce\OrderState;
use App\Domain\Commerce\ProductState;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\TaxRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartCheckoutOrdersTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $currency = 'USD', int $stock = 3): array
    {
        $suffix = (string) (Product::count() + 1);
        $product = Product::create(['name' => 'Café <safe>', 'slug' => 'cafe-safe-'.$suffix, 'state' => ProductState::Active]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'SAFE-'.$suffix, 'normalized_sku' => 'SAFE-'.$suffix, 'is_active' => true, 'price_minor' => 1001, 'currency' => $currency, 'options' => ['size' => 'M'], 'options_fingerprint' => hash('sha256', 'm'), 'tracks_stock' => true]);
        $variant->forceFill(['on_hand' => $stock])->save();
        $zone = ShippingZone::create(['name' => 'Canada', 'country_code' => 'CA']);
        $method = ShippingMethod::create(['shipping_zone_id' => $zone->id, 'name' => 'Standard', 'amount_minor' => 499, 'currency' => $currency]);

        return [$variant, $method];
    }

    private function input(ShippingMethod $method): array
    {
        return ['shipping_method_id' => $method->id, 'shipping_address' => ['full_name' => '李 Example <script>', 'organization' => null, 'line1' => '1 Rue Test', 'line2' => null, 'city' => 'Montréal', 'region' => null, 'postal_code' => 'H1H 1H1', 'country_code' => 'CA', 'email' => 'guest@example.test', 'phone' => null], 'coupon_code' => null];
    }

    public function test_secure_guest_cart_merges_lines_and_enforces_currency(): void
    {
        [$cart, $token] = app(GetOrCreateCart::class)->execute(null);
        $this->assertSame(43, strlen($token));
        $this->assertNotSame($token, $cart->token_hash);
        [$variant] = $this->fixture();
        app(MutateCart::class)->add($cart, $variant, 1);
        app(MutateCart::class)->add($cart, $variant, 1);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id, 'quantity' => 2, 'observed_price_minor' => 1001]);
        [$other] = $this->fixture('EUR');
        $this->expectException(ValidationException::class);
        app(MutateCart::class)->add($cart, $other, 1);
    }

    public function test_integer_rounding_is_half_up(): void
    {
        $this->assertSame(1, MinorUnitMath::percentage(1, 5000));
        $this->assertSame(75, MinorUnitMath::percentage(999, 750));
    }

    public function test_checkout_is_authoritative_idempotent_and_snapshots_then_cancellation_releases(): void
    {
        [$variant, $method] = $this->fixture();
        [$cart] = app(GetOrCreateCart::class)->execute(null);
        app(MutateCart::class)->add($cart, $variant, 1);
        TaxRule::create(['country_code' => 'CA', 'rate_basis_points' => 750]);
        Coupon::create(['code' => 'SAVE10', 'normalized_code' => 'SAVE10', 'type' => CouponType::Percentage, 'value' => 1000, 'usage_limit' => 1]);
        $input = $this->input($method);
        $input['coupon_code'] = 'save10';
        [$order, $token] = app(CreateOrder::class)->execute($cart, $input, str_repeat('a', 43));
        $this->assertSame(OrderState::AwaitingPayment, $order->state);
        $this->assertSame(1001 - 100 + 68 + 499, $order->total_minor);
        $this->assertNotSame('Café changed', $order->items->first()->product_name);
        $variant->product->update(['name' => 'Café changed']);
        [$same, , $replay] = app(CreateOrder::class)->execute($cart, $input, str_repeat('a', 43));
        $this->assertTrue($replay);
        $this->assertSame($order->id, $same->id);
        $this->assertSame(1, Order::count());
        $this->assertSame(1, $variant->fresh()->reserved);
        app(TransitionOrder::class)->execute($order, OrderState::Cancelled);
        app(TransitionOrder::class)->execute($order, OrderState::Cancelled);
        $this->assertSame(0, $variant->fresh()->reserved);
        $this->assertDatabaseHas('coupons', ['normalized_code' => 'SAVE10', 'reserved_count' => 0]);
        $this->assertNotEmpty($token);
    }

    public function test_changed_idempotency_body_and_oversell_fail_atomically(): void
    {
        [$variant, $method] = $this->fixture(stock: 1);
        [$cart] = app(GetOrCreateCart::class)->execute(null);
        app(MutateCart::class)->add($cart, $variant, 1);
        $input = $this->input($method);
        app(CreateOrder::class)->execute($cart, $input, str_repeat('b', 43));
        $input['shipping_address']['city'] = 'Toronto';
        try {
            app(CreateOrder::class)->execute($cart, $input, str_repeat('b', 43));
            $this->fail('Changed replay succeeded.');
        } catch (ValidationException) {
        }
        [$second] = app(GetOrCreateCart::class)->execute(null);
        $this->expectException(ValidationException::class);
        app(MutateCart::class)->add($second, $variant, 1);
    }

    public function test_guest_order_capability_rejects_forgery(): void
    {
        $order = Order::create(['public_reference' => 'PULSE-REFERENCE', 'access_token_hash' => hash('sha256', 'secret'), 'idempotency_hash' => hash('sha256', 'key'), 'request_fingerprint' => hash('sha256', 'body'), 'state' => OrderState::AwaitingPayment, 'currency' => 'USD', 'subtotal_minor' => 1, 'total_minor' => 1, 'customer_email' => 'x@example.test', 'customer_email_hash' => hash('sha256', 'x@example.test'), 'shipping_address' => [], 'billing_address' => [], 'shipping_snapshot' => [], 'tax_snapshot' => [], 'expires_at' => now()->addMinute()]);
        $this->get(route('orders.show', $order->public_reference))->assertNotFound();
    }
}
