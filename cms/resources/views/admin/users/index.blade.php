@extends('admin.layouts.app')
@section('content')
@if(session('status'))<div class="pulse-card" role="status">{{ session('status') }}</div>@endif
<div class="pulse-card"><div class="pulse-section-header"><h2>Users</h2><a class="pulse-btn" href="{{ route('admin.users.create') }}">Add user</a></div>
<table class="pulse-table"><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Roles</th><th>Actions</th></tr></thead><tbody>
@foreach($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ ucfirst($user->status) }}</td><td>{{ $user->roles->pluck('label')->join(', ') }}</td><td><a href="{{ route('admin.users.edit',$user) }}">Edit</a> @if(!$user->is(auth()->user()))<form method="POST" action="{{ route('admin.users.destroy',$user) }}" class="pulse-inline-form">@csrf @method('DELETE')<button type="submit">Delete</button></form>@endif</td></tr>@endforeach
</tbody></table>{{ $users->links() }}</div>
@endsection
