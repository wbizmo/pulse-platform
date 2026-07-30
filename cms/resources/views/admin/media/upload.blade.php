@extends('admin.layouts.app', [
    'title' => 'Upload Media',
    'heading' => 'Upload Media',
    'subheading' => 'Upload images, videos, PDFs, and documents to the Pulse media library.'
])

@section('content')
    <x-pulse.errors />

    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="p-module-settings-form">
        @csrf

        <section class="p-card">
            <div class="p-card-head">
                <h3>Select Files</h3>
                <p>Upload files up to 20MB each. Supported files include images, PDFs, videos, and common documents.</p>
            </div>

            <label class="p-module-upload-drop">
                <span class="material-symbols-rounded">cloud_upload</span>
                <strong>Choose files to upload</strong>
                <small>Click here and select one or more files from your computer.</small>
                <input type="file" name="files[]" multiple required>
            </label>
        </section>

        <div class="p-module-save-bar">
            <div>
                <strong>Media Manager</strong>
                <span>Files will be stored on the public disk and listed in the media library.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Upload files</span>
                <span class="material-symbols-rounded">upload</span>
            </button>
        </div>
    </form>
@endsection
