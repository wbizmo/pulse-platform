<?php

namespace App\Actions\Payments;

use App\Domain\Payments\PaymentState;
use App\Models\PaymentAttempt;
use App\Models\PaymentWebhookEvent;
use App\Payments\PaymentGatewayRegistry;
use Illuminate\Validation\ValidationException;

final class ProcessWebhookEvent
{
    public function __construct(private PaymentGatewayRegistry $gateways, private ConfirmSuccessfulPayment $confirm) {}

    public function execute(PaymentWebhookEvent $event, array $normalized): void
    {
        if ($event->processing_state === 'processed') {
            return;
        }try {
            $attempt = PaymentAttempt::where('gateway', $event->gateway)->where(fn ($q) => $q->where('provider_reference', $normalized['reference'])->orWhere('reference', $normalized['reference']))->first();
            if (! $attempt) {
                $event->update(['processing_state' => 'unmatched', 'error_code' => 'attempt_not_found', 'processed_at' => now()]);

                return;
            }$type = strtolower($normalized['type']);
            if (str_contains($type, 'succeed') || str_contains($type, 'capture.completed') || str_contains($type, 'charge.success')) {
                $verified = $this->gateways->resolve($event->gateway)->verify($attempt);
                if ($verified->state !== PaymentState::Succeeded) {
                    throw new \RuntimeException('provider_not_successful');
                }$this->confirm->execute($attempt, (int) $verified->amountMinor, (string) $verified->currency);
            } elseif (str_contains($type, 'fail') || str_contains($type, 'cancel')) {
                $attempt->update(['state' => str_contains($type, 'cancel') ? PaymentState::Cancelled : PaymentState::Failed, 'provider_status' => mb_substr($normalized['status'], 0, 80), 'completed_at' => now()]);
            }$event->update(['processing_state' => 'processed', 'processed_at' => now(), 'error_code' => null]);
        } catch (\Throwable $e) {
            $event->update(['processing_state' => 'failed', 'retry_count' => $event->retry_count + 1, 'error_code' => mb_substr($e instanceof ValidationException ? 'commercial_mismatch' : 'processing_failed', 0, 80)]);
            throw $e;
        }
    }
}
