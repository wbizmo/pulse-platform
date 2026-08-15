<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('success_message', 500)->default('Thank you. Your response has been received.');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index(['is_active', 'slug']);
        });
        Schema::create('form_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('label', 120);
            $table->string('type', 20);
            $table->string('help', 500)->nullable();
            $table->string('placeholder', 200)->nullable();
            $table->boolean('required')->default(false);
            $table->unsignedSmallInteger('sort_order');
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->unique(['form_id', 'key']);
            $table->unique(['form_id', 'sort_order']);
        });
        Schema::create('form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained()->restrictOnDelete();
            $table->json('values');
            $table->json('field_snapshot');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['form_id', 'created_at', 'id']);
        });
        DB::table('permissions')->insertOrIgnore(['name' => 'forms.manage', 'label' => 'Manage forms and submissions']);
        $permission = DB::table('permissions')->where('name', 'forms.manage')->value('id');
        $roles = DB::table('roles')->whereIn('name', ['super_admin', 'editor'])->pluck('id');
        foreach ($roles as $role) {
            DB::table('permission_role')->insertOrIgnore(['permission_id' => $permission, 'role_id' => $role]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
        $id = DB::table('permissions')->where('name', 'forms.manage')->value('id');
        if ($id) {
            DB::table('permission_role')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();
        }
    }
};
