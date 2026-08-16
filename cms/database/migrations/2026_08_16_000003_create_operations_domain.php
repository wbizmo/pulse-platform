<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_states', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('last_completed_at')->nullable()->index();
            $table->string('status', 24)->default('unknown');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('operations_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('disk', 40)->default('local');
            $table->string('path', 255);
            $table->unsignedInteger('row_count')->default(0);
            $table->string('status', 24)->default('pending');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::table('audit_logs', fn (Blueprint $table) => $table->index(['created_at', 'action']));
        Schema::table('payment_webhook_events', fn (Blueprint $table) => $table->index(['signature_verified', 'processed_at', 'received_at'], 'webhook_operations_state_idx'));
    }

    public function down(): void
    {
        Schema::table('payment_webhook_events', fn (Blueprint $table) => $table->dropIndex('webhook_operations_state_idx'));
        Schema::table('audit_logs', fn (Blueprint $table) => $table->dropIndex(['created_at', 'action']));
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('operations_exports');
        Schema::dropIfExists('operational_states');
    }
};
