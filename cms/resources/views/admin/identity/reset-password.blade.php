@extends('admin.identity.layout', ['title' => 'Choose a new password'])
@section('identity-content')
<form method="POST" action="{{ route('admin.password.update') }}" class="pulse-form">@csrf<input type="hidden" name="token" value="{{ $token }}"><label>Email address<input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"></label><label>New password<input type="password" name="password" required autocomplete="new-password"></label><label>Confirm password<input type="password" name="password_confirmation" required autocomplete="new-password"></label><button class="pulse-btn pulse-btn-dark" type="submit">Reset password</button></form>
@endsection
