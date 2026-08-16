<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Commerce\AdjustInventory;
use App\Domain\Commerce\StockMovement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InventoryAdjustmentRequest;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        return view('admin.commerce.inventory.index', ['variants' => ProductVariant::with('product')->withCount(['reservations as active_reservations_count' => fn ($q) => $q->where('state', 'active')])->orderBy('normalized_sku')->paginate(25)]);
    }

    public function show(ProductVariant $variant): View
    {
        return view('admin.commerce.inventory.show', ['variant' => $variant->load('product'), 'entries' => $variant->ledgerEntries()->latest()->paginate(30), 'reservations' => $variant->reservations()->where('state', 'active')->orderBy('expires_at')->paginate(20, ['*'], 'reservations')]);
    }

    public function adjust(InventoryAdjustmentRequest $r, ProductVariant $variant, AdjustInventory $adjust): RedirectResponse
    {
        $adjust->execute($variant, $r->integer('quantity'), StockMovement::from($r->validated('movement')), $r->validated('reason'), $r->user());

        return back()->with('success', 'Inventory adjusted.');
    }
}
