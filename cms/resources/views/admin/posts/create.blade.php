@extends('admin.layouts.app', ['title' => 'Create Post', 'heading' => 'Create Post'])
@section('content')
    <x-pulse.page-header title="Create post" description="Write a post with taxonomy, publication, image, and search metadata." />
    <x-pulse.errors />
    <form method="POST" action="{{ route('admin.posts.store') }}" class="p-form">@csrf
        @include('admin.posts.partials.form', ['post' => null, 'buttonText' => 'Create post'])
    </form>
@endsection
