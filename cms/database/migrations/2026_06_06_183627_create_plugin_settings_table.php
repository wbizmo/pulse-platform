<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugin_settings', function (Blueprint $table) {
            $table->id();

            // The legacy migration shares a timestamp with the parent-table
            // migration and is sorted first on case-sensitive filesystems.
            // The foreign key is attached after both tables exist.
            $table->foreignId('plugin_id');

            $table->string('key');
            $table->longText('value')->nullable();

            $table->timestamps();

            $table->unique(['plugin_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_settings');
    }
};
