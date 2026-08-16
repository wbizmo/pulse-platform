<?php

use App\Domain\Access\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('normalized_name', 120)->unique();
            $t->string('slug', 140)->unique();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
            $t->index(['is_active', 'position']);
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name', 180);
            $t->string('slug', 200)->unique();
            $t->string('short_description', 500)->nullable();
            $t->text('description')->nullable();
            $t->string('state', 20)->default('draft');
            $t->foreignId('featured_media_id')->nullable()->constrained('media')->restrictOnDelete();
            $t->timestamps();
            $t->index(['state', 'slug']);
        });
        Schema::create('product_category_product', function (Blueprint $t) {
            $t->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['product_category_id', 'product_id']);
        });
        Schema::create('product_media', function (Blueprint $t) {
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $t->unsignedSmallInteger('position');
            $t->primary(['product_id', 'media_id']);
            $t->unique(['product_id', 'position']);
        });
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->string('sku', 64);
            $t->string('normalized_sku', 64)->unique();
            $t->boolean('is_active')->default(true);
            $t->unsignedBigInteger('price_minor');
            $t->char('currency', 3);
            $t->json('options')->nullable();
            $t->string('options_fingerprint', 64);
            $t->boolean('tracks_stock')->default(true);
            $t->unsignedBigInteger('on_hand')->default(0);
            $t->unsignedBigInteger('reserved')->default(0);
            $t->timestamps();
            $t->unique(['product_id', 'options_fingerprint']);
            $t->index(['product_id', 'is_active']);
        });
        Schema::create('inventory_reservations', function (Blueprint $t) {
            $t->id();
            $t->uuid('token')->unique();
            $t->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $t->unsignedBigInteger('quantity');
            $t->string('state', 20)->default('active');
            $t->string('reference', 120)->nullable();
            $t->timestamp('expires_at');
            $t->timestamp('finalized_at')->nullable();
            $t->timestamps();
            $t->index(['state', 'expires_at']);
            $t->index(['product_variant_id', 'state']);
        });
        Schema::create('inventory_ledger_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $t->foreignId('inventory_reservation_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('movement', 40);
            $t->bigInteger('on_hand_delta')->default(0);
            $t->bigInteger('reserved_delta')->default(0);
            $t->unsignedBigInteger('on_hand_after');
            $t->unsignedBigInteger('reserved_after');
            $t->string('reason', 300)->nullable();
            $t->timestamps();
            $t->index(['product_variant_id', 'created_at']);
        });
        foreach ([Permission::ManageCommerceProducts, Permission::ManageCommerceInventory] as $permission) {
            DB::table('permissions')->updateOrInsert(['name' => $permission->value], ['label' => $permission->label()]);
            $id = DB::table('permissions')->where('name', $permission->value)->value('id');
            foreach (DB::table('roles')->whereIn('name', ['super_admin', 'admin'])->pluck('id') as $role) {
                DB::table('permission_role')->insertOrIgnore(['permission_id' => $id, 'role_id' => $role]);
            }
        }
    }

    public function down(): void
    {
        foreach (['commerce.products.manage', 'commerce.inventory.manage'] as $name) {
            $id = DB::table('permissions')->where('name', $name)->value('id');
            if ($id) {
                DB::table('permission_role')->where('permission_id', $id)->delete();
            }
            DB::table('permissions')->where('id', $id)->delete();
        }
        Schema::dropIfExists('inventory_ledger_entries');
        Schema::dropIfExists('inventory_reservations');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_category_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
