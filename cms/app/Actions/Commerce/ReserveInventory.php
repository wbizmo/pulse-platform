<?php

namespace App\Actions\Commerce;

use App\Domain\Commerce\ReservationState;
use App\Domain\Commerce\StockMovement;
use App\Models\InventoryLedgerEntry;
use App\Models\InventoryReservation;
use App\Models\ProductVariant;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ReserveInventory
{
    public function execute(ProductVariant $variant, int $quantity, CarbonInterface $expiresAt, ?string $reference = null): InventoryReservation
    {
        if ($quantity < 1 || $quantity > 1_000_000) {
            throw ValidationException::withMessages(['quantity' => 'Reservation quantity is invalid.']);
        }

        return DB::transaction(function () use ($variant, $quantity, $expiresAt, $reference) {
            $v = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
            if (! $v->is_active || ! $v->product()->where('state', 'active')->exists()) {
                throw ValidationException::withMessages(['variant' => 'Variant is not available.']);
            }
            if ($v->tracks_stock && $v->available < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Insufficient available stock.']);
            }
            $r = InventoryReservation::create(['token' => (string) Str::uuid(), 'product_variant_id' => $v->id, 'quantity' => $quantity, 'state' => ReservationState::Active, 'reference' => $reference ? mb_substr($reference, 0, 120) : null, 'expires_at' => $expiresAt]);
            $v->increment('reserved', $quantity);
            $v->refresh();
            InventoryLedgerEntry::create(['product_variant_id' => $v->id, 'inventory_reservation_id' => $r->id, 'movement' => StockMovement::Reservation, 'on_hand_delta' => 0, 'reserved_delta' => $quantity, 'on_hand_after' => $v->on_hand, 'reserved_after' => $v->reserved]);

            return $r;
        });
    }
}
