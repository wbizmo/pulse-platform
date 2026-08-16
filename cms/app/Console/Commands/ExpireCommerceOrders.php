<?php

namespace App\Console\Commands;

use App\Actions\Commerce\TransitionOrder;
use App\Domain\Commerce\OrderState;
use App\Models\Order;
use Illuminate\Console\Command;

class ExpireCommerceOrders extends Command
{
    protected $signature = 'commerce:expire-orders {--batch=100}';

    protected $description = 'Expire a bounded batch of awaiting-payment orders';

    public function handle(TransitionOrder $transition): int
    {
        $batch = max(1, min(500, (int) $this->option('batch')));
        $ids = Order::where('state', OrderState::AwaitingPayment)->where('expires_at', '<=', now())->orderBy('expires_at')->limit($batch)->pluck('id');
        $count = 0;
        foreach ($ids as $id) {
            $order = Order::find($id);
            if ($order && $order->state === OrderState::AwaitingPayment) {
                $transition->execute($order, OrderState::Expired, null, 'Awaiting-payment window elapsed');
                $count++;
            }
        }$this->info("Expired {$count} order(s).");

        return self::SUCCESS;
    }
}
