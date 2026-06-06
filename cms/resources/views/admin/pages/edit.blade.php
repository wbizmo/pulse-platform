@extends('admin.layouts.app', [
    'title' => 'Edit Page',
    'heading' => 'Edit Page',
    'subheading' => 'Update page content, publishing status, visibility, and SEO metadata.'
])

@section('content')
    @if (session('success'))
        <div class="pulse-success">
            <span class="material-symbols-rounded">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="pulse-editor-form">
        @csrf
        @method('PUT')

        @include('admin.pages.partials.form', [
            'page' => $page,
            'buttonText' => 'Save changes'
        ])
    </form>
@endsection
