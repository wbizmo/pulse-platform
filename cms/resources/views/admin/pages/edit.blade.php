@extends('admin.layouts.app', ['title' => 'Edit Page', 'heading' => 'Edit Page'])
@section('content')
    <x-pulse.page-header title="Edit page" description="Update content, publishing, presentation, and search metadata." />
    <x-pulse.errors />
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="p-form">@csrf @method('PUT')
        @include('admin.pages.partials.form', ['page' => $page, 'buttonText' => 'Save changes'])
    </form>
@endsection
