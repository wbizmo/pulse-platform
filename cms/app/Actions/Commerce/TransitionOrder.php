<?php

namespace App\Actions\Commerce;

use App\Domain\Commerce\OrderState;
use App\Domain\Commerce\ReservationState;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderStateHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TransitionOrder
{
    public function __construct(private FinalizeReservation $finalize) {}

    public function execute(Order $order, OrderState $target, ?User $actor = null, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $target, $actor, $reason) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);
            if ($locked->state === $target) {
                return $locked;
            }if (! $locked->state->canTransitionTo($target)) {
                throw ValidationException::withMessages(['state' => 'Order transition is not allowed.']);
            }$locked->load('reservationLinks.reservation');
            foreach ($locked->reservationLinks as $link) {
                $this->finalize->execute($link->reservation, $target === OrderState::Expired ? ReservationState::Expired : ReservationState::Released);
            }$redemption = CouponRedemption::where('order_id', $locked->id)->where('state', 'reserved')->lockForUpdate()->first();
            if ($redemption) {
                $coupon = Coupon::lockForUpdate()->findOrFail($redemption->coupon_id);
                if ($coupon->reserved_count > 0) {
                    $coupon->decrement('reserved_count');
                }$redemption->update(['state' => 'released', 'released_at' => now()]);
            }$from = $locked->state;
            $locked->update(['state' => $target]);
            OrderStateHistory::create(['order_id' => $locked->id, 'from_state' => $from->value, 'to_state' => $target->value, 'reason' => $reason, 'actor_id' => $actor?->id, 'created_at' => now()]);

            return $locked->fresh();
        });
    }
}
