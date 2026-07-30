@extends('admin.identity.layout', ['title' => 'Reset password'])
@section('identity-content')
<p>Enter your email address. To protect account privacy, the response is the same whether or not an eligible account exists.</p>
<form method="POST" action="{{ route('admin.password.email') }}" class="pulse-form">@csrf<label>Email address<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"></label><button class="pulse-btn pulse-btn-dark" type="submit">Send reset link</button></form>
<p><a href="{{ route('admin.login') }}">Return to sign in</a></p>
@endsection
