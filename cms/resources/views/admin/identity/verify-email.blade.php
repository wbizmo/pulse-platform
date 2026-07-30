@extends('admin.identity.layout', ['title' => 'Verify your email'])
@section('identity-content')
<p>Check your inbox and follow the signed link before accessing administration. The link expires and is rate limited.</p>
<form method="POST" action="{{ route('admin.verification.send') }}">@csrf<button class="pulse-btn pulse-btn-dark" type="submit">Send another link</button></form>
<form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="pulse-btn" type="submit">Sign out</button></form>
@endsection
