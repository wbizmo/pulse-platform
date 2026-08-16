@extends('admin.layouts.app',['heading'=>'Operations'])
@section('content')
<x-pulse.page-header title="Operations overview" description="Bounded, privileged runtime diagnostics." />
@include('admin.operations._nav')
<div class="p-grid p-grid--3">
<x-pulse.card><h2>Overall health</h2><p><x-pulse.badge :tone="$status->value === 'healthy' ? 'success' : 'warning'">{{ ucfirst($status->value) }}</x-pulse.badge></p></x-pulse.card>
<x-pulse.card><h2>Queue</h2><p>{{ $queue['pending'] ?? 'Unknown' }} pending; {{ $queue['failed'] ?? 'unknown' }} failed.</p></x-pulse.card>
<x-pulse.card><h2>Scheduler</h2><p>{{ $scheduler?->last_completed_at?->diffForHumans() ?? 'No heartbeat recorded' }}</p></x-pulse.card>
</div>
<x-pulse.card><h2>Checks</h2><div class="p-table-wrap"><table class="p-table"><caption class="sr-only">Operational health checks</caption><thead><tr><th>Check</th><th>Status</th><th>Summary</th></tr></thead><tbody>@foreach($results as $result)<tr><th>{{ $result->label }}</th><td>{{ ucfirst($result->status->value) }}</td><td>{{ $result->summary }}</td></tr>@endforeach</tbody></table></div></x-pulse.card>
@endsection
