<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->unsignedSmallInteger('manifest_version')->default(1);
            $table->unsignedSmallInteger('settings_schema_version')->default(1);
            $table->string('active_slot')->nullable()->unique();
            $table->timestamp('retired_at')->nullable()->index();
        });
        $active = DB::table('themes')->where('is_active', true)->orderBy('id')->value('id');
        if ($active) {
            DB::table('themes')->where('id', $active)->update(['active_slot' => 'active']);
        }
        DB::table('themes')->where('is_active', true)->when($active, fn ($q) => $q->where('id', '!=', $active))->update(['is_active' => false]);
        Schema::create('theme_activation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('previous_theme_id')->nullable()->constrained('themes')->restrictOnDelete();
            $table->foreignId('next_theme_id')->constrained('themes')->restrictOnDelete();
            $table->string('previous_version')->nullable();
            $table->string('next_version');
            $table->unsignedSmallInteger('settings_schema_version');
            $table->json('previous_settings')->nullable();
            $table->json('next_settings');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rolled_back_from_id')->nullable()->constrained('theme_activation_history')->nullOnDelete();
            $table->timestamps();
            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_activation_history');
        Schema::table('themes', function (Blueprint $table) {
            $table->dropUnique(['active_slot']);
            $table->dropIndex(['retired_at']);
        });
        Schema::table('themes', fn (Blueprint $table) => $table->dropColumn(['manifest_version', 'settings_schema_version', 'active_slot', 'retired_at']));
    }
};
