@extends('admin.layouts.app', ['title' => 'Create Page', 'heading' => 'Create Page'])
@section('content')
    <x-pulse.page-header title="Create page" description="Add content, presentation controls, and search metadata." />
    <x-pulse.errors />
    <form method="POST" action="{{ route('admin.pages.store') }}" class="p-form">@csrf
        @include('admin.pages.partials.form', ['page' => null, 'buttonText' => 'Create page'])
    </form>
@endsection
