@extends('admin.layouts.app')
@section('content')
<div class="pulse-card"><h2>{{ $managedRole->exists ? 'Edit role' : 'Add role' }}</h2>@if($errors->any())<div role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $managedRole->exists ? route('admin.roles.update',$managedRole) : route('admin.roles.store') }}">@csrf @if($managedRole->exists)@method('PUT')@endif
<label>Label<input name="label" value="{{ old('label',$managedRole->label) }}" required></label>@unless($managedRole->exists)<label>Machine name<input name="name" value="{{ old('name') }}" pattern="[A-Za-z0-9_-]+" required></label>@endunless
<fieldset><legend>Permissions</legend>@foreach($permissions as $permission)<label><input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name,old('permissions',$managedRole->permissions->pluck('name')->all() ?? [])))> {{ $permission->label }}</label>@endforeach</fieldset><button class="pulse-btn" type="submit">Save role</button></form></div>
@endsection
