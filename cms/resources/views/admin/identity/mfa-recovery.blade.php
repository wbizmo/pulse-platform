@extends('admin.identity.layout')
@section('title', 'Save recovery codes')
@section('content')
<h1>Save your recovery codes</h1><div class="alert alert-warning" role="alert">Each code works once. These codes will never be shown again. Store them offline in a secure place.</div>
<ul aria-label="Recovery codes">@foreach($codes as $code)<li><code>{{ $code }}</code></li>@endforeach</ul>
<a class="btn btn-primary" href="{{ route('admin.mfa.show') }}">I have saved these codes</a>
@endsection
