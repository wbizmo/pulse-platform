@extends('admin.layouts.app', ['title' => 'Edit Post', 'heading' => 'Edit Post'])
@section('content')
    <x-pulse.page-header title="Edit post" description="Update content, taxonomy, publication, and search metadata." />
    <x-pulse.errors />
    <form method="POST" action="{{ route('admin.posts.update', $post) }}" class="p-form">@csrf @method('PUT')
        @include('admin.posts.partials.form', ['post' => $post, 'buttonText' => 'Save changes'])
    </form>
@endsection
