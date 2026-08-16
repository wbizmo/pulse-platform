<?php

namespace App\Http\Controllers;

use App\Actions\Payments\ProcessWebhookEvent;
use App\Models\PaymentWebhookEvent;
use App\Payments\PaymentGatewayRegistry;
use Illuminate\Http\Request;

final class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, string $gateway, PaymentGatewayRegistry $registry, ProcessWebhookEvent $process)
    {
        $raw = $request->getContent();
        abort_if(strlen($raw) > 262144, 413);
        abort_unless(str_contains(strtolower((string) $request->header('content-type')), 'application/json'), 415);
        $adapter = $registry->resolve($gateway);
        $headers = collect($request->headers->all())->map(fn ($v) => is_array($v) ? ($v[0] ?? '') : $v)->all();
        abort_unless($adapter->verifyWebhook($raw, $headers), 401);
        $payload = json_decode($raw, true, 32);
        abort_unless(is_array($payload) && json_last_error() === JSON_ERROR_NONE, 400);
        $normalized = $adapter->normalizeWebhook($payload);
        $event = PaymentWebhookEvent::firstOrCreate(['gateway' => $gateway, 'external_event_id' => $normalized['id']], ['event_type' => mb_substr($normalized['type'], 0, 120), 'provider_reference' => mb_substr($normalized['reference'], 0, 191), 'payload_hash' => hash('sha256', $raw), 'signature_verified' => true, 'processing_state' => 'received', 'received_at' => now()]);
        if ($event->payload_hash !== hash('sha256', $raw)) {
            abort(409);
        }if ($event->processing_state !== 'processed') {
            $process->execute($event, $normalized);
        }

        return response()->json(['received' => true], 202);
    }
}
