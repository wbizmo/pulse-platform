@extends('admin.layouts.app', [
    'title' => 'Page Builder',
    'heading' => 'Page Builder',
    'subheading' => $page->title
])

@section('content')
    <x-pulse.errors />

    @if ($legacyBuilderData)
        <div class="p-alert p-alert--warning" role="alert"><strong>Legacy layout preserved.</strong> It is not rendered because it does not satisfy Builder V4 security rules. Saving starts a new empty V4 document; export the database value first if recovery is required.</div>
    @endif
    <div id="pulseBuilderRecovery" class="p-alert p-alert--info" role="status" hidden>
        <span>A matching unsaved local draft is available.</span>
        <button type="button" class="p-button" data-builder-restore>Restore draft</button>
        <button type="button" class="p-button p-button--secondary" data-builder-discard-draft>Discard</button>
    </div>

    <form method="POST" action="{{ route('admin.pages.builder.update', $page) }}" class="p-module-builder-form">
        @csrf
        <input type="hidden" name="lock_version" value="{{ $page->lock_version }}">

        <section class="p-module-builder-shell">
            <aside class="p-module-builder-sidebar">
                <div class="p-card-head">
                    <h3>Blocks</h3>
                    <p>Add ready-made sections to the page.</p>
                </div>

                <div class="p-module-builder-block-list" aria-label="Block library">
                    @foreach($registry as $block)<button type="button" data-builder-add="{{ $block['type'] }}"><span class="material-symbols-rounded">add_box</span>{{ $block['label'] }}</button>@endforeach
                </div>
                <h3>Reusable templates</h3>
                <div class="p-module-builder-block-list">
                    @forelse($templates as $template)<button type="button" data-builder-template-document='@json($template->document)'>Insert {{ $template->name }}</button>@empty<p>No reusable templates yet.</p>@endforelse
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

                <div class="p-module-builder-top" aria-label="Responsive preview controls">
                    <button type="button" class="p-button p-button--secondary" data-builder-viewport="desktop">Desktop</button>
                    <button type="button" class="p-button p-button--secondary" data-builder-viewport="tablet">Tablet</button>
                    <button type="button" class="p-button p-button--secondary" data-builder-viewport="mobile">Mobile</button>
                    <a class="p-button p-button--secondary" href="{{ URL::temporarySignedRoute('admin.pages.preview', now()->addMinutes(15), ['page' => $page]) }}" target="_blank" rel="noopener">Secure preview</a>
                </div>

                <textarea
                    name="builder_data"
                    id="pulseBuilderData"
                    class="p-module-builder-json"
                    rows="18"
                >{{ old('builder_data', json_encode($document, JSON_PRETTY_PRINT)) }}</textarea>
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

    <form method="POST" action="{{ route('admin.builder.templates.store') }}" class="p-card p-stack">
        @csrf
        <label for="builder_template_name">Save current document as reusable snapshot template</label>
        <input id="builder_template_name" name="name" maxlength="120" required>
        <input type="hidden" name="builder_data" id="pulseBuilderTemplateData">
        <button class="p-button" type="submit">Create reusable template</button>
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
        window.PULSE_BUILDER = @json(['page' => $page->id, 'version' => $page->lock_version, 'registry' => $registry]);
    </script>
    <script src="{{ asset('js/builder.js') }}"></script>
@endsection
