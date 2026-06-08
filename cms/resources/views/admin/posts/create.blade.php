@extends('admin.layouts.app', [
    'title' => 'Create Post',
    'heading' => 'Create Post',
    'subheading' => 'Write a new blog post with category, tags, featured image, and SEO fields.'
])

@section('content')
    @if ($errors->any())
        <div class="pulse-alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.posts.store') }}" class="pulse-editor-form">
        @csrf

        @include('admin.posts.partials.form', [
            'post' => null,
            'buttonText' => 'Create post'
        ])
    </form>
@endsection
