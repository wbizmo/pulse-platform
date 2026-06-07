<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('original_name');
            $table->string('file_name');
            $table->string('mime_type');
            $table->string('extension')->nullable();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('url');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('type')->default('file');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
