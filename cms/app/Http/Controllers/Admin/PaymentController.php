<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Actions\Payments\RequestRefund;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayConfiguration;
use App\Payments\PaymentGatewayRegistry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.commerce.payments.index', ['payments' => Payment::with(['order', 'attempts'])->latest()->paginate(25)]);
    }

    public function show(Payment $payment)
    {
        return view('admin.commerce.payments.show', ['payment' => $payment->load(['order', 'attempts', 'refunds', 'disputes'])]);
    }

    public function configuration(PaymentGatewayRegistry $r)
    {
        $configs = collect($r->slugs())->map(fn ($slug) => PaymentGatewayConfiguration::firstOrNew(['gateway' => $slug], ['environment' => 'sandbox', 'currencies' => []]));

        return view('admin.commerce.payments.configuration', compact('configs'));
    }

    public function updateConfiguration(Request $r, string $gateway, RecordAudit $audit)
    {
        $data = $r->validate(['enabled' => 'sometimes|boolean', 'environment' => ['required', Rule::in(['sandbox', 'live'])], 'public_identifier' => 'nullable|string|max:255', 'secret' => 'nullable|string|min:8|max:2000', 'webhook_secret' => 'nullable|string|min:8|max:2000', 'currencies' => 'required|array|min:1|max:20', 'currencies.*' => 'string|size:3']);
        $c = PaymentGatewayConfiguration::firstOrNew(['gateway' => $gateway]);
        $c->enabled = (bool) ($data['enabled'] ?? false);
        $c->environment = $data['environment'];
        $c->public_identifier = $data['public_identifier'] ?? null;
        $c->currencies = array_values(array_unique(array_map('strtoupper', $data['currencies'])));
        if (filled($data['secret'] ?? null)) {
            $c->secret = $data['secret'];
        }if (filled($data['webhook_secret'] ?? null)) {
            $c->webhook_secret = $data['webhook_secret'];
        }$c->save();
        $audit->execute($r->user(), 'commerce.payment_configuration.updated', $c, ['gateway' => $gateway, 'enabled' => $c->enabled, 'environment' => $c->environment, 'currencies' => $c->currencies, 'secret_replaced' => filled($data['secret'] ?? null), 'webhook_secret_replaced' => filled($data['webhook_secret'] ?? null)]);

        return back()->with('success', 'Payment configuration updated; secrets remain hidden.');
    }

    public function refund(Request $r, Payment $payment, RequestRefund $requestRefund, RecordAudit $audit)
    {
        $d = $r->validate(['amount_minor' => 'required|integer|min:1', 'reason' => 'required|string|max:240', 'idempotency_key' => 'required|string|min:32|max:100', 'gateway' => ['required', Rule::in(['stripe', 'paypal', 'flutterwave', 'paystack'])]]);
        $refund = $requestRefund->execute($payment, $d['gateway'], (int) $d['amount_minor'], $d['reason'], $d['idempotency_key'], $r->user());
        $audit->execute($r->user(), 'commerce.refund.initiated', $refund, ['payment_id' => $payment->id, 'refund_reference' => $refund->reference, 'amount_minor' => $refund->amount_minor, 'currency' => $refund->currency, 'gateway' => $refund->gateway]);

        return back()->with('success', 'Refund request recorded.');
    }
}
