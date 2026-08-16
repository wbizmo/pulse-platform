<?php

namespace Tests\Feature;

use App\Actions\Commerce\AdjustInventory;
use App\Actions\Commerce\FinalizeReservation;
use App\Actions\Commerce\ReserveInventory;
use App\Domain\Commerce\Currency;
use App\Domain\Commerce\Money;
use App\Domain\Commerce\ProductState;
use App\Domain\Commerce\ReservationState;
use App\Domain\Commerce\StockMovement;
use App\Models\InventoryLedgerEntry;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CommerceCatalogueInventoryTest extends TestCase
{
    use RefreshDatabase;

    private function variant(int $stock = 0): ProductVariant
    {
        $product = Product::create(['name' => 'Safe <script>', 'slug' => 'safe-product', 'state' => ProductState::Active]);

        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'Sku-One', 'normalized_sku' => 'SKU-ONE', 'is_active' => true, 'price_minor' => 129900, 'currency' => 'NGN', 'options' => ['size' => 'L'], 'options_fingerprint' => hash('sha256', 'size-l'), 'tracks_stock' => true]);
        $variant->forceFill(['on_hand' => $stock])->save();

        return $variant;
    }

    public function test_money_uses_minor_units_and_explicit_supported_currency(): void
    {
        $this->assertStringContainsString('1,299', (new Money(129900, Currency::NGN))->format());
        $this->expectException(\InvalidArgumentException::class);
        new Money(-1, Currency::USD);
    }

    public function test_ledger_adjustments_are_atomic_and_cannot_make_available_negative(): void
    {
        $variant = $this->variant();
        $actor = User::factory()->create();
        app(AdjustInventory::class)->execute($variant, 2, StockMovement::Opening, 'Initial count', $actor);
        $this->assertDatabaseHas('inventory_ledger_entries', ['product_variant_id' => $variant->id, 'on_hand_after' => 2]);
        $this->expectException(ValidationException::class);
        app(AdjustInventory::class)->execute($variant, -3, StockMovement::AdjustmentDecrease, 'Bad count', $actor);
    }

    public function test_competing_reservations_cannot_oversell_and_finalization_is_idempotent(): void
    {
        $variant = $this->variant(1);
        $reserve = app(ReserveInventory::class);
        $reservation = $reserve->execute($variant, 1, now()->addMinutes(10), 'test');
        try {
            $reserve->execute($variant, 1, now()->addMinutes(10));
            $this->fail('Second reservation succeeded.');
        } catch (ValidationException) {
        }
        $finalize = app(FinalizeReservation::class);
        $finalize->execute($reservation, ReservationState::Released);
        $finalize->execute($reservation, ReservationState::Released);
        $this->assertSame(0, $variant->fresh()->reserved);
        $this->assertSame(2, InventoryLedgerEntry::where('product_variant_id', $variant->id)->count());
    }

    public function test_expiration_command_releases_once(): void
    {
        $variant = $this->variant(2);
        $reservation = app(ReserveInventory::class)->execute($variant, 1, now()->subSecond());
        $this->artisan('commerce:expire-reservations --batch=1')->assertSuccessful();
        $this->artisan('commerce:expire-reservations --batch=1')->assertSuccessful();
        $this->assertSame(ReservationState::Expired, $reservation->fresh()->state);
        $this->assertSame(0, $variant->fresh()->reserved);
    }

    public function test_public_visibility_scope_excludes_archived_products(): void
    {
        $variant = $this->variant(1);
        $this->assertSame(1, Product::publiclyVisible()->count());
        $variant->product->update(['state' => ProductState::Archived]);
        $this->assertSame(0, Product::publiclyVisible()->count());
    }
}
