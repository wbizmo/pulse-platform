@extends('admin.layouts.app', [
    'title' => 'Page Builder',
    'heading' => 'Page Builder',
    'subheading' => $page->title
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

    <form method="POST" action="{{ route('admin.pages.builder.update', $page) }}" class="pulse-builder-form">
        @csrf

        <section class="pulse-builder-shell">
            <aside class="pulse-builder-sidebar">
                <div class="pulse-panel-head">
                    <h3>Blocks</h3>
                    <p>Add ready-made sections to the page.</p>
                </div>

                <div class="pulse-builder-block-list">
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

            <section class="pulse-builder-main">
                <div class="pulse-builder-top">
                    <div>
                        <h3>{{ $page->title }}</h3>
                        <p>Build structured page sections. This v1 saves blocks as JSON.</p>
                    </div>

                    <a href="{{ route('admin.pages.edit', $page) }}" class="pulse-inline-btn pulse-inline-btn-soft">
                        <span class="material-symbols-rounded">edit</span>
                        Page settings
                    </a>
                </div>

                <div id="pulseBuilderCanvas" class="pulse-builder-canvas"></div>

                <textarea
                    name="builder_data"
                    id="pulseBuilderData"
                    class="pulse-builder-json"
                    rows="18"
                >{{ old('builder_data', json_encode($page->builder_data ?? [], JSON_PRETTY_PRINT)) }}</textarea>
            </section>
        </section>

        <div class="pulse-save-bar">
            <div>
                <strong>Page Builder</strong>
                <span>Save builder layout blocks for frontend rendering.</span>
            </div>

            <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
                <span>Save builder</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>

    <script src="{{ asset('js/builder.js') }}"></script>
@endsection
