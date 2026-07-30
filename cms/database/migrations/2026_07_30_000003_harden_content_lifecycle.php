<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->unsignedInteger('lock_version')->default(0);
            $table->index(['status', 'published_at']);
            $table->index('author_id');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
            $table->unsignedInteger('lock_version')->default(0);
            $table->index(['status', 'published_at']);
            $table->index('user_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])->default('draft')->change();
            $table->dropIndex(['status', 'published_at']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['category_id']);
            $table->dropColumn('lock_version');
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
            $table->dropIndex(['author_id']);
            $table->dropColumn('lock_version');
        });
    }
};
