<?php

namespace App\Contracts\Payments;

use App\Domain\Payments\PaymentResult;
use App\Domain\Payments\RefundResult;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Refund;

interface PaymentGateway
{
    public function initiate(PaymentAttempt $attempt, Order $order): PaymentResult;

    public function verify(PaymentAttempt $attempt): PaymentResult;

    public function refund(Refund $refund, string $providerPaymentReference): RefundResult;

    public function verifyWebhook(string $raw, array $headers): bool;

    public function normalizeWebhook(array $payload): array;
}
