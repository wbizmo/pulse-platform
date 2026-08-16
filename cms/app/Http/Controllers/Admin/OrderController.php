<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Actions\Commerce\TransitionOrder;
use App\Domain\Commerce\OrderState;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $r)
    {
        $q = Order::query()->withCount('items')->latest();
        if ($r->filled('q')) {
            $term = mb_substr(trim($r->string('q')->toString()), 0, 100);
            $q->where(fn ($x) => $x->where('public_reference', 'like', '%'.addcslashes($term, '%_').'%')->orWhere('customer_email', 'like', '%'.addcslashes($term, '%_').'%'));
        }if ($r->filled('state')) {
            $q->where('state', OrderState::tryFrom($r->string('state')->toString())?->value ?? '__invalid__');
        }

        return view('admin.commerce.orders.index', ['orders' => $q->paginate(25)->withQueryString()]);
    }

    public function show(Order $order)
    {
        return view('admin.commerce.orders.show', ['order' => $order->load(['items', 'histories', 'reservationLinks.reservation', 'couponRedemptions'])]);
    }

    public function cancel(Request $r, Order $order, TransitionOrder $transition, RecordAudit $audit)
    {
        $transition->execute($order, OrderState::Cancelled, $r->user(), 'Cancelled by administrator');
        $audit->execute($r->user(), 'commerce.order.cancelled', $order, ['reference' => $order->public_reference, 'prior_state' => OrderState::AwaitingPayment->value]);

        return back()->with('success', 'Order cancelled and reservations released.');
    }
}
