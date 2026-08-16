<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            // The parent migration has the same legacy timestamp and sorts
            // after this file; the constraint is reconciled once both exist.
            $table->foreignId('menu_id');
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();

            $table->string('label');
            $table->string('type')->default('custom');
            $table->string('url')->nullable();
            $table->string('target')->default('_self');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
