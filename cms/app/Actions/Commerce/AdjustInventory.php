<?php

namespace App\Actions\Commerce;

use App\Actions\Access\RecordAudit;
use App\Domain\Commerce\StockMovement;
use App\Models\InventoryLedgerEntry;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AdjustInventory
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(ProductVariant $variant, int $delta, StockMovement $movement, string $reason, User $actor): ProductVariant
    {
        if ($delta === 0 || abs($delta) > 1_000_000) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be a non-zero integer no greater than 1,000,000.']);
        }

        return DB::transaction(function () use ($variant, $delta, $movement, $reason, $actor) {
            $locked = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
            $next = $locked->on_hand + $delta;
            if ($next < 0 || $next < $locked->reserved) {
                throw ValidationException::withMessages(['quantity' => 'Adjustment would make available stock negative.']);
            }
            $locked->on_hand = $next;
            $locked->save();
            InventoryLedgerEntry::create(['product_variant_id' => $locked->id, 'actor_id' => $actor->id, 'movement' => $movement, 'on_hand_delta' => $delta, 'reserved_delta' => 0, 'on_hand_after' => $next, 'reserved_after' => $locked->reserved, 'reason' => mb_substr($reason, 0, 300)]);
            $this->audit->execute($actor, 'commerce.inventory.adjusted', $locked, ['sku' => $locked->sku, 'delta' => $delta, 'movement' => $movement->value]);

            return $locked;
        });
    }
}
