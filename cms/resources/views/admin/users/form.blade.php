@extends('admin.layouts.app')
@section('content')
<div class="pulse-card"><h2>{{ $managedUser->exists ? 'Edit user' : 'Add user' }}</h2>
@if($errors->any())<div role="alert"><strong>Correct the highlighted fields.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $managedUser->exists ? route('admin.users.update',$managedUser) : route('admin.users.store') }}">@csrf @if($managedUser->exists)@method('PUT')@endif
<label>Name<input name="name" value="{{ old('name',$managedUser->name) }}" required></label><label>Email<input type="email" name="email" value="{{ old('email',$managedUser->email) }}" required></label>
<label>Password<input type="password" name="password" {{ $managedUser->exists ? '' : 'required' }} autocomplete="new-password"></label><label>Confirm password<input type="password" name="password_confirmation" {{ $managedUser->exists ? '' : 'required' }} autocomplete="new-password"></label>
<label>Status<select name="status"><option value="active" @selected(old('status',$managedUser->status ?? 'active')==='active')>Active</option><option value="inactive" @selected(old('status',$managedUser->status)==='inactive')>Inactive</option></select></label>
<fieldset><legend>Roles</legend>@foreach($roles as $role)<label><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id,old('roles',$managedUser->roles->modelKeys() ?? [])))> {{ $role->label }}</label>@endforeach</fieldset><button class="pulse-btn" type="submit">Save user</button></form></div>
@endsection
