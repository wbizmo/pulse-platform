<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', ['roles' => Role::withCount('users')->orderByDesc('is_system')->orderBy('label')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.roles.form', ['managedRole' => new Role, 'permissions' => $this->delegablePermissions()]);
    }

    public function store(StoreRoleRequest $request, RecordAudit $audit): RedirectResponse
    {
        $data = $request->validated();
        $names = $this->validateDelegation($data['permissions'] ?? []);
        DB::transaction(function () use ($data, $names, $request, $audit) {
            $role = Role::create(['name' => $data['name'], 'label' => $data['label']]);
            $role->permissions()->sync(Permission::whereIn('name', $names)->pluck('id'));
            $audit->execute($request->user(), 'role.created', $role, ['permissions' => $names]);
        });

        return redirect()->route('admin.roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role): View
    {
        abort_if($role->is_system, 403);

        return view('admin.roles.form', ['managedRole' => $role->load('permissions'), 'permissions' => $this->delegablePermissions()]);
    }

    public function update(UpdateRoleRequest $request, Role $role, RecordAudit $audit): RedirectResponse
    {
        $data = $request->validated();
        $names = $this->validateDelegation($data['permissions'] ?? []);
        DB::transaction(function () use ($role, $data, $names, $request, $audit) {
            $locked = Role::lockForUpdate()->findOrFail($role->id);
            abort_if($locked->is_system, 403);
            $locked->update(['label' => $data['label']]);
            $locked->permissions()->sync(Permission::whereIn('name', $names)->pluck('id'));
            $audit->execute($request->user(), 'role.updated', $locked, ['permissions' => $names]);
        });

        return redirect()->route('admin.roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Role $role, RecordAudit $audit): RedirectResponse
    {
        abort_if($role->is_system, 403);
        if ($role->users()->exists()) {
            throw ValidationException::withMessages(['role' => ['Reassign all users before deleting this role.']]);
        }
        DB::transaction(function () use ($role, $audit) {
            $audit->execute(auth()->user(), 'role.deleted', $role);
            $role->delete();
        });

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted.');
    }

    private function delegablePermissions()
    {
        $allowed = auth()->user()->permissionNames();

        return Permission::whereIn('name', $allowed)->orderBy('label')->get();
    }

    private function validateDelegation(array $names): array
    {
        $allowed = auth()->user()->permissionNames();
        if (array_diff($names, $allowed)) {
            throw ValidationException::withMessages(['permissions' => ['You cannot delegate permissions you do not hold.']]);
        }

        return $names;
    }
}
