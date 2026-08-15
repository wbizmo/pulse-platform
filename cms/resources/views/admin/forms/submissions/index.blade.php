@extends('admin.layouts.app', ['title' => 'Submissions', 'heading' => 'Submissions'])

@section('content')
    <x-pulse.page-header title="Submissions" :description="$form->name" />
    <x-pulse.card>
        <x-pulse.table>
            <x-slot:head>
                <tr><th>ID</th><th>Received</th><th>Action</th></tr>
            </x-slot:head>
            @forelse ($submissions as $submission)
                <tr>
                    <td>#{{ $submission->id }}</td>
                    <td>{{ $submission->created_at->toDayDateTimeString() }}</td>
                    <td><x-pulse.button href="{{ route('admin.forms.submissions.show', [$form, $submission]) }}" variant="secondary">View</x-pulse.button></td>
                </tr>
            @empty
                <tr><td colspan="3">No submissions.</td></tr>
            @endforelse
        </x-pulse.table>
        <x-pulse.pagination :paginator="$submissions" />
    </x-pulse.card>
@endsection
