@extends('admin.layouts.app', [
    'title' => 'Upload Media',
    'heading' => 'Upload Media',
    'subheading' => 'Upload safely validated raster images to the Pulse media library.'
])

@section('content')
    <x-pulse.errors />

    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="p-module-settings-form">
        @csrf

        <section class="p-card">
            <div class="p-card-head">
                <h3>Select Files</h3>
                <p>Upload up to 10 JPEG, PNG, WebP, or GIF images. Each image may be up to {{ config('media.max_kilobytes') }} KB and 10,000 pixels per side. SVG is not accepted.</p>
            </div>

            <label class="p-module-upload-drop">
                <span class="material-symbols-rounded">cloud_upload</span>
                <strong>Choose files to upload</strong>
                <small>Click here and select one or more files from your computer.</small>
                <input type="file" name="files[]" multiple required accept="image/jpeg,image/png,image/webp,image/gif">
            </label>
        </section>

        <div class="p-module-save-bar">
            <div>
                <strong>Media Manager</strong>
                <span>Validated images receive opaque storage names and are listed in the media library.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Upload files</span>
                <span class="material-symbols-rounded">upload</span>
            </button>
        </div>
    </form>
@endsection
