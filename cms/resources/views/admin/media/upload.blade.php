@extends('admin.layouts.app', [
    'title' => 'Upload Media',
    'heading' => 'Upload Media',
    'subheading' => 'Upload images, videos, PDFs, and documents to the Pulse media library.'
])

@section('content')
    @if ($errors->any())
        <div class="pulse-alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="pulse-settings-form">
        @csrf

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Select Files</h3>
                <p>Upload files up to 20MB each. Supported files include images, PDFs, videos, and common documents.</p>
            </div>

            <label class="pulse-upload-drop">
                <span class="material-symbols-rounded">cloud_upload</span>
                <strong>Choose files to upload</strong>
                <small>Click here and select one or more files from your computer.</small>
                <input type="file" name="files[]" multiple required>
            </label>
        </section>

        <div class="pulse-save-bar">
            <div>
                <strong>Media Manager</strong>
                <span>Files will be stored on the public disk and listed in the media library.</span>
            </div>

            <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
                <span>Upload files</span>
                <span class="material-symbols-rounded">upload</span>
            </button>
        </div>
    </form>
@endsection
