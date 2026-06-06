@php
    $page = $page ?? null;
@endphp

@if ($errors->any())
    <div class="pulse-alert">
        {{ $errors->first() }}
    </div>
@endif

<div class="pulse-editor-grid">
    <section class="pulse-panel">
        <div class="pulse-panel-head">
            <h3>Page Content</h3>
            <p>Basic page information and body content. The visual builder will use this foundation later.</p>
        </div>

        <div class="pulse-form-grid">
            <label>
                <span>Title</span>
                <input type="text" name="title" value="{{ old('title', $page?->title) }}" required>
            </label>

            <label>
                <span>Slug</span>
                <input type="text" name="slug" value="{{ old('slug', $page?->slug) }}" placeholder="auto-generated-from-title">
            </label>

            <label>
                <span>Status</span>
                <select name="status">
                    <option value="draft" @selected(old('status', $page?->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $page?->status) === 'published')>Published</option>
                </select>
            </label>

            <label>
                <span>Template</span>
                <select name="template">
                    @foreach (['default', 'landing', 'full-width', 'blog', 'shop', 'school', 'portfolio'] as $template)
                        <option value="{{ $template }}" @selected(old('template', $page?->template ?? 'default') === $template)>
                            {{ ucfirst(str_replace('-', ' ', $template)) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="pulse-form-wide">
                <span>Content</span>
                <textarea name="content" rows="12" placeholder="Write page content here...">{{ old('content', $page?->content) }}</textarea>
            </label>
        </div>
    </section>

    <aside class="pulse-editor-side">
        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>Page Settings</h3>
                <p>Control page behavior and frontend visibility.</p>
            </div>

            <div class="pulse-toggle-list">
                @php
                    $toggles = [
                        'is_homepage' => 'Set as homepage',
                        'is_blog_page' => 'Set as blog page',
                        'show_header' => 'Show header',
                        'show_footer' => 'Show footer',
                    ];
                @endphp

                @foreach ($toggles as $key => $label)
                    <label class="pulse-toggle-row">
                        <span>{{ $label }}</span>

                        <span class="pulse-switch">
                            <input
                                type="checkbox"
                                name="{{ $key }}"
                                value="1"
                                @checked(old($key, $page?->{$key} ?? in_array($key, ['show_header', 'show_footer'])))
                            >

                            <span class="pulse-switch-track">
                                <span class="pulse-switch-thumb"></span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="pulse-panel">
            <div class="pulse-panel-head">
                <h3>SEO</h3>
                <p>Search engine and social sharing metadata.</p>
            </div>

            <div class="pulse-form-grid pulse-form-grid-single">
                <label>
                    <span>Meta title</span>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $page?->meta_title) }}">
                </label>

                <label>
                    <span>Meta description</span>
                    <textarea name="meta_description" rows="4">{{ old('meta_description', $page?->meta_description) }}</textarea>
                </label>

                <label>
                    <span>Meta keywords</span>
                    <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $page?->meta_keywords) }}">
                </label>

                <label>
                    <span>Canonical URL</span>
                    <input type="text" name="canonical_url" value="{{ old('canonical_url', $page?->canonical_url) }}">
                </label>

                <label>
                    <span>Open Graph title</span>
                    <input type="text" name="og_title" value="{{ old('og_title', $page?->og_title) }}">
                </label>

                <label>
                    <span>Open Graph description</span>
                    <textarea name="og_description" rows="4">{{ old('og_description', $page?->og_description) }}</textarea>
                </label>

                <label>
                    <span>Open Graph image</span>
                    <input type="text" name="og_image" value="{{ old('og_image', $page?->og_image) }}">
                </label>

                <label>
                    <span>Twitter title</span>
                    <input type="text" name="twitter_title" value="{{ old('twitter_title', $page?->twitter_title) }}">
                </label>

                <label>
                    <span>Twitter description</span>
                    <textarea name="twitter_description" rows="4">{{ old('twitter_description', $page?->twitter_description) }}</textarea>
                </label>

                <label>
                    <span>Twitter image</span>
                    <input type="text" name="twitter_image" value="{{ old('twitter_image', $page?->twitter_image) }}">
                </label>
            </div>
        </section>
    </aside>
</div>

<div class="pulse-save-bar">
    <div>
        <strong>Page Editor</strong>
        <span>Save content, publishing status, visibility, and SEO configuration.</span>
    </div>

    <button type="submit" class="pulse-btn pulse-btn-dark pulse-save-btn">
        <span>{{ $buttonText }}</span>
        <span class="material-symbols-rounded">save</span>
    </button>
</div>
