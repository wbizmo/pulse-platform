<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('canonical_url', 2048)->nullable()->after('meta_keywords');
            $table->string('og_title')->nullable()->after('canonical_url');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('twitter_title')->nullable()->after('og_image');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->string('twitter_image', 2048)->nullable()->after('twitter_description');
        });
    }

    public function down(): void
    {
        Schema::table('posts', fn (Blueprint $table) => $table->dropColumn(['meta_keywords', 'canonical_url', 'og_title', 'og_description', 'twitter_title', 'twitter_description', 'twitter_image']));
    }
};
