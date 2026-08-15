@php($page = $page ?? null)
<div class="p-editor-grid">
    <x-pulse.card>
        <x-pulse.form-section title="Page content" description="Provide the page identity, publication state, template, and body.">
            <x-pulse.field name="title" label="Title" :value="$page?->title" required />
            <x-pulse.field name="slug" label="Slug" :value="$page?->slug" placeholder="auto-generated-from-title" />
            <x-pulse.select name="status" label="Status">
                <option value="draft" @selected(old('status', $page?->status?->value ?? 'draft') === 'draft')>Draft</option>
                @foreach (['scheduled' => 'Scheduled', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $page?->status?->value) === $value)>{{ $label }}</option>@endforeach
            </x-pulse.select>
            <x-pulse.field name="published_at" label="Publication time ({{ config('app.timezone') }})" type="datetime-local" :value="$page?->published_at?->format('Y-m-d\TH:i')" />
            <input type="hidden" name="lock_version" value="{{ $page?->lock_version ?? 0 }}">
            <x-pulse.select name="template" label="Template">
                @foreach (['default', 'landing', 'full-width', 'blog', 'shop', 'school', 'portfolio'] as $template)
                    <option value="{{ $template }}" @selected(old('template', $page?->template ?? 'default') === $template)>{{ ucfirst(str_replace('-', ' ', $template)) }}</option>
                @endforeach
            </x-pulse.select>
            @can('media.manage')
            <x-pulse.select name="featured_media_id" label="Featured image">
                <option value="">No featured image</option>
                @foreach ($mediaItems as $media)<option value="{{ $media->id }}" @selected(old('featured_media_id', $page?->featured_media_id) == $media->id)>{{ $media->name }}</option>@endforeach
            </x-pulse.select>
            <p class="p-muted">Showing the 100 most recently uploaded managed images.</p>
            @endcan
            <x-pulse.textarea name="content" label="Content" :value="$page?->content" rows="12" />
        </x-pulse.form-section>
    </x-pulse.card>
    <aside class="p-stack">
        <x-pulse.card>
            <x-pulse.form-section title="Page settings" description="Control special assignments and frontend regions.">
                @foreach (['is_homepage' => 'Set as homepage', 'is_blog_page' => 'Set as blog page', 'show_header' => 'Show header', 'show_footer' => 'Show footer'] as $key => $label)
                    <label class="p-check" for="{{ $key }}"><input id="{{ $key }}" type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $page?->{$key} ?? in_array($key, ['show_header', 'show_footer'], true)))><span>{{ $label }}</span></label>
                @endforeach
            </x-pulse.form-section>
        </x-pulse.card>
        @can('seo.manage')
        <x-pulse.card>
            <x-pulse.form-section title="SEO" description="Search engine and social sharing metadata.">
                <x-pulse.field name="meta_title" label="Meta title" :value="$page?->meta_title" />
                <x-pulse.textarea name="meta_description" label="Meta description" :value="$page?->meta_description" rows="4" />
                <x-pulse.field name="meta_keywords" label="Meta keywords" :value="$page?->meta_keywords" />
                <x-pulse.field name="canonical_url" label="Canonical URL" type="url" :value="$page?->canonical_url" />
                <x-pulse.field name="og_title" label="Open Graph title" :value="$page?->og_title" />
                <x-pulse.textarea name="og_description" label="Open Graph description" :value="$page?->og_description" rows="4" />
                <x-pulse.field name="og_image" label="Open Graph image URL" type="url" :value="$page?->og_image" />
                <x-pulse.field name="twitter_title" label="Twitter title" :value="$page?->twitter_title" />
                <x-pulse.textarea name="twitter_description" label="Twitter description" :value="$page?->twitter_description" rows="4" />
                <x-pulse.field name="twitter_image" label="Twitter image URL" type="url" :value="$page?->twitter_image" />
            </x-pulse.form-section>
        </x-pulse.card>
        @endcan
    </aside>
</div>
<x-pulse.action-bar><x-pulse.button type="submit">{{ $buttonText }}</x-pulse.button></x-pulse.action-bar>
