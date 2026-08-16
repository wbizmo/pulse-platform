<?php

namespace App\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Domain\Payments\PaymentResult;
use App\Domain\Payments\PaymentState;
use App\Domain\Payments\RefundResult;
use App\Domain\Payments\RefundState;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\PaymentGatewayConfiguration;
use App\Models\Refund;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

abstract class AbstractHttpGateway implements PaymentGateway
{
    public function __construct(protected PaymentGatewayConfiguration $configuration) {}

    protected function http(): PendingRequest
    {
        return Http::acceptJson()->asJson()->connectTimeout(5)->timeout(15)->retry(2, 200, throw: false);
    }

    protected function string(array $data, string $key, int $max = 191): string
    {
        $v = data_get($data, $key);
        if (! is_string($v) || $v === '' || mb_strlen($v) > $max) {
            throw ValidationException::withMessages(['gateway' => 'Payment provider returned an invalid response.']);
        }

return $v;
    }

    protected function status(string $value): PaymentState
    {
        return match (strtolower($value)) {
            'succeeded','successful','success','completed','captured' => PaymentState::Succeeded,'requires_action','requires_payment_method' => PaymentState::RequiresAction,'pending','processing','approved' => PaymentState::Pending,'cancelled','canceled','voided' => PaymentState::Cancelled,default => PaymentState::Failed
        };
    }

    protected function refundStatus(string $value): RefundState
    {
        return match (strtolower($value)) {
            'succeeded','successful','success','completed' => RefundState::Succeeded,'pending' => RefundState::Pending,'processing' => RefundState::Processing,'cancelled','canceled' => RefundState::Cancelled,default => RefundState::Failed
        };
    }

    abstract public function initiate(PaymentAttempt $attempt, Order $order): PaymentResult;

    abstract public function verify(PaymentAttempt $attempt): PaymentResult;

    abstract public function refund(Refund $refund, string $providerPaymentReference): RefundResult;
}
