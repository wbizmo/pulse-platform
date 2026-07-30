@extends('admin.identity.layout')
@section('title', 'Authentication challenge')
@section('content')
<h1>Authentication challenge</h1><p>Enter a current six-digit authenticator code or one unused recovery code.</p>
@if($errors->any()) <div class="alert alert-danger" role="alert">{{ $errors->first('code') }}</div> @endif
<form method="POST" action="{{ route('admin.mfa.verify') }}">@csrf<label for="code" class="form-label">Authentication or recovery code</label><input id="code" name="code" class="form-control" autocomplete="one-time-code" required autofocus><button class="btn btn-primary mt-3" type="submit">Verify identity</button></form>
<form method="POST" action="{{ route('admin.logout') }}" class="mt-3">@csrf<button class="btn btn-link" type="submit">Sign out</button></form>
@endsection
