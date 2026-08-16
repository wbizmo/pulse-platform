<?php

namespace App\Actions\Commerce;

use App\Domain\Commerce\ReservationState;
use App\Domain\Commerce\StockMovement;
use App\Models\InventoryLedgerEntry;
use App\Models\InventoryReservation;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

final class FinalizeReservation
{
    public function execute(InventoryReservation $reservation, ReservationState $target): InventoryReservation
    {
        if (! in_array($target, [ReservationState::Released, ReservationState::Expired, ReservationState::Consumed], true)) {
            throw new \InvalidArgumentException('Invalid final reservation state.');
        }

        return DB::transaction(function () use ($reservation, $target) {
            $r = InventoryReservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($r->state !== ReservationState::Active) {
                return $r;
            }
            $v = ProductVariant::query()->lockForUpdate()->findOrFail($r->product_variant_id);
            $v->reserved -= $r->quantity;
            if ($target === ReservationState::Consumed) {
                $v->on_hand -= $r->quantity;
            }
            $v->save();
            $r->update(['state' => $target, 'finalized_at' => now()]);
            $movement = match ($target) {
                ReservationState::Released => StockMovement::ReservationRelease,
                ReservationState::Expired => StockMovement::ReservationExpiry,
                ReservationState::Consumed => StockMovement::ReservationConsume,
            };
            InventoryLedgerEntry::create(['product_variant_id' => $v->id, 'inventory_reservation_id' => $r->id, 'movement' => $movement, 'on_hand_delta' => $target === ReservationState::Consumed ? -$r->quantity : 0, 'reserved_delta' => -$r->quantity, 'on_hand_after' => $v->on_hand, 'reserved_after' => $v->reserved]);

            return $r->fresh();
        });
    }
}
