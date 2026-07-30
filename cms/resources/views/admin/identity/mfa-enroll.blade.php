@extends('admin.identity.layout')
@section('title', 'Enroll authenticator')
@section('content')
<h1>Enroll your authenticator</h1>
<div class="alert alert-warning" role="alert">This secret is displayed once. Do not share or store it in an insecure location.</div>
<p>In your standards-based authenticator app, add this setup key:</p>
<p><code aria-label="Authenticator setup key">{{ $secret }}</code></p>
<p class="small">Authenticator URI for compatible password managers: <code class="text-break">{{ $uri }}</code></p>
<form method="POST" action="{{ route('admin.mfa.confirm') }}">@csrf<label for="code" class="form-label">Six-digit authentication code</label><input id="code" name="code" class="form-control" inputmode="numeric" autocomplete="one-time-code" required pattern="[0-9]{6}" aria-describedby="code-help"><div id="code-help" class="form-text">Enter the current code to verify setup.</div><button class="btn btn-primary mt-3" type="submit">Confirm and enable</button></form>
@endsection
