<?php

namespace App\Console\Commands;

use App\Actions\Commerce\FinalizeReservation;
use App\Domain\Commerce\ReservationState;
use App\Models\InventoryReservation;
use Illuminate\Console\Command;

class ExpireCommerceReservations extends Command
{
    protected $signature = 'commerce:expire-reservations {--batch=100}';

    protected $description = 'Expire a bounded batch of commerce inventory reservations';

    public function handle(FinalizeReservation $finalize): int
    {
        $batch = max(1, min(1000, (int) $this->option('batch')));
        // Order-linked reservations are owned exclusively by the order expiry lifecycle.
        $ids = InventoryReservation::where('state', 'active')->whereDoesntHave('orderLink')->where('expires_at', '<=', now())->orderBy('expires_at')->limit($batch)->pluck('id');
        foreach ($ids as $id) {
            $reservation = InventoryReservation::find($id);
            if ($reservation) {
                $finalize->execute($reservation, ReservationState::Expired);
            }
        }
        $this->info("Expired {$ids->count()} reservation(s).");

        return self::SUCCESS;
    }
}
