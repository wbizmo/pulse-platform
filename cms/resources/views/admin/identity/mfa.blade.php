@extends('admin.identity.layout')
@section('title', 'Multi-factor authentication')
@section('content')
<h1>Multi-factor authentication</h1>
@if(session('warning')) <div class="alert alert-warning" role="alert">{{ session('warning') }}</div> @endif
@if(session('status')) <div class="alert alert-success" role="status">{{ session('status') }}</div> @endif
@if($errors->any()) <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div> @endif
@if(!$enabled)
<p>Privileged capabilities require an authenticator app. Confirm your password, then enroll. SMS is not supported.</p>
<form method="POST" action="{{ route('admin.mfa.enroll') }}">@csrf<button class="btn btn-primary" type="submit">Set up authenticator</button></form>
@else
<p>Your authenticator is enabled. Recovery codes cannot be retrieved after generation.</p>
<form method="POST" action="{{ route('admin.mfa.recovery.regenerate') }}">@csrf<button class="btn btn-secondary" type="submit">Generate new recovery codes</button></form>
<form method="POST" action="{{ route('admin.mfa.disable') }}" class="mt-3">@csrf @method('DELETE')<p class="text-danger">Disabling MFA immediately removes privileged access until you enroll again.</p><button class="btn btn-danger" type="submit">Disable MFA</button></form>
@endif
@endsection
