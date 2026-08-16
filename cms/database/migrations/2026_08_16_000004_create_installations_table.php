<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->string('release', 64);
            $table->foreignId('installed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};
