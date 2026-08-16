<?php

namespace App\Http\Controllers;

use App\Actions\Payments\ConfirmSuccessfulPayment;
use App\Actions\Payments\InitiatePayment;
use App\Domain\Payments\PaymentState;
use App\Models\Order;
use App\Payments\PaymentGatewayRegistry;
use Illuminate\Http\Request;

final class PaymentController extends Controller
{
    private function order(Request $r, string $reference): Order
    {
        $o = Order::with(['items', 'payment.attempts'])->where('public_reference', $reference)->firstOrFail();
        $token = $r->cookie(config('commerce.order_cookie'));
        abort_unless(is_string($token) && hash_equals($o->access_token_hash, hash('sha256', $token)), 404);

        return $o;
    }

    public function show(Request $r, string $reference, PaymentGatewayRegistry $registry)
    {
        $order = $this->order($r, $reference);

        return response()->view('frontend.commerce.payment', ['order' => $order, 'gateways' => $registry->available($order->currency)])->header('X-Robots-Tag', 'noindex, nofollow, noarchive')->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $r, string $reference, InitiatePayment $initiate)
    {
        $order = $this->order($r, $reference);
        $data = $r->validate(['gateway' => ['required', 'string', 'max:24']]);
        $attempt = $initiate->execute($order, $data['gateway']);
        $url = $attempt->action['redirect_url'] ?? null;
        if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return redirect()->away($url);
        }

return redirect()->route('payments.show', $order->public_reference)->with('success', 'Payment initialized securely.');
    }

    public function callback(Request $r, string $gateway, string $reference, PaymentGatewayRegistry $registry, ConfirmSuccessfulPayment $confirm)
    {
        $order = $this->order($r, $reference);
        $attempt = $order->payment?->attempts()->where('gateway', $gateway)->latest()->first();
        if ($attempt) {
            $result = $registry->resolve($gateway)->verify($attempt);
            if ($result->state === PaymentState::Succeeded) {
                $confirm->execute($attempt, (int) $result->amountMinor, (string) $result->currency);
            }
        }

return redirect()->route('orders.show',$reference);
    }
}
