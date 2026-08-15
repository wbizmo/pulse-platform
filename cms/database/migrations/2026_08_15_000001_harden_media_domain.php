<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedInteger('width')->nullable()->after('size');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->index(['type', 'created_at']);
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('featured_media_id')->nullable()->after('author_id')->constrained('media')->restrictOnDelete();
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('featured_image', 'legacy_featured_image');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('featured_media_id')->nullable()->after('user_id')->constrained('media')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['featured_media_id']);
            $table->dropColumn('featured_media_id');
        });
        Schema::table('posts', fn (Blueprint $table) => $table->renameColumn('legacy_featured_image', 'featured_image'));
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['featured_media_id']);
            $table->dropColumn('featured_media_id');
        });
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['type', 'created_at']);
            $table->dropColumn(['width', 'height']);
        });
    }
};
