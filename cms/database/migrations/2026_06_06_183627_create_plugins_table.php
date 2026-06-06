<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->default('1.0.0');
            $table->string('author')->default('Pulse CMS');
            $table->text('description')->nullable();
            $table->string('category')->default('core');
            $table->string('icon')->default('extension');

            $table->boolean('is_active')->default(false);
            $table->boolean('has_settings')->default(false);

            $table->json('requires')->nullable();
            $table->json('provides')->nullable();
            $table->json('permissions')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
