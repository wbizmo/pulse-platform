<?php

namespace Tests\Feature;

use App\Actions\Commerce\CreateOrder;
use App\Actions\Commerce\GetOrCreateCart;
use App\Actions\Commerce\MutateCart;
use App\Actions\Commerce\TransitionOrder;
use App\Actions\Payments\CompleteRefund;
use App\Actions\Payments\ConfirmSuccessfulPayment;
use App\Domain\Commerce\OrderState;
use App\Domain\Commerce\ProductState;
use App\Domain\Payments\PaymentState;
use App\Domain\Payments\RefundState;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\PaymentGatewayConfiguration;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Refund;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class PaymentsRefundsTest extends TestCase
{
    use RefreshDatabase;

    private function order(): array
    {
        $p = Product::create(['name' => 'Payable', 'slug' => 'payable', 'state' => ProductState::Active]);
        $v = ProductVariant::create(['product_id' => $p->id, 'sku' => 'PAY-1', 'normalized_sku' => 'PAY-1', 'is_active' => true, 'price_minor' => 1500, 'currency' => 'USD', 'options' => [], 'options_fingerprint' => hash('sha256', 'x'), 'tracks_stock' => true, 'on_hand' => 2]);
        $v->forceFill(['on_hand' => 2])->save();
        $z = ShippingZone::create(['name' => 'US', 'country_code' => 'US']);
        $m = ShippingMethod::create(['shipping_zone_id' => $z->id, 'name' => 'Free', 'amount_minor' => 0, 'currency' => 'USD']);
        [$cart] = app(GetOrCreateCart::class)->execute(null);
        app(MutateCart::class)->add($cart, $v, 1);
        [$o] = app(CreateOrder::class)->execute($cart, ['shipping_method_id' => $m->id, 'shipping_address' => ['full_name' => 'Customer', 'organization' => null, 'line1' => '1 Main', 'line2' => null, 'city' => 'City', 'region' => null, 'postal_code' => null, 'country_code' => 'US', 'email' => 'customer@example.test', 'phone' => null], 'coupon_code' => null], str_repeat('p', 43));
        $payment = Payment::create(['order_id' => $o->id, 'amount_minor' => $o->total_minor, 'currency' => $o->currency, 'state' => PaymentState::Pending]);
        $attempt = PaymentAttempt::create(['payment_id' => $payment->id, 'gateway' => 'stripe', 'reference' => (string) Str::uuid(), 'idempotency_key' => hash('sha256', Str::random()), 'provider_reference' => 'pi_test', 'state' => PaymentState::Pending]);

        return [$o, $payment, $attempt, $v];
    }

    public function test_authoritative_success_is_idempotent_and_consumes_once(): void
    {
        [$o,$p,$a,$v] = $this->order();
        $action = app(ConfirmSuccessfulPayment::class);
        $action->execute($a, $o->total_minor, 'USD');
        $action->execute($a, $o->total_minor, 'USD');
        $this->assertSame(OrderState::Paid, $o->fresh()->state);
        $this->assertSame(1, $v->fresh()->on_hand);
        $this->assertSame(0, $v->fresh()->reserved);
        $this->assertSame(1, $o->histories()->where('to_state', 'paid')->count());
    }

    public function test_commercial_mismatch_cannot_fulfil(): void
    {
        [$o,,$a,$v] = $this->order();
        try {
            app(ConfirmSuccessfulPayment::class)->execute($a, $o->total_minor + 1, 'USD');
            $this->fail('Mismatch fulfilled');
        } catch (ValidationException) {
        }$this->assertSame(OrderState::AwaitingPayment, $o->fresh()->state);
        $this->assertSame(1, $v->fresh()->reserved);
    }

    public function test_success_after_cancellation_is_reconciliation_only(): void
    {
        [$o,$p,$a,$v] = $this->order();
        app(TransitionOrder::class)->execute($o, OrderState::Cancelled);
        app(ConfirmSuccessfulPayment::class)->execute($a, $o->total_minor, 'USD');
        $this->assertSame(OrderState::Cancelled, $o->fresh()->state);
        $this->assertNotNull($p->fresh()->reconciliation_required_at);
        $this->assertSame(2, $v->fresh()->on_hand);
    }

    public function test_refund_completion_is_monotonic_and_does_not_restock(): void
    {
        [$o,$p,$a,$v] = $this->order();
        app(ConfirmSuccessfulPayment::class)->execute($a, $o->total_minor, 'USD');
        $r = Refund::create(['payment_id' => $p->id, 'order_id' => $o->id, 'gateway' => 'stripe', 'reference' => (string) Str::uuid(), 'amount_minor' => 500, 'currency' => 'USD', 'state' => RefundState::Succeeded, 'reason' => 'Partial', 'idempotency_key' => hash('sha256', 'refund')]);
        app(CompleteRefund::class)->execute($r);
        app(CompleteRefund::class)->execute($r);
        $this->assertSame(500, $p->fresh()->refunded_minor);
        $this->assertSame(OrderState::PartiallyRefunded, $o->fresh()->state);
        $this->assertSame(1, $v->fresh()->on_hand);
    }

    public function test_gateway_secrets_are_encrypted_and_hidden(): void
    {
        $c = PaymentGatewayConfiguration::create(['gateway' => 'stripe', 'enabled' => true, 'environment' => 'sandbox', 'secret' => 'sk_test_secret', 'webhook_secret' => 'whsec_secret', 'currencies' => ['USD']]);
        $this->assertStringNotContainsString('sk_test_secret', (string) \DB::table('payment_gateway_configurations')->where('id', $c->id)->value('secret'));
        $this->assertArrayNotHasKey('secret', $c->toArray());
    }
}
