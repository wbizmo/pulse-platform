@extends('frontend.layout')

@section('content')
    @if ($page->builder_data)
        @include('frontend.builder.render', [
            'blocks' => $page->builder_data,
        ])
    @else
        <section class="pulse-hero">
            <div class="pulse-site-container">
                <span class="pulse-eyebrow">
                    {{ $theme?->name ?? 'Pulse CMS' }}
                </span>

                <h1>{{ $page->title }}</h1>

                @if ($page->meta_description)
                    <p>{{ $page->meta_description }}</p>
                @else
                    <p>{{ $settings['site_tagline'] ?? 'Build pages, activate themes, manage plugins, and publish a complete site with Pulse CMS.' }}</p>
                @endif
            </div>
        </section>

        <section class="pulse-page-content">
            <div class="pulse-site-container pulse-content-card">
                @if ($page->content)
                    {!! nl2br(e($page->content)) !!}
                @else
                    <p>This page is ready for content. Edit it from the Pulse CMS admin dashboard.</p>
                @endif
            </div>
        </section>
    @endif
@endsection
