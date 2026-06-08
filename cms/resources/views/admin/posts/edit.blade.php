@extends('admin.layouts.app', [
    'title' => 'Edit Post',
    'heading' => 'Edit Post',
    'subheading' => 'Update blog content, publishing status, category, tags, and SEO metadata.'
])

@section('content')
    @if (session('success'))
        <div class="pulse-success">
            <span class="material-symbols-rounded">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="pulse-alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.posts.update', $post) }}" class="pulse-editor-form">
        @csrf
        @method('PUT')

        @include('admin.posts.partials.form', [
            'post' => $post,
            'buttonText' => 'Save changes'
        ])
    </form>
@endsection
