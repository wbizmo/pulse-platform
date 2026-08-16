<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->foreign('theme_id')->references('id')->on('themes')->cascadeOnDelete();
        });
        Schema::table('plugin_settings', function (Blueprint $table) {
            $table->foreign('plugin_id')->references('id')->on('plugins')->cascadeOnDelete();
        });
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreign('menu_id')->references('id')->on('menus')->cascadeOnDelete();
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', fn (Blueprint $table) => $table->dropForeign(['theme_id']));
        Schema::table('plugin_settings', fn (Blueprint $table) => $table->dropForeign(['plugin_id']));
        Schema::table('menu_items', fn (Blueprint $table) => $table->dropForeign(['menu_id']));
        Schema::table('posts', fn (Blueprint $table) => $table->dropForeign(['category_id']));
    }
};
