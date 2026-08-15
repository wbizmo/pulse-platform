<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['main', 'footer'] as $location) {
            $keep = DB::table('menus')->where('location', $location)->where('is_active', true)->orderBy('id')->value('id');
            DB::table('menus')->where('location', $location)->where('is_active', true)->when($keep, fn ($query) => $query->where('id', '!=', $keep))->update(['is_active' => false]);
        }
        DB::table('menu_items')->where('type', 'page')->whereNull('page_id')->delete();
        DB::table('menu_items')->where('type', 'page')->update(['url' => null]);
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->dropForeign(['page_id']);
            $table->foreign('page_id')->references('id')->on('pages')->restrictOnDelete();
            $table->index(['menu_id', 'is_active', 'sort_order', 'id'], 'menu_items_public_order_idx');
        });
        Schema::table('menus', function (Blueprint $table): void {
            $table->string('active_singleton_location')->nullable()->unique();
            $table->index(['location', 'is_active', 'id'], 'menus_location_active_idx');
        });
        DB::table('menus')->whereIn('location', ['main', 'footer'])->where('is_active', true)->update(['active_singleton_location' => DB::raw('location')]);
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->dropIndex('menu_items_public_order_idx');
            $table->dropForeign(['page_id']);
            $table->foreign('page_id')->references('id')->on('pages')->nullOnDelete();
        });
        Schema::table('menus', function (Blueprint $table): void {
            $table->dropUnique(['active_singleton_location']);
            $table->dropIndex('menus_location_active_idx');
            $table->dropColumn('active_singleton_location');
        });
    }
};
