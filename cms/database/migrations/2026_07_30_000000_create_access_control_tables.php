<?php

use App\Domain\Access\Permission as PermissionName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->boolean('is_system')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        $now = now();
        foreach (PermissionName::cases() as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission->value,
                'label' => $permission->label(),
            ]);
        }

        $roles = [
            'super_admin' => ['Super administrator', true, true, PermissionName::cases()],
            'admin' => ['Administrator', true, false, PermissionName::cases()],
            'editor' => ['Editor', true, false, [PermissionName::ViewDashboard, PermissionName::ManagePages, PermissionName::ManagePosts, PermissionName::ManageTaxonomy, PermissionName::ManageMedia, PermissionName::ManageMenus, PermissionName::ManageSeo]],
            'author' => ['Author', true, false, [PermissionName::ViewDashboard, PermissionName::ManagePosts, PermissionName::ManageMedia]],
        ];

        foreach ($roles as $name => [$label, $system, $super, $permissions]) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => $name,
                'label' => $label,
                'is_system' => $system,
                'is_super_admin' => $super,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('permission_role')->insert(array_map(fn (PermissionName $permission) => [
                'permission_id' => DB::table('permissions')->where('name', $permission->value)->value('id'),
                'role_id' => $roleId,
            ], $permissions));
        }

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            $roleId = DB::table('roles')->where('name', $user->role)->value('id')
                ?? DB::table('roles')->where('name', 'author')->value('id');
            DB::table('role_user')->insertOrIgnore(['role_id' => $roleId, 'user_id' => $user->id]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
