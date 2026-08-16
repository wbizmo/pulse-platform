@extends('admin.layouts.app', ['title' => $manifest['name'].' settings', 'heading' => $manifest['name'].' settings'])
@section('content')
<x-pulse.page-header :title="$manifest['name'].' settings'" description="Only manifest-defined settings are accepted." />
<form method="POST" action="{{ route('admin.plugins.settings.update', $plugin) }}">@csrf
@foreach($manifest['settings'] as $key => $definition)
<div class="p-field"><label for="setting-{{ $key }}">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
@if($definition['type'] === 'boolean')<input type="hidden" name="{{ $key }}" value="0"><input id="setting-{{ $key }}" type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $settings[$key] ?? $definition['default']))>
@elseif($definition['type'] === 'enum')<select id="setting-{{ $key }}" name="{{ $key }}">@foreach($definition['values'] as $choice)<option value="{{ $choice }}" @selected(old($key, $settings[$key] ?? $definition['default']) === $choice)>{{ ucfirst($choice) }}</option>@endforeach</select>
@else<input id="setting-{{ $key }}" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? $definition['default']) }}" maxlength="{{ $definition['max'] }}">
@endif @error($key)<p role="alert">{{ $message }}</p>@enderror</div>
@endforeach
<x-pulse.button type="submit">Save settings</x-pulse.button>
</form>
@endsection
