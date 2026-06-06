<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('author')->after('password');
            $table->string('status')->default('active')->after('role');
            $table->string('avatar')->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('avatar');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'status',
                'avatar',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
