<?php

namespace App\Actions\Payments;

use App\Domain\Commerce\OrderState;
use App\Domain\Payments\RefundState;
use App\Models\Order;
use App\Models\OrderStateHistory;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;

final class CompleteRefund
{
    public function execute(Refund $refund): Refund
    {
        return DB::transaction(function () use ($refund) {
            $r = Refund::lockForUpdate()->findOrFail($refund->id);
            $p = Payment::lockForUpdate()->findOrFail($r->payment_id);
            if ($r->state !== RefundState::Succeeded) {
                $r->update(['state' => RefundState::Succeeded, 'completed_at' => now()]);
            }$total = $p->refunds()->where('state', RefundState::Succeeded)->sum('amount_minor');
            $p->update(['refunded_minor' => $total]);
            $o = Order::lockForUpdate()->findOrFail($p->order_id);
            $target = $total === $p->captured_minor ? OrderState::Refunded : OrderState::PartiallyRefunded;
            if ($o->state !== $target && $o->state->canTransitionTo($target)) {
                $from = $o->state;
                $o->update(['state' => $target]);
                OrderStateHistory::create(['order_id' => $o->id, 'from_state' => $from->value, 'to_state' => $target->value, 'reason' => 'Provider refund completed', 'created_at' => now()]);
            }

return $r->fresh();
        });
    }
}
