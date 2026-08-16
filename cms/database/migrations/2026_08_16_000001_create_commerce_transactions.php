<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([['commerce.orders.manage', 'Manage commerce orders'], ['commerce.rules.manage', 'Manage commerce rules']] as [$name,$label]) {
            $permissionId = DB::table('permissions')->where('name', $name)->value('id') ?? DB::table('permissions')->insertGetId(['name' => $name, 'label' => $label]);
            $adminId = DB::table('roles')->where('name', 'admin')->value('id');
            if ($adminId) {
                DB::table('permission_role')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $adminId]);
            }
        }
        Schema::create('carts', function (Blueprint $t) {
            $t->id();
            $t->char('token_hash', 64)->unique();
            $t->string('currency', 3)->nullable();
            $t->string('state', 24)->default('active');
            $t->unsignedInteger('version')->default(1);
            $t->timestamp('expires_at')->nullable()->index();
            $t->timestamps();
            $t->index(['state', 'updated_at']);
        });
        Schema::create('cart_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('observed_price_minor');
            $t->timestamps();
            $t->unique(['cart_id', 'product_variant_id']);
        });
        Schema::create('shipping_zones', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100);
            $t->char('country_code', 2)->index();
            $t->string('region', 100)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['country_code', 'region', 'is_active']);
        });
        Schema::create('shipping_methods', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->boolean('is_active')->default(true);
            $t->unsignedBigInteger('amount_minor');
            $t->char('currency', 3);
            $t->unsignedBigInteger('free_shipping_threshold_minor')->nullable();
            $t->unsignedSmallInteger('position')->default(0);
            $t->timestamps();
        });
        Schema::create('tax_rules', function (Blueprint $t) {
            $t->id();
            $t->char('country_code', 2)->index();
            $t->string('region', 100)->nullable();
            $t->unsignedSmallInteger('rate_basis_points');
            $t->unsignedSmallInteger('priority')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index(['country_code', 'region', 'is_active', 'priority']);
        });
        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code', 80);
            $t->string('normalized_code', 80)->unique();
            $t->string('type', 20);
            $t->unsignedBigInteger('value');
            $t->char('currency', 3)->nullable();
            $t->unsignedBigInteger('minimum_subtotal_minor')->nullable();
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('reserved_count')->default(0);
            $t->unsignedInteger('consumed_count')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamp('valid_from')->nullable();
            $t->timestamp('valid_until')->nullable();
            $t->timestamps();
            $t->index(['is_active', 'valid_from', 'valid_until']);
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('public_reference', 40)->unique();
            $t->char('access_token_hash', 64);
            $t->char('idempotency_hash', 64)->unique();
            $t->char('request_fingerprint', 64);
            $t->string('state', 24)->index();
            $t->char('currency', 3);
            $t->unsignedBigInteger('subtotal_minor');
            $t->unsignedBigInteger('discount_minor')->default(0);
            $t->unsignedBigInteger('tax_minor')->default(0);
            $t->unsignedBigInteger('shipping_minor')->default(0);
            $t->unsignedBigInteger('total_minor');
            $t->string('customer_email', 254);
            $t->char('customer_email_hash', 64)->index();
            $t->json('shipping_address');
            $t->json('billing_address');
            $t->json('coupon_snapshot')->nullable();
            $t->json('shipping_snapshot');
            $t->json('tax_snapshot');
            $t->timestamp('expires_at')->index();
            $t->timestamps();
            $t->index(['state', 'created_at']);
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $t->string('product_name', 180);
            $t->string('sku', 100);
            $t->json('options_snapshot');
            $t->unsignedBigInteger('unit_price_minor');
            $t->char('currency', 3);
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('line_subtotal_minor');
            $t->unsignedBigInteger('line_discount_minor')->default(0);
            $t->unsignedBigInteger('line_tax_minor')->default(0);
            $t->timestamps();
            $t->index('order_id');
        });
        Schema::create('order_reservations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $t->foreignId('inventory_reservation_id')->constrained()->restrictOnDelete();
            $t->unique('inventory_reservation_id');
        });
        Schema::create('coupon_redemptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('coupon_id')->constrained()->restrictOnDelete();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('state', 20)->default('reserved');
            $t->timestamp('released_at')->nullable();
            $t->timestamps();
            $t->unique(['coupon_id', 'order_id']);
        });
        Schema::create('order_state_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('from_state', 24)->nullable();
            $t->string('to_state', 24);
            $t->string('reason', 160)->nullable();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['order_state_histories', 'coupon_redemptions', 'order_reservations', 'order_items', 'orders', 'coupons', 'tax_rules', 'shipping_methods', 'shipping_zones', 'cart_items', 'carts'] as $table) {
            Schema::dropIfExists($table);
        } $ids = DB::table('permissions')->whereIn('name', ['commerce.orders.manage', 'commerce.rules.manage'])->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
