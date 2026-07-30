<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\EnsureRoleDelegation;
use App\Actions\Access\EnsureSuperAdministratorRemains;
use App\Actions\Access\RecordAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', ['users' => User::with('roles')->orderBy('name')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['managedUser' => new User, 'roles' => Role::orderBy('label')->get()]);
    }

    public function store(StoreUserRequest $request, EnsureRoleDelegation $delegation, RecordAudit $audit): RedirectResponse
    {
        $data = $request->validated();
        $roleIds = $delegation->execute($request->user(), $data['roles']);
        DB::transaction(function () use ($data, $roleIds, $request, $audit) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'], 'status' => $data['status'], 'role' => 'author']);
            $user->roles()->sync($roleIds);
            $audit->execute($request->user(), 'user.created', $user, ['roles' => $roleIds, 'status' => $data['status']]);
        });

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['managedUser' => $user->load('roles'), 'roles' => Role::orderBy('label')->get()]);
    }

    public function update(UpdateUserRequest $request, User $user, EnsureRoleDelegation $delegation, EnsureSuperAdministratorRemains $guard, RecordAudit $audit): RedirectResponse
    {
        $data = $request->validated();
        $roleIds = $delegation->execute($request->user(), $data['roles']);
        DB::transaction(function () use ($user, $data, $roleIds, $request, $guard, $audit) {
            $locked = User::lockForUpdate()->findOrFail($user->id);
            $guard->execute($locked, $data['status'] === 'active', $roleIds);
            $attributes = ['name' => $data['name'], 'email' => $data['email'], 'status' => $data['status']];
            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }
            $locked->update($attributes);
            $locked->roles()->sync($roleIds);
            $audit->execute($request->user(), 'user.updated', $locked, ['roles' => $roleIds, 'status' => $data['status']]);
        });

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user, EnsureSuperAdministratorRemains $guard, RecordAudit $audit): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot delete your own account.');
        DB::transaction(function () use ($user, $guard, $audit) {
            $locked = User::lockForUpdate()->findOrFail($user->id);
            $guard->execute($locked, false, []);
            $audit->execute(auth()->user(), 'user.deleted', $locked);
            $locked->delete();
        });

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
