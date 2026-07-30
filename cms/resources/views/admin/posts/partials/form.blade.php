@php
    $post = $post ?? null;
    $selectedTags = array_map('intval', old('tags', $post?->tags?->pluck('id')->all() ?? []));
@endphp
<div class="p-editor-grid">
    <x-pulse.card>
        <x-pulse.form-section title="Post content" description="Provide the post identity, publication state, and body.">
            <x-pulse.field name="title" label="Title" :value="$post?->title" required />
            <x-pulse.field name="slug" label="Slug" :value="$post?->slug" placeholder="auto-generated-from-title" />
            @can('taxonomy.manage')
                <x-pulse.select name="category_id" label="Category"><option value="">Uncategorized</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $post?->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</x-pulse.select>
            @else
                <p class="p-muted">Taxonomy assignment requires taxonomy management permission.</p>
            @endcan
            <x-pulse.select name="status" label="Status">@foreach (['draft' => 'Draft', 'scheduled' => 'Scheduled', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $post?->status?->value ?? 'draft') === $value)>{{ $label }}</option>@endforeach</x-pulse.select>
            <x-pulse.field name="published_at" label="Published at" type="datetime-local" :value="$post?->published_at?->format('Y-m-d\TH:i')" />
            <input type="hidden" name="lock_version" value="{{ $post?->lock_version ?? 0 }}">
            <x-pulse.field name="featured_image" label="Featured image URL" type="url" :value="$post?->featured_image" />
            <x-pulse.textarea name="excerpt" label="Excerpt" :value="$post?->excerpt" rows="4" />
            <x-pulse.textarea name="content" label="Content" :value="$post?->content" rows="14" />
        </x-pulse.form-section>
    </x-pulse.card>
    <aside class="p-stack">
        <x-pulse.card>
            @can('taxonomy.manage')
            <x-pulse.form-section title="Tags" description="Select up to 50 tags associated with this post.">
                @forelse ($tags as $tag)
                    <label class="p-check" for="tag-{{ $tag->id }}"><input id="tag-{{ $tag->id }}" type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags, true))><span>{{ $tag->name }}</span></label>
                @empty
                    <p class="p-muted">No tags are available.</p>
                @endforelse
            </x-pulse.form-section>
            @endcan
        </x-pulse.card>
        <x-pulse.card>
            <x-pulse.form-section title="SEO" description="Search and social sharing metadata.">
                <x-pulse.field name="meta_title" label="Meta title" :value="$post?->meta_title" />
                <x-pulse.textarea name="meta_description" label="Meta description" :value="$post?->meta_description" rows="4" />
                <x-pulse.field name="og_image" label="Open Graph image URL" type="url" :value="$post?->og_image" />
            </x-pulse.form-section>
        </x-pulse.card>
    </aside>
</div>
<x-pulse.action-bar><x-pulse.button type="submit">{{ $buttonText }}</x-pulse.button></x-pulse.action-bar>
