@extends('admin.layouts.app', [
    'title' => 'Pulse SEO',
    'heading' => 'SEO',
    'subheading' => 'Configure global search, social, sitemap, robots, canonical, and schema defaults.'
])

@section('content')

    <form method="POST" action="{{ route('admin.seo.update') }}" class="p-module-settings-form">
        @csrf

        <div class="p-module-settings-grid">
            <section class="p-card">
                <div class="p-card-head">
                    <h3>Global Meta Defaults</h3>
                    <p>Used when a page or post has no custom SEO values.</p>
                </div>

                <div class="p-module-form-grid p-module-form-grid-single">
                    <label>
                        <span>Default meta title</span>
                        <input type="text" name="seo_default_title" value="{{ $settings['seo_default_title'] ?? '' }}">
                    </label>

                    <label>
                        <span>Default meta description</span>
                        <textarea name="seo_default_description" rows="4">{{ $settings['seo_default_description'] ?? '' }}</textarea>
                    </label>

                    <label>
                        <span>Default keywords</span>
                        <input type="text" name="seo_default_keywords" value="{{ $settings['seo_default_keywords'] ?? '' }}">
                    </label>

                    <label>
                        <span>Default Open Graph image URL</span>
                        <input type="text" name="seo_default_og_image" value="{{ $settings['seo_default_og_image'] ?? '' }}">
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Indexing Rules</h3>
                    <p>Control global indexing, canonicals, and social metadata.</p>
                </div>

                <div class="p-module-toggle-list">
                    <label class="p-module-toggle-row">
                        <span>Enable sitemap.xml</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="seo_sitemap_enabled" value="1" @checked(($settings['seo_sitemap_enabled'] ?? '1') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>

                    <label class="p-module-toggle-row">
                        <span>Enable robots.txt output</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="seo_robots_enabled" value="1" @checked(($settings['seo_robots_enabled'] ?? '1') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>

                    <label class="p-module-toggle-row">
                        <span>Enable canonical URLs</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="seo_canonical_enabled" value="1" @checked(($settings['seo_canonical_enabled'] ?? '1') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>

                    <label class="p-module-toggle-row">
                        <span>Noindex entire site</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="seo_noindex_enabled" value="1" @checked(($settings['seo_noindex_enabled'] ?? '0') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>

                    <label class="p-module-toggle-row">
                        <span>Enable social metadata</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="seo_social_enabled" value="1" @checked(($settings['seo_social_enabled'] ?? '1') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>

                    <label class="p-module-toggle-row">
                        <span>Enable schema markup</span>
                        <span class="p-module-switch">
                            <input type="checkbox" name="seo_schema_enabled" value="1" @checked(($settings['seo_schema_enabled'] ?? '1') == '1')>
                            <span class="p-module-switch-track"><span class="p-module-switch-thumb"></span></span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Robots.txt</h3>
                    <p>Customize robots directives for crawlers.</p>
                </div>

                <div class="p-module-form-grid p-module-form-grid-single">
                    <label>
                        <span>Robots content</span>
                        <textarea name="seo_robots_content" rows="10">{{ $settings['seo_robots_content'] ?? "User-agent: *\nAllow: /\n\nSitemap: /sitemap.xml" }}</textarea>
                    </label>
                </div>
            </section>

            <section class="p-card">
                <div class="p-card-head">
                    <h3>Schema Defaults</h3>
                    <p>Basic structured data defaults for the public website.</p>
                </div>

                <div class="p-module-form-grid p-module-form-grid-single">
                    <label>
                        <span>Site schema type</span>
                        <select name="seo_schema_type">
                            @foreach (['WebSite', 'Organization', 'LocalBusiness', 'Person'] as $type)
                                <option value="{{ $type }}" @selected(($settings['seo_schema_type'] ?? 'WebSite') === $type)>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Organization name</span>
                        <input type="text" name="seo_organization_name" value="{{ $settings['seo_organization_name'] ?? ($settings['site_name'] ?? '') }}">
                    </label>

                    <label>
                        <span>Organization logo URL</span>
                        <input type="text" name="seo_organization_logo" value="{{ $settings['seo_organization_logo'] ?? '' }}">
                    </label>
                </div>
            </section>
        </div>

        <div class="p-module-save-bar">
            <div>
                <strong>SEO Module</strong>
                <span>Save global SEO, sitemap, robots, social, and schema defaults.</span>
            </div>

            <button type="submit" class="p-button">
                <span>Save SEO settings</span>
                <span class="material-symbols-rounded">save</span>
            </button>
        </div>
    </form>
@endsection
