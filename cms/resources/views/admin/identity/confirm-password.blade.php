@extends('admin.identity.layout', ['title' => 'Confirm your password'])
@section('identity-content')
<p>Confirm your password before changing credentials or revoking sessions.</p><form method="POST" action="{{ route('admin.password.confirm.store') }}" class="pulse-form">@csrf<label>Password<input type="password" name="password" required autofocus autocomplete="current-password"></label><button class="pulse-btn pulse-btn-dark" type="submit">Confirm</button></form>
@endsection
