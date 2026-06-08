@php
    $post = $post ?? null;
    $selectedTags = old('tags', $post?->tags?->pluck('id')->toArray() ?? []);
@endphp

<div class="pulse-editor-grid">
    <section class="pulse-panel">
        <div class="pulse-panel-head">
            <h3>Post Content</h3>
            <p>Write the main blog post content and publishing information.</p>
        </div>

        <div class="pulse-form-grid">
            <label>
                <span>Title</span>
                <input type="text" name="title" value="{{ old('title', $post?->title) }}" required>
            </label>

            <label>
                <span>Slug</span>
                <input type="text" name="slug" value="{{ old('slug', $post?->slug) }}" placeholder="auto-generated-from-title">
            </label>

            <label>
                <span>Category</span>
                <select name="category_id">
                    <option value="">Uncategorized</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $post?->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Status</span>
                <select name="status">
                    <option value="draft" @selected(old('status', $post?->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $post?->status) === 'published')>Published</option>
                </select>
            </label>

            <label>
                <span>Published at</span>
                <input
                    type="datetime-local"
                    name="published_at"
                    value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}"
                >
            </label>

            <label>
                <span>Featured image URL</span>
                <input type="text" name="featured_image" value="{{ old('featured_image', $post?->featured_image) }}">
            </label>

            <label class="pulse-form-wide">
                <span>Excerpt</span>
                <textarea name="excerpt" rows="4">{{ old('excerpt', $post?->excerpt) }}</textarea>
            </label>

            <label class="pulse-form-wide">
                <span>Content</span>
                <textarea name="content" rows="14">{{ old('content', $post?->content) }}</textarea>
            </label>
        </div>
    </section>

    <aside class="pulse-editor-side">
        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Tags</h3>
                <p>Select tags for this post.</p>
            </div>

            <div class="pulse-checkbox-list">
                @forelse ($tags as $tag)
                    <label class="pulse-check-row">
                        <span>{{ $tag->name }}</span>
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags))>
                    </label>
                @empty
                    <p class="pulse-muted-note">No tags yet. Tags management will be added next.</p>
                @endforelse
            </div>
        </section>

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>SEO</h3>
                <p>Search and social preview metadata.</p>
            </div>

            <div class="pulse-form-grid pulse-form-grid-single">
                <label>
                    <span>Meta title</span>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $post?->meta_title) }}">
                </label>

                <label>
                    <span>Meta description</span>
                    <textarea name="meta_description" rows="4">{{ old('meta_description', $post?->meta_description) }}</textarea>
                </label>

                <label>
                    <span>Open Graph image</span>
                    <input type="text" name="og_image" value="{{ old('og_image', $post?->og_image) }}">
                </label>
            </div>
        </section>
    </aside>
</div>

<div class="pulse-save-bar">
    <div>
        <strong>Blog Editor</strong>
        <span>Save post content, category, tags, publishing status, and SEO metadata.</span>
    </div>

    <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
        <span>{{ $buttonText }}</span>
        <span class="material-symbols-rounded">save</span>
    </button>
</div>
