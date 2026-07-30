@extends('admin.layouts.app')
@section('content')
@if(session('status'))<div class="pulse-card" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="pulse-card" role="alert">{{ $errors->first() }}</div>@endif
<div class="pulse-card"><div class="pulse-section-header"><h2>Roles</h2><a class="pulse-btn" href="{{ route('admin.roles.create') }}">Add role</a></div><table class="pulse-table"><thead><tr><th>Role</th><th>Type</th><th>Users</th><th>Actions</th></tr></thead><tbody>@foreach($roles as $role)<tr><td>{{ $role->label }}</td><td>{{ $role->is_system ? 'Protected system role' : 'Custom role' }}</td><td>{{ $role->users_count }}</td><td>@if(!$role->is_system)<a href="{{ route('admin.roles.edit',$role) }}">Edit</a><form class="pulse-inline-form" method="POST" action="{{ route('admin.roles.destroy',$role) }}">@csrf @method('DELETE')<button type="submit">Delete</button></form>@else<span>Managed by Pulse</span>@endif</td></tr>@endforeach</tbody></table>{{ $roles->links() }}</div>
@endsection
