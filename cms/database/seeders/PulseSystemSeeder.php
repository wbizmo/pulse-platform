<?php

namespace Database\Seeders;

use App\Domain\Access\Permission as PermissionName;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PulseSystemSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (PermissionName::cases() as $permission) {
                Permission::query()->updateOrCreate(
                    ['name' => $permission->value],
                    ['label' => $permission->label()],
                );
            }

            $roles = [
                'super_admin' => ['Super administrator', true, PermissionName::values()],
                'admin' => ['Administrator', false, PermissionName::values()],
                'editor' => ['Editor', false, ['dashboard.view', 'pages.manage', 'posts.manage', 'taxonomy.manage', 'media.manage', 'menus.manage', 'seo.manage']],
                'author' => ['Author', false, ['dashboard.view', 'posts.manage', 'media.manage']],
            ];

            foreach ($roles as $name => [$label, $super, $permissions]) {
                $role = Role::query()->updateOrCreate(['name' => $name], [
                    'label' => $label,
                    'is_system' => true,
                    'is_super_admin' => $super,
                ]);
                $role->permissions()->sync(Permission::query()->whereIn('name', $permissions)->pluck('id'));
            }
        });
    }
}
