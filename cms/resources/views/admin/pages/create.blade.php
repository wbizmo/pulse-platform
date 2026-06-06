@extends('admin.layouts.app', [
    'title' => 'Create Page',
    'heading' => 'Create Page',
    'subheading' => 'Add a new page with content, template controls, and SEO details.'
])

@section('content')
    <form method="POST" action="{{ route('admin.pages.store') }}" class="pulse-editor-form">
        @csrf

        @include('admin.pages.partials.form', [
            'page' => null,
            'buttonText' => 'Create page'
        ])
    </form>
@endsection
