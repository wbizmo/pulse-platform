<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([['commerce.payments.manage', 'Manage payments'], ['commerce.refunds.manage', 'Manage refunds']] as [$name, $label]) {
            $id = DB::table('permissions')->where('name', $name)->value('id') ?? DB::table('permissions')->insertGetId(compact('name', 'label'));
            if ($role = DB::table('roles')->where('name', 'admin')->value('id')) {
                DB::table('permission_role')->insertOrIgnore(['permission_id' => $id, 'role_id' => $role]);
            }
        }
        Schema::create('payment_gateway_configurations', function (Blueprint $t) {
            $t->id();
            $t->string('gateway', 24)->unique();
            $t->boolean('enabled')->default(false);
            $t->string('environment', 12)->default('sandbox');
            $t->string('public_identifier')->nullable();
            $t->text('secret')->nullable();
            $t->text('webhook_secret')->nullable();
            $t->json('currencies');
            $t->timestamps();
        });
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $t->unsignedBigInteger('amount_minor');
            $t->char('currency', 3);
            $t->string('state', 24)->default('initialized')->index();
            $t->unsignedBigInteger('captured_minor')->default(0);
            $t->unsignedBigInteger('refunded_minor')->default(0);
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('reconciliation_required_at')->nullable()->index();
            $t->timestamps();
        });
        Schema::create('payment_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $t->string('gateway', 24);
            $t->uuid('reference')->unique();
            $t->char('idempotency_key', 64)->unique();
            $t->string('provider_reference', 191)->nullable();
            $t->string('state', 24)->default('initialized');
            $t->string('provider_status', 80)->nullable();
            $t->json('action')->nullable();
            $t->string('failure_code', 80)->nullable();
            $t->string('failure_reason', 240)->nullable();
            $t->timestamp('initiated_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['gateway', 'provider_reference']);
            $t->index(['state', 'created_at']);
        });
        Schema::create('payment_webhook_events', function (Blueprint $t) {
            $t->id();
            $t->string('gateway', 24);
            $t->string('external_event_id', 191);
            $t->string('event_type', 120);
            $t->string('provider_reference', 191)->nullable();
            $t->char('payload_hash', 64);
            $t->boolean('signature_verified');
            $t->string('processing_state', 24)->default('received');
            $t->unsignedSmallInteger('retry_count')->default(0);
            $t->string('error_code', 80)->nullable();
            $t->timestamp('received_at');
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
            $t->unique(['gateway', 'external_event_id']);
            $t->index(['processing_state', 'received_at']);
        });
        Schema::create('refunds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('payment_id')->constrained()->restrictOnDelete();
            $t->foreignId('order_id')->constrained()->restrictOnDelete();
            $t->string('gateway', 24);
            $t->uuid('reference')->unique();
            $t->string('provider_reference', 191)->nullable();
            $t->unsignedBigInteger('amount_minor');
            $t->char('currency', 3);
            $t->string('state', 24)->default('requested')->index();
            $t->string('reason', 240);
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->char('idempotency_key', 64)->unique();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['gateway', 'provider_reference']);
        });
        Schema::create('payment_disputes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('payment_id')->constrained()->restrictOnDelete();
            $t->string('gateway', 24);
            $t->string('provider_reference', 191);
            $t->unsignedBigInteger('amount_minor');
            $t->char('currency', 3);
            $t->string('state', 24);
            $t->string('reason', 120)->nullable();
            $t->timestamp('respond_by')->nullable();
            $t->timestamp('opened_at');
            $t->timestamp('closed_at')->nullable();
            $t->timestamps();
            $t->unique(['gateway', 'provider_reference']);
            $t->index(['state', 'updated_at']);
        });
        Schema::table('coupon_redemptions', fn (Blueprint $t) => $t->timestamp('consumed_at')->nullable());
    }

    public function down(): void
    {
        Schema::table('coupon_redemptions', fn (Blueprint $t) => $t->dropColumn('consumed_at'));
        foreach (['payment_disputes', 'refunds', 'payment_webhook_events', 'payment_attempts', 'payments', 'payment_gateway_configurations'] as $table) {
            Schema::dropIfExists($table);
        }
        $ids = DB::table('permissions')->whereIn('name', ['commerce.payments.manage', 'commerce.refunds.manage'])->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
