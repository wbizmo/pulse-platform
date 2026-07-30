@extends('admin.layouts.app', [
    'title' => 'Page Builder',
    'heading' => 'Page Builder',
    'subheading' => $page->title
])

@section('content')
    <x-pulse.errors />

    <form method="POST" action="{{ route('admin.pages.builder.update', $page) }}" class="p-module-builder-form">
        @csrf

        <section class="p-module-builder-shell">
            <aside class="p-module-builder-sidebar">
                <div class="p-card-head">
                    <h3>Blocks</h3>
                    <p>Add ready-made sections to the page.</p>
                </div>

                <div class="p-module-builder-block-list">
                    <button type="button" data-builder-add="hero">
                        <span class="material-symbols-rounded">auto_awesome</span>
                        Hero
                    </button>

                    <button type="button" data-builder-add="text">
                        <span class="material-symbols-rounded">notes</span>
                        Text
                    </button>

                    <button type="button" data-builder-add="image">
                        <span class="material-symbols-rounded">image</span>
                        Image
                    </button>

                    <button type="button" data-builder-add="video">
                        <span class="material-symbols-rounded">smart_display</span>
                        Video
                    </button>

                    <button type="button" data-builder-add="cta">
                        <span class="material-symbols-rounded">ads_click</span>
                        CTA
                    </button>

                    <button type="button" data-builder-add="features">
                        <span class="material-symbols-rounded">grid_view</span>
                        Features
                    </button>

                    <button type="button" data-builder-add="stats">
                        <span class="material-symbols-rounded">bar_chart</span>
                        Stats
                    </button>

                    <button type="button" data-builder-add="accordion">
                        <span class="material-symbols-rounded">expand_circle_down</span>
                        Accordion
                    </button>

                    <button type="button" data-builder-add="testimonial">
                        <span class="material-symbols-rounded">format_quote</span>
                        Testimonial
                    </button>

                    <button type="button" data-builder-add="html">
                        <span class="material-symbols-rounded">code</span>
                        Custom HTML
                    </button>
                </div>
            </aside>

            <section class="p-module-builder-main">
                <div class="p-module-builder-top">
                    <div>
                        <h3>{{ $page->title }}</h3>
                        <p>Visual builder. Add, edit, duplicate, reorder, collapse, and import page sections.</p>
                    </div>

                    <a href="{{ route('admin.pages.edit', $page) }}" class="p-button p-button--secondary">
                        <span class="material-symbols-rounded">edit</span>
                        Page settings
                    </a>
                </div>

                @include('admin.builder.templates.index')

                <div id="pulseBuilderCanvas" class="p-module-builder-canvas"></div>

                <textarea
                    name="builder_data"
                    id="pulseBuilderData"
                    class="p-module-builder-json"
                    rows="18"
                >{{ old('builder_data', json_encode($page->builder_data ?? [], JSON_PRETTY_PRINT)) }}</textarea>
            </section>

            @include('admin.builder.sidebar')
        </section>

        <div class="p-module-save-bar">
            <div>
                <strong>Page Builder</strong>
                <span>Save builder layout blocks for frontend rendering.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save builder</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>

    <div class="p-module-media-picker" id="pulseMediaPicker" hidden>
        <div class="p-module-media-picker-backdrop" data-media-close></div>

        <section class="p-module-media-picker-panel">
            <div class="p-module-media-picker-head">
                <div>
                    <h3>Select Media</h3>
                    <p>Choose an uploaded image for the selected builder block.</p>
                </div>

                <button type="button" data-media-close>
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <div id="pulseMediaPickerGrid" class="p-module-media-picker-grid">
                <div class="p-empty">
                    <span class="material-symbols-rounded">hourglass_empty</span>
                    <h3>Loading media...</h3>
                </div>
            </div>
        </section>
    </div>

    @include('admin.builder.templates.modal')

    <script>
        window.PULSE_MEDIA_LIBRARY_URL = "{{ route('admin.media.library', ['type' => 'image']) }}";
    </script>

    <script src="{{ asset('js/media-picker.js') }}"></script>
    <script src="{{ asset('js/builder.js') }}"></script>
@endsection
