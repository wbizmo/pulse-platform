@extends('admin.layouts.app', ['title' => 'Plugins', 'heading' => 'Plugins'])
@section('content')
<x-pulse.page-header title="First-party plugins" description="Activate only code-defined, compatible Pulse extensions." />
@if($errors->any())<x-pulse.card><div role="alert"><strong>Plugin change was refused.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></x-pulse.card>@endif
<div class="p-grid">
@foreach($manifests as $slug => $manifest)
@php($plugin = $plugins->get($slug))
<x-pulse.card>
<h2>{{ $manifest['name'] }} <small>v{{ $manifest['version'] }}</small></h2>
<p>{{ $manifest['description'] }}</p>
<p><strong>Status:</strong> {{ $plugin?->is_active ? 'Active' : 'Inactive' }}</p>
<p><strong>Compatibility:</strong> Pulse {{ $manifest['compatibility']['pulse'] }}</p>
<p><strong>Provides:</strong> {{ implode(', ', $manifest['provides']) }}</p>
<p><strong>Dependencies:</strong> {{ $manifest['requires'] ? collect($manifest['requires'])->map(fn($version,$dependency) => "$dependency $version")->implode(', ') : 'None' }}</p>
@if($plugin)
<form method="POST" action="{{ $plugin->is_active ? route('admin.plugins.deactivate', $plugin) : route('admin.plugins.activate', $plugin) }}">@csrf
<x-pulse.button type="submit">{{ $plugin->is_active ? 'Deactivate' : 'Activate' }}</x-pulse.button>
</form>
@if($manifest['settings'])<a href="{{ route('admin.plugins.settings', $plugin) }}">Configure settings</a>@endif
@else<p role="alert">Persisted runtime state is unavailable; activation is closed.</p>@endif
</x-pulse.card>
@endforeach
</div>
@endsection
