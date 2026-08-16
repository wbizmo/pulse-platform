<?php

namespace App\Actions\Payments;

use App\Actions\Commerce\FinalizeReservation;
use App\Domain\Commerce\OrderState;
use App\Domain\Commerce\ReservationState;
use App\Domain\Payments\PaymentState;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderStateHistory;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ConfirmSuccessfulPayment
{
    public function __construct(private FinalizeReservation $finalize) {}

    public function execute(PaymentAttempt $attempt, int $amount, string $currency): Payment
    {
        return DB::transaction(function () use ($attempt, $amount, $currency) {
            $a = PaymentAttempt::lockForUpdate()->findOrFail($attempt->id);
            $p = Payment::lockForUpdate()->findOrFail($a->payment_id);
            $o = Order::lockForUpdate()->findOrFail($p->order_id);
            if ($p->state === PaymentState::Succeeded) {
                return $p;
            }if ($amount !== $p->amount_minor || strtoupper($currency) !== $p->currency) {
                throw ValidationException::withMessages(['payment' => 'Provider amount or currency mismatch.']);
            }if ($o->state !== OrderState::AwaitingPayment) {
                $p->update(['reconciliation_required_at' => now()]);
                $a->update(['state' => PaymentState::Succeeded, 'completed_at' => now()]);

                return $p->fresh();
            }$p->update(['state' => PaymentState::Succeeded, 'captured_minor' => $amount, 'paid_at' => now(), 'reconciliation_required_at' => null]);
            $a->update(['state' => PaymentState::Succeeded, 'completed_at' => now()]);
            $o->load('reservationLinks.reservation');
            foreach ($o->reservationLinks as $link) {
                $this->finalize->execute($link->reservation, ReservationState::Consumed);
            }$redemption = $o->couponRedemptions()->where('state', 'reserved')->lockForUpdate()->first();
            if ($redemption) {
                $coupon = Coupon::lockForUpdate()->findOrFail($redemption->coupon_id);
                if ($coupon->reserved_count > 0) {
                    $coupon->decrement('reserved_count');
                }$coupon->increment('consumed_count');
                $redemption->update(['state' => 'consumed', 'consumed_at' => now()]);
            }$o->update(['state' => OrderState::Paid]);
            OrderStateHistory::create(['order_id' => $o->id, 'from_state' => OrderState::AwaitingPayment->value, 'to_state' => OrderState::Paid->value, 'reason' => 'Authoritative payment confirmed', 'created_at' => now()]);

            return $p->fresh();
        }, 5);
    }
}
